<?php
class PublicController extends \Yaf\Controller_Abstract {
	// 导入配置信息，开启session
	public $Config,$session;
	public function init(){
		// 更改session方式为mysql
		new \Session();
		$this->session = \Yaf\Session::getInstance();
		$this->Config = \Yaf\Registry::get('config');
		$this->getView()->assign('conf',$this->Config);
	}
	// 默认转到登陆页
	public function indexAction(){
		// $this->forward("login");
		$this->redirect("/mzsj/public/login");
		exit;
	}
	// 登陆
	public function loginAction(){
		if (isset($_POST['dosubmit'])) {
			$data = $_POST['info'];
			// 取得session中的验证码
			$verifycode = $this->session->get('code');
			if ($data['verify'] !== $verifycode)
			{
				$this->msg('验证码错误！','/mzsj/public/login');
			}
			else
			{
				$admin = new AdminModel();
				$uinfo = $admin->get_one(array('adminname'=>$data['username']),'*');
				if (empty($uinfo) || $uinfo['status'] == 0) {$this->msg('用户不存在，或已禁用！','/mzsj/public/login');}
				// 查看组是否被禁用
				$role = new RoleModel();
				$rinfo = $role->get_one(array('roleid'=>$uinfo['roleid']));
				if ($rinfo['status'] == 0) {$this->msg('用户组已被禁用！','/mzsj/public/login');}
				if ($uinfo['password'] === md5(md5($data['password'].$uinfo['encrypt']))) {
					$this->session->set('mz_uid',$uinfo['adminid']);
					$this->session->set('mz_uname',$uinfo['adminname']);
					$this->session->set('mz_rid',$uinfo['roleid']);
					// 更新用户最后登陆状态
					$updata['lastip'] = get_ip();
					$updata['lasttime'] = time();
					$admin->update($updata,array('adminid'=>$uinfo['adminid']));
					$this->msg('登陆成功，正在跳转至用户中心！','mzsj/index/index');
				}
				else
				{
					$this->msg('密码错误，请联系管理员！','/mzsj/public/login');
				}
			}
		}else{
			// 先判断是否登陆过
			if ($this->session->get('mz_uid') != null) 
			{
				$this->msg('已经登陆过，即将跳转到用户中心！','/mzsj/index/index');
			}
			else
			{
				$this->getView()->assign('title','用户登陆');
				// 使用组合模板
				$this->getView()->display('public/login.html');
			}
		}
	}
	// 登出
	public function logoutAction()
	{
		$this->session->del('mz_uid');
		$this->session->del('mz_uname');
		$this->session->del('mz_rid');
		$this->msg('已退出登陆！','/mzsj/public/login');
	}
	// 跳转
	private function msg($msg = '',$url = '',$top = 0)
	{	
		$data['title'] = '跳转页面';
		$data['content'] = $msg;
		$data['url'] = $url;
		$data['top'] = $top;
		$this->getView()->assign('msg',$data);
		$this->getView()->display('common/msg.html');
	}
	// 添加用户日志
	private function addlog($q = ''){
		$data['url'] = '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$this->getRequest()->getActionName().'/';
		$data['data'] = $q;
		$data['adminid'] = $this->session->get('mz_uid');
		$data['adminname'] = $this->session->get('mz_uname');
		$data['ip'] = get_ip();
		$data['time'] = time();
		$log = new \LogModel();
		$log->insert($data);
	}
	// 验证码
	public function verifyAction(){
		$verify = new \Checkcode();
		$verify->width = '240';
		$verify->height = '60';
		$verify->font_size = '30';
		$verify->doimage();
		// 验证码保存到session中
		$this->session->set('code',$verify->get_code());
	}
	/*
	* 上传图片
	* 先生成当天的上传子目录mkdir()，以此来分类图片
	*/
	public function kinduploadAction()
	{
		$config['upload_path'] = './uploads/'.date('Ymd').'/';
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size'] = '2048'; //kb
		// $config['file_name'] = '';  //重命名方法
		$config['file_ext_tolower'] = true; //文件后缀名将转换为小写
		$config['encrypt_name'] = true; //文件名将会转换为一个随机的字符串
		$config['max_filename_increment'] = 10000; //同名文件最大自增数
		$upload = new \Upload($config);
		/* 返回标准数据 */
        $return  = array('error' => 0, 'info' => '上传成功', 'data' => '');
        /* 记录附件信息 */
        // 字段是imgFile，由KindEditor生成的上传字段
		if(!$upload->do_upload('imgFile'))
        {
        	$return['error'] = 1;
            $return['message'] = $upload->display_errors();
        }
        else
        {
        	$return['url'] = '/uploads/'.date('Ymd').'/'.$upload->data()['file_name'];
        }
        exit(json_encode($return));
	}
}
?>