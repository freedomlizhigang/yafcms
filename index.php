<?php
	 /* 项目主目录 */
	define("APP_PATH", realpath(__DIR__.'/'));
	// composer自动加载文件
	\Yaf\Loader::import(APP_PATH."/vendor/autoload.php");
	/*定义项目状态,dev为开发状态，product为上线状态*/
	define('ENV','DEV');
	// 导入项目配置文件+加载命名空间支持及错误信息提示方法
	\Yaf\Loader::import(APP_PATH."/app/app.php");
	$app  = new \Yaf\Application(APP_PATH."/conf/app.ini");
	$app->bootstrap()->run();