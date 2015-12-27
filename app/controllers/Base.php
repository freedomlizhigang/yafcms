<?php
class BaseController extends IndexController {
	public function init(){
		parent::init();
		echo "base<br />";
	}
	public function indexAction($a = '' ,$s = ''){
		$ns = new Mynamespace();
		$ns->namestring();
		echo "<br />";
		// $site = new SiteModel();
		// $all = $site->select('','*');
		// var_dump($all);
		// $this->getView()->assign("content","Hello World!");
	}
	public function indexsAction($haha = ''){
		var_dump(Yaf\Session::getInstance()->get('isaddmin'));
		exit;
	}

	
}

?>