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
	/*
	* 默认SESSION配置
	*/
	/*public function _initSession(){
		// 改变默认session保存路径，保存时间（以文件方式存储时）
		ini_set('session.save_handler', 'files');
		session_save_path(APP_PATH.\Yaf\Registry::get('config')['session_path']);
		ini_set('session.gc_maxlifetime',\Yaf\Registry::get('config')['session_time']);
	}*/
	// Bootstrap分发
	// public function _initboot(Yaf\Dispatcher $dispatcher){
	// 	var_dump($dispatcher->getModuleName());
	// 	exit;
	// }
	// 注册插件
	// public function _initPlugin(Yaf\Dispatcher $dispatcher){
	// 	// 注册程序分析插件
	// 	// $bm = new BmPlugin();
	// 	// $dispatcher->registerPlugin($bm);
	// }
	// 添加路由规则
	// public function _initRoute(){
		//通过派遣器得到默认的路由器
    	// $router = Yaf_Dispatcher::getInstance()->getRouter();
		//创建一个路由协议实例
	    // $route = new Yaf_Route_Rewrite(
	    // 	"product/:haha",
	    // 	array(
	    // 		"controller" => "Index",
	    // 		"action"	 => "indexs",
	    // 	)
	    // );
	    //使用路由器装载路由协议
	    // $router->addRoute('dummy', $route);
	// }
	// 一些测试
	// public function _inittest(){
	// 	$dispatcher = Yaf_Dispatcher::getInstance();
	// 	var_dump($dispatcher);
	// }
}
?>