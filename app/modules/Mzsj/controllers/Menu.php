<?php
use modules\Mzsj\Api\Mzsj;
class MenuController extends Mzsj {
	public function init(){
		parent::init();
	}
	// menu tree
	public function indexAction(){
		$menu = new MenuModel();
		$tree = $this->list_to_tree($menu,'menuid','parentid',0);
		$tree = $this->numtoname($tree,'display','是','否');
		$this->getView()->assign('tree',$tree);
		$this->ttpl('菜单列表','menu/index.html');
	}
	// add menu
	public function addmenuAction(){
		$menu = new MenuModel();
		if (isset($_POST['dosubmit'])) {
			// 安全处理
			$data = $this->checkinput($_POST['info']);
			$menuid = $menu->insert($data);
			if ($menuid) {
				$msg['content'] = "添加成功";
			}else{
				$msg['content'] = "添加失败";
			}
			// 记录用户行为
			$this->addlog("menuid=$menuid");
			// 拼接跳转的URL
			$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/index';
			$this->msg($msg['content'],$msg['url']);
		}else{
			$pid = $this->getRequest()->getParam('pid',0);
			$this->getView()->assign('pid',$pid);
			if($pid == 0){
				$this->getView()->assign('level',1);
			}else{
				$level = $menu->get_one(array('menuid'=>$pid),"*");
				$level = $level['level'] + 1;
				$this->getView()->assign('level',$level);
			}
			$this->ttpl('添加菜单','menu/addmenu.html');
		}
	}
	// edit menu
	public function editmenuAction(){
		$menu = new MenuModel();
		$mid = $this->getRequest()->getParam('mid',0);
		if (isset($_POST['dosubmit'])) {
			$where = "menuid = $mid";
			// 安全处理
			$data = $this->checkinput($_POST['info']);
			$menuid = $menu->update($data,$where);
			if ($menuid) {
				$msg['content'] = "修改成功";
			}else{
				$msg['content'] = "修改失败";
			}
			// 记录用户行为
			$this->addlog("menuid=$mid");
			// 拼接跳转的URL
			$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/index';
			$this->msg($msg['content'],$msg['url']);
		}else{
			$info = $menu->get_one(array('menuid'=>$mid),"*");
			$this->getView()->assign('info',$info);
			$this->ttpl('修改菜单','menu/editmenu.html');
		}
	}
	// del menu
	public function delmenuAction(){
		$menu = new MenuModel();
		if (isset($_POST['dosubmit'])) {
			$mids = $_POST['mids'];
			foreach ($mids as $v) {
				$res = $this->delChild($menu,'menuid','parentid',$v);
				$del = $menu->delete("menuid = $v");
			}
			// 记录用户行为
			$this->addlog("menuid=".implode(",", $mids));
		}else{
			$mid = $this->getRequest()->getParam('mid',0);
			$res = $this->delChild($menu,'menuid','parentid',$mid);
			$del = $menu->delete("menuid = $mid");
			// 记录用户行为
			$this->addlog("menuid=$mid");
		}
		// 拼接跳转的URL
		$msg['content'] = "删除成功";
		$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/index';
		$this->msg($msg['content'],$msg['url']);
	}
}
?>