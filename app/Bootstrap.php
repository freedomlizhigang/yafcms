<?php
class Bootstrap extends \Yaf\Bootstrap_Abstract{
	// 配置项目基础信息
	public function _initConfig(){
		// 关闭自动渲染视图
		\Yaf\Dispatcher::getInstance()->disableView();
		// 导入配置信息
		\Yaf\Loader::import(APP_PATH."/conf/config.php");
		// 将配置信息存入全局对象中
		\Yaf\Registry::set("config", $config);
		// 导入数据库信息
		\Yaf\Loader::import(APP_PATH."/conf/database.php");
		// 将数据库信息存入全局对象中
		\Yaf\Registry::set("dbconfig", $dbconfig);
		// 导入公用函数
		\Yaf\Loader::import(APP_PATH."/app/common/Func.php");
	}
    // 注册程序分析插件
	/*public function _initPlugin(Yaf\Dispatcher $dispatcher){
		// 注册程序分析插件
		$bm = new BmPlugin();
		$dispatcher->registerPlugin($bm);
	}*/
}
?>