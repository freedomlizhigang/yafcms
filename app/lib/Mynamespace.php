<?php
/*
* 测试namespace用的类，可以删除
*/
namespace lib;
class Mynamespace
{
	public function namestring(){
		$db = new \AdminModel();
		// $all = $db->select('','*');
		echo "namestring";
	}
}
