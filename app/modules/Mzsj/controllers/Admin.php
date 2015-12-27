<?php
use modules\Mzsj\Api\Mzsj;
class AdminController extends Mzsj {
	public function init(){
		parent::init();
		$this->admin_db = new AdminModel();
		$this->role_db = new RoleModel();
	}
	/*
	* 用户列表
	*/
	public function indexAction()
	{
		$page = (int)$this->getRequest()->getParam('page',1);
		$adminlist = $this->admin_db->listinfo('','adminid ASC',$page,20);
		$adminlist = $this->numtoname($adminlist,'status','正常','禁用');
	    $pages = $this->admin_db->pages;
	    $this->getView()->assign("adminlist",$adminlist);
    	$this->getView()->assign("pages",$pages);
    	// 用户组名称
    	$rolename = $this->cache->get('role','admin');
    	$this->getView()->assign('rolename',$rolename);
		$this->ttpl('用户中心','admin/index.html');
	}
	/*
	* 新增用户
	*/
	public function addadminAction()
	{
		if (isset($_POST['dosubmit']))
		{
			// 取数据
			$data = $this->checkinput($_POST['info']);
			// 查看是否有相同用户名
			$find = $this->admin_db->get_one('adminname = '.$data['adminname'],'adminid,adminname');
			if (is_array($find))
			{
				$msg['content'] = '用户名已被占用，换一个再试吧!';
				$this->msg($msg['content']);
				return;
			}
			// 密码加密
			$data['password'] = md5(md5($data['password'].$data['encrypt']));
			// 插入数据
			$res = $this->admin_db->insert($data);
			if ($res)
			{
				$msg['content'] = '添加用户成功!';
				$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/index';
				// 记录日志
				$this->addlog('adminid = '.$res);
				// 更新管理员缓存
				$this->cacheall->setadmincache();
			}
			else
			{
				$msg['content'] = '添加用户失败！';
			}
			$this->msg($msg['content'],$msg['url']);
		}
		else
		{
			$this->getView()->assign('encrypt',create_randomstr());
			$this->getView()->assign('rolelist',$this->cache->get('role','admin'));
			$this->ttpl('新增用户','admin/addadmin.html');
		}
	}
	/*
	* 修改用户
	*/
	public function editadminAction($aid = null)
	{
		$aid = (int)$this->getRequest()->getParam('aid',0);
		if (!$aid)
		{
			$msg['content'] = '参数错误';
			$this->msg($msg['content']);
		}
		if (isset($_POST['dosubmit']))
		{
			$data = $this->checkinput($_POST['info']);
			// 要修改密码时，构造密码出来
			if (!empty($data['password']))
			{
				$data['encrypt'] = create_randomstr();
				$data['password'] = md5(md5($data['password'].$data['encrypt']));
			}
			else
			{
				unset($data['password']);
			}
			$res = $this->admin_db->update($data,array('adminid'=>$aid));
			if ($res)
			{
				$msg['content'] = '修改用户成功';
				// 跳转链接
				$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/index';
				// 记录日志
				$this->addlog('adminid = '.$aid);
				// 更新缓存
				$this->cacheall->setadmincache();
			}
			else
			{
				$msg['content'] = '修改用户失败';
			}
			$this->msg($msg['content'],$msg['url']);
		}
		else
		{
			$this->getView()->assign('rolelist',$this->cache->get('role','admin'));
			$this->getView()->assign('info',$this->admin_db->get_one(array('adminid'=>$aid),'*'));
			$this->ttpl('修改用户信息','admin/editadmin.html');
		}
	}
	/*
	* 删除用户
	*/
	public function deladminAction($aid = null)
	{
		$aid = (int)$this->getRequest()->getParam('aid',0);
		if (!$aid)
		{
			$msg['content'] = "参数错误";
		}
		else
		{
			$this->admin_db->delete(array('adminid'=>$aid));
			$this->addlog('adminid = '.$aid);
			$this->cacheall->setadmincache();
			$msg['content'] = '删除成功';
		}
		$this->msg($msg['content']);
	}
	/*
	* 组列表
	*/
	public function rolelistAction()
	{
		$page = (int)$this->getRequest()->getParam('page',1);
		$rolelist = $this->role_db->listinfo('','roleid ASC',$page,20);
		$rolelist = $this->numtoname($rolelist,'status','正常','禁用');
	    $pages = $this->role_db->pages;
	    $this->getView()->assign("rolelist",$rolelist);
    	$this->getView()->assign("pages",$pages);
		$this->ttpl('角色管理','admin/rolelist.html');
	}
	/*
	* 添加组
	*/
	public function addroleAction()
	{
		if (isset($_POST['dosubmit'])) {
			// 安全处理
			$data = $this->checkinput($_POST['info']);
			$roleid = $this->role_db>insert($data);
			if ($roleid) {
				$msg['content'] = "添加角色成功";
				// 拼接跳转的URL
				$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/index';
			}else{
				$msg['content'] = "添加角色失败";
			}
			// 记录用户行为
			$this->addlog("roleid=$roleid");+
			// 更新缓存
			$this->cacheall->setrolecache();
			$this->msg($msg['content'],$msg['url']);
		}else{
			$this->ttpl('添加角色','admin/addrole.html');
		}	
	}
	/*
	* 修改组
	*/
	public function editroleAction($rid = null)
	{
		$rid = (int)$this->getRequest()->getParam('rid',null);
		if (!$rid) 
		{
			$msg['content'] = "参数错误";
			$this->msg($msg['content']);
		}
		if (isset($_POST['dosubmit'])) {
			// 安全处理
			$data = $this->checkinput($_POST['info']);
			$roleid = $this->role_db->update($data,array('roleid'=>$rid));
			if ($roleid)
			{
				$msg['content'] = '修改角色成功';
			}
			else
			{
				$msg['content'] = '修改角色失败';
			}
			// 记录日志
			$this->addlog('roleid = '.$rid);
			// 更新缓存
			$this->cacheall->setrolecache();
			// 跳转链接
			$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/rolelist';
			$this->msg($msg['content'],$msg['url']);
		}
		else
		{
			$infos = $this->role_db->get_one('roleid = '.$rid,'*');
			$this->getView()->assign('infos',$infos);
			$this->ttpl('修改角色','admin/editrole.html');
		}
	}
	/*
	* 删除角色
	*/
	public function delroleAction($rid = null)
	{
		$rid = (int)$this->getRequest()->getParam('rid',0);
		if (!$rid)
		{
			$msg['content'] = "参数错误";
		}
		else
		{
			// 先检查组里有用户吗
			if(is_array($this->role_db->get_one(array('roleid'=>$rid),'*')))
			{
				$msg['content'] = "组里还有用户，请先删除用户！";
				$this->getView()->assign('msg',$msg);
				$this->getView()->display('common/msg.html');
				return;
			}
			$this->role_db->delete(array('roleid'=>$rid));
			$this->addlog('roleid = '.$rid);
			$this->cacheall->setrolecache();
			$msg['content'] = '删除成功';
		}
		$this->msg($msg['content']);
	}
	/*
	* 角色权限管理
	*/
	public function adminprivAction($rid = null)
	{
		$adminpriv = new AdminPrivModel();
		$rid = (int)$this->getRequest()->getParam('rid',0);
		if (!$rid)
		{
			$msg['content'] = '参数错误';
			$this->msg($msg['content']);
		}
		if (isset($_POST['dosubmit']))
		{
			// 清空当前角色在权限表里的url
			$adminpriv->delete(array('roleid'=>$rid));
			// 拼合插入的数组
			$urls = $this->checkinput($_POST['urls']);
			$tmp = array();
			foreach (array_unique($urls) as $k => $v) {
				$tmp[$k]['roleid'] = $rid;
				$tmp[$k]['url'] = $v;
			}
			$adminpriv->insert_all($tmp);
			$msg['content'] = '更改角色权限成功！';
			$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/rolelist';
			// 添加记录
			$this->addlog('roleid = '.$rid);
			$this->msg($msg['content'],$msg['url']);
		}
		else
		{
			$data['roleurl'] = $adminpriv->select(array('roleid'=>$rid),'*','1000');
			$menu = new MenuModel();
			$data['tree'] = $this->list_to_tree($menu,'menuid','parentid',0);
			$tempstr = '';
			foreach ($data['roleurl'] as $v) {
				$tempstr .= "'".$v['url']."',";
			}
			$tempstr = trim($tempstr,',');
			$data['roleurl'] = $tempstr;
			$data['roleid'] = $rid;
			$this->getView()->assign('data',$data);
			$this->ttpl('修改角色权限','admin/adminpriv.html');
		}
	}
}