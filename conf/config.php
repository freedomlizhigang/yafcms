<?php

$config = array (
	'js_path' => '/public/js/', //CDN JS
	'css_path' => '/public/css/', //CDN CSS
	'img_path' => '/public/images/', //CDN img
	'app_path' => 'http://www.yafcms.com/',//动态域名配置地址
	//Cookie配置
	'cookie_domain' => '', //Cookie 作用域
	'cookie_path' => '', //Cookie 作用路径
	'cookie_pre' => 'mzsj_', //Cookie 前缀，同一域名下安装多套系统时，请修改Cookie前缀
	'cookie_ttl' => 0, //Cookie 生命周期，0 表示随浏览器进程
	//Session配置
	'session_time' => 1800,
	'session_path' => '/cache/session/',
);