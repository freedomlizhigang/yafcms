<?php
/*
* 公用函数文件
*/

/*
所有输入去除前后空格
*/
function trims($data){
	foreach ($data as $key => $val){
		$data[$key] = trim($val);
	}
	return $data;
}
/*
除富文本字段外所有字段去除php/html/xml标记，富文本进行html转义
*/
function istextarea($data){
	foreach ($data as $k => $value) {
		if ('content' != substr($k, 0,7)) {
			$data[$k] = strip_tags($value);
		}else{
			$data[$k] = htmlspecialchars($value);
		}
	}
	return $data;
}
/**
 * 转义 javascript 代码标记
 *
 * @param $str
 * @return mixed
 */
function trim_script($str) {
	if(is_array($str)){
		foreach ($str as $key => $val){
			$str[$key] = trim_script($val);
		}
 	}else{
 		$str = preg_replace ( '/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str );
		$str = preg_replace ( '/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str );
		$str = preg_replace ( '/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str );
		$str = str_replace ( 'javascript:', 'javascript：', $str );
 	}
	return $str;
}
// get_ip
function get_ip() {
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$ip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$ip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}
/**
 * 生成随机字符串
 * @param string $lenth 长度
 * @return string 字符串
 */
function create_randomstr($lenth = 6) {
	return random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
}

/**
* 产生随机字符串
*
* @param    int        $length  输出长度
* @param    string     $chars   可选的 ，默认为 0123456789
* @return   string     字符串
*/
function random($length, $chars = '0123456789') {
	$hash = '';
	$max = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}
/*
* PHP版本
*/
function is_php($version)
{
	static $_is_php;
	$version = (string) $version;

	if ( ! isset($_is_php[$version]))
	{
		$_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
	}

	return $_is_php[$version];
}
/*
* 检查目录是否可写
*/
function is_really_writable($file)
{
	// If we're on a Unix server with safe_mode off we call is_writable
	if (DIRECTORY_SEPARATOR === '/' && (is_php('5.4') OR ! ini_get('safe_mode')))
	{
		return is_writable($file);
	}

	/* For Windows servers and safe_mode "on" installations we'll actually
	 * write a file then read it. Bah...
	 */
	if (is_dir($file))
	{
		$file = rtrim($file, '/').'/'.md5(mt_rand());
		if (($fp = @fopen($file, 'ab')) === FALSE)
		{
			return FALSE;
		}

		fclose($fp);
		@chmod($file, 0777);
		@unlink($file);
		return TRUE;
	}
	elseif ( ! is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE)
	{
		return FALSE;
	}

	fclose($fp);
	return TRUE;
}
