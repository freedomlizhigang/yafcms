<?php
use lib\Mynamespace as Mynamespace;
class IndexController extends Yaf\Controller_Abstract {
	public function init(){
	}
	public function indexAction($a = '' ,$s = ''){
		$site = new \SiteModel();
		$res = $site->select('','*');
		var_dump($res);
		echo "<br />";echo "<br />";

		foreach ($res as &$value) {
			var_dump($value['name']);
			$res[0]['name'] .= '7777';
			$res[1]['name'] = '7777';
			
			echo "<br />";echo "<br />";
		}

		var_dump($res);
	}
	public function indexsAction($haha = ''){
		var_dump(Yaf\Session::getInstance()->get('isaddmin'));
		exit;
	}
}

?>