<?php
namespace modules\Mzsj\Api;
use lib\CacheAll as CacheAll;
class Mzsj extends \Yaf\Controller_Abstract{
	// 导入配置信息，开启session
	public $Config,$session,$Mname,$Cname,$Aname,$cache,$cacheall;
	public function init(){
		// 更改session方式为mysql
		new \Session();
		// 启用session别名
		$this->session = \Yaf\Session::getInstance();
		// 加载配置并存到模板变量中
		$this->Config = \Yaf\Registry::get('config');
		$this->getView()->assign('conf',$this->Config);

		// 当前模块、控制器、方法名
		$this->Mname = $this->getRequest()->getModuleName();
		$this->Cname = $this->getRequest()->getControllerName();
		$this->Aname = $this->getRequest()->getActionName();
		$this->getView()->assign('Mname',$this->Mname);
		$this->getView()->assign('Cname',$this->Cname);
		$this->getView()->assign('Aname',$this->Aname);
		// 判断是否登陆
		$this->islogin();
		// 加载公用函数，用户信息，权限检查，右侧菜单等
		$this->getadmin();
		$this->check_priv();
		$this->getView()->assign('rightmenu',$this->getsubmenu());
		$this->cache = new \Cache();
		$this->cacheall = new CacheAll();
	}
	// 判断是否登陆
	public function islogin(){
		if(!$this->session->has('mz_uid') || !$this->session->has('mz_rid')){
			$this->msg("请先登陆后台，再进行操作！",'/mzsj/public/login');
		}
	}
	// 取得用户信息
	public function getadmin(){
		$adminid = $this->session->get('mz_uid');
		$admin = new \AdminModel();
		$this->getView()->assign('ainfo',$admin->get_one(array('adminid'=>$adminid),'*'));
	}
	// 检查权限
	final function check_priv(){
		if($this->Mname =='Mzsj' && $this->Cname =='Public' && $this->Aname =='login') return true;
		if($this->Mname =='Mzsj' && $this->Cname =='Index' && $this->Aname =='main') return true;
		if($this->Mname =='Mzsj' && $this->Cname =='Index' && $this->Aname =='leftmenu') return true;
		if($this->session->get('mz_rid') == 1) return true;
		$nowsurl = $this->Mname.'/'.$this->Cname.'/'.$this->Aname;
		$adminpriv = new \AdminPrivModel();
		$res = $adminpriv->get_one(array('roleid'=>$this->session->get('mz_rid'),'url'=>$nowsurl));
		if (!$res) $this->msg("您没有这个操作的权限！");
	}
	// 找出所有有权限的url
	public function privurl(){
		$adminpriv = new \AdminPrivModel();
		$list = $adminpriv->select(array('roleid'=>$this->session->get('mz_rid')),'*',1000);
		return $list;
	}
	/*
	* 按父级查找菜单
	* pid 父级id 找到下级子栏目
	*/
	public function findmenu($pid = 0){
		$Menu = new \MenuModel();
		$pid = intval($pid);
		$menus = $Menu->select(array('display'=>1,'parentid'=>$pid),'*',1000,'listorder ASC,menuid ASC');
		// 取出所有可以显示的菜单
		// 根据不同用户组，进行权限判断，超级管理员拥有所有权限
		// 因为会在leftmenu中实例化多次，所以不能用$this->session,可以直接取得session中的值，大多数时候，$this->session也只是方便写，并不是真的有必要实例化一次。
		if (\Yaf\Session::getInstance()->__get('mz_rid') == 1)
		{
			return $menus;
		}
		else
		{
			$array = array();
			$privmenu = $this->privurl();
			foreach (is_array($menus) as $mv) {
				if (is_array($privmenu)) {
					foreach ($privmenu as $pv) {
						if($mv['url'] == $pv['url']){
							$array[] = $mv;
						}
					}
				}
			}
			return $array;
		}
	}
	// 输出导航菜单
	public function getsubmenu(){
		$privmenu = $this->privurl();
		$submenus = array();
		$Menu = new \MenuModel();
		$url = $this->Mname."/".$this->Cname."/".$this->Aname;
		$menuid = $Menu->get_one(array('level'=>3,'url'=>$url),'*','listorder Asc,menuid Asc');
		$tempsub = array();
		if ($menuid) {
			$submenus = $Menu->select(array('display'=>1,'parentid'=>$menuid['menuid']),'*','100','listorder Asc,menuid Asc');
			// 根据不同用户组，进行权限判断，超级管理员拥有所有权限
			if ($this->session->get('mz_rid') == 1) {
				$tempsub = $submenus;
			}else{
				foreach (is_array($submenus) as $privsub) {
					if (is_array($privmenu)) {
						foreach (is_array($privmenu) as $pvsub) {
							if($privsub['url'] == $pvsub['url']){
								$tempsub[] = $privsub;
							}
						}
					}
				}
			}
		}
		return $tempsub;
	}
	// 输入安全处理
	public function checkinput($data){
		// 开始进行安全处理,1、去除前后空格；2、格式化js代码；3、部分字段去除php/html/xml标记，以及html转义;
		$data = trims($data);
		$data = trim_script($data);
		$data = istextarea($data);
		$data = trims($data);
		return $data;
	}
	// 输出树形菜单
	protected $resarray = array();
	protected function list_to_tree($model = '',$pkname = '',$parentidname = '',$pid = 0,$jg = '&nbsp;',$field = '*'){
		$where = "$parentidname = $pid";
		$lists = $model->select($where,"*",100,"$pkname ASC");
		if (is_array($lists)) {
		foreach($lists as $ml){
			$ml['nbsp'] = '';
			for ($j = 2 ;$j < $ml['level'] ;$j++) {
				$ml['nbsp'] .= "|".$jg.$jg;
			}
			if ($ml['level'] > 1) {
				$ml['nbsp'] .= "|-";
			}
			$this->resarray[] = $ml;
			$this->list_to_tree($model,$pkname,$parentidname,$ml[$pkname],$jg,$field);
		}
		}
		return $this->resarray;
	}
	// 状态数字转为文字
	protected function numtoname($list,$field='',$name1='',$name2=''){
		foreach ($list as $key => $value) {
			$list[$key][$field] = $value[$field] ? "<span class='color_green'>".$name1."</span>" : "<span class='color_red'>".$name2."</span>";
		}
		return $list;
	}
	// 状态数字转为文字
	protected function fieldtoname($list,$field='',$fieldname = '',$name1='',$name2=''){
		foreach ($list as $key => $value) {
			$list[$key][$fieldname] = $value[$field] ? "<span class='color_green'>".$name1."</span>" : "<span class='color_red'>".$name2."</span>";
		}
		return $list;
	}
	// 循环删除子菜单
	protected function delChild($model = '',$pkname = '',$parentid = '',$pid = ''){
		$res = $model->select("$parentid = $pid","$pkname,$parentid");
		if ($res) {
			foreach ($res as $value) {
				$r=$model->delete("$pkname = $value[$pkname]");
				$this->delChild($model,$pkname,$parentid,$value[$pkname]);
			}
		}
		return true;
	}
	// 添加用户日志
	protected function addlog($q = ''){
		$data['url'] = '/'.$this->Mname.'/'.$this->Cname.'/'.$this->Aname.'/';
		$data['data'] = $q;
		$data['adminid'] = $this->session->get('mz_uid');
		$data['adminname'] = $this->session->get('mz_uname');
		$data['ip'] = get_ip();
		$data['time'] = time();
		$log = new \LogModel();
		$log->insert($data);
	}
	// 跳转
	protected function msg($msg = '',$url = '',$top = 0)
	{	
		$data['title'] = '跳转页面';
		$data['content'] = $msg;
		$data['url'] = $url;
		$data['top'] = $top;
		$this->getView()->assign('msg',$data);
		$this->getView()->setScriptPath(APP_PATH."/app/modules/Mzsj/views/");
		$this->getView()->display('common/msg.html');
		die();
	}
	// 将标题与模板合并到一个方法中
	protected function ttpl($title = '',$tpl = '')
	{
		// 标题
		$this->getView()->assign("title",$title);
		$this->getView()->display('common/header.html');
		$this->getView()->display($tpl);
		$this->getView()->display('common/footer.html');
	}
}
?>