<?php
use \modules\Mzsj\Api\Mzsj;
class IndexController extends Mzsj {
	public function init(){
		parent::init();
	}
	// 首页
	public function indexAction(){
		$this->getView()->assign('mainmenu',$this->findmenu(0));
		$this->getView()->display('index/index.html');
	}
	/*
	* left_menu()
	* pid 父级id 找到下级子栏目
	*/ 
	public function leftmenuAction()
	{
		$pid = (int)$this->getRequest()->getParam('pid',0);
		$this->getView()->assign('two_menu',$this->findmenu($pid));
		$this->getView()->assign('Mzsj',$this);
		$this->getView()->display('index/left.html');
	}
	/*
	* main
	*/
	public function mainAction()
	{
		$this->ttpl('管理中心','index/main.html');
	}
	/*
	* 站点列表
	*/
	public function sitelistAction(){
		$site = new SiteModel();
		$page = $this->getRequest()->getParam('page',1);
		$sitelist = $site->listinfo('','',$page,20);
	    $pages = $site->pages;
	    $this->getView()->assign("sitelist",$sitelist);
    	$this->getView()->assign("pages",$pages);
		$this->ttpl('管理中心','index/sitelist.html');
	}
	public function editsiteAction(){
		// 标题
		$site = new SiteModel();
		$siteid = $this->getRequest()->getParam('siteid',0);
		if (isset($_POST['dosubmit'])) {
			$where = "siteid = ".$_POST['siteid'];
			// 安全处理
			$data = $this->checkinput($_POST['info']);
			$siteid = $site->update($data,$where);
			if ($siteid) {
				$msg['content'] = "修改成功";
			}else{
				$msg['content'] = "修改失败";
			}
			// 拼接跳转的URL
			$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/'.$this->Aname.'/siteid/'.$siteid;
			// 添加日志记录
			$this->addlog($where);
			// 更新缓存
			$this->cacheall->setsitecache();
			$this->msg($msg['content'],$msg['url']);
		}else{
			$where = 'siteid = '.$siteid;
			$siteinfo = $site->get_one($where,'*','siteid DESC');
			$this->getView()->assign('site',$siteinfo);
			$this->ttpl('修改站点','index/editsite.html');
		}
	}
	/*
	* 日志列表
	*/
	public function loglistAction()
	{
		$log = new LogModel();
		$page = $this->getRequest()->getParam('page',1);
		$sitelist = $log->listinfo('','logid DESC',$page,20);
	    $pages = $log->pages;
	    $this->getView()->assign("loglist",$sitelist);
    	$this->getView()->assign("pages",$pages);
		$this->ttpl('用户日志','index/loglist.html');
	}
	/*
	* 清除7天前日志
	*/
	public function clearAction()
	{
		$log = new LogModel();
		$times = time() - 3600*24*7;
		$where = 'time < '.$times;
		$log->delete($where);
		$this->addlog('清除日志');
		$msg['content'] = '清除日志成功！';
		$this->msg($msg['content']);
	}
	/*
	* 更新缓存
	*/
	public function updatecacheAction()
	{
		$this->getView()->assign('title','更新缓存');
		$this->getView()->display('common/header.html');
		// 站点缓存
		$this->cacheall->setsitecache();
		echo "<p>更新站点缓存成功！</p>";
		// 角色
		$this->cacheall->setrolecache();
		echo "<p>更新角色缓存成功！</p>";
		// 管理员
		$this->cacheall->setadmincache();
		echo "<p>更新管理员缓存成功！</p>";
		// 栏目
		$this->cacheall->setcatecache();
		echo "<p>更新栏目缓存成功！</p>";
		echo "<p style='color:red'>更新缓存完成！</p>";
		$this->getView()->display('common/footer.html');
	}
}
?>