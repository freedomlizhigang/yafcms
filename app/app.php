<?php
/*
* 命名空间支持，这个一定要最先引用，否则会出现错误提示
*/
spl_autoload_register(function($classname){
    // 将路径中的\转化为/
    $classname = str_ireplace('\\','/',$classname);
    if (file_exists(APP_PATH.'/app/'.$classname.'.php')) {
        \Yaf\Loader::import(APP_PATH.'/app/'.$classname.'.php');
    }
});

// 错误信息显示级别
error_reporting(E_ALL ^E_NOTICE);
// 根据项目状态，显示错误信息或记录日志
switch (ENV) {
	case 'DEV':
		ini_set('display_errors', 'on');
		break;
	
	case 'PRODUCT':
		// 发布状态错误log保存状态
		$logFile = APP_PATH.'/cache/php/error.log';
        ini_set('display_errors', 'off');
        ini_set('log_errors', 'on');
        ini_set('error_log', $logFile);
		break;
}