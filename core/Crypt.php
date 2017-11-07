<?php
/**
* 对称的加密解密类，算法为des/ecb模式，主要用来对token进行加解密
*/
class Crypt
{
	private $key = 'LiYafCmF';
	public function __construct()
	{
		// 这里取新的KEY，如果配置文件中有
		$this->key = Yaf\Registry::get('config')->crypt->key != '' ? Yaf\Registry::get('config')->crypt->key : 'LiYafCmF';
	}
	// 解密
	public function decrypt($decrypt) {
		/* 打开加密算法和模式 */
		$td = mcrypt_module_open('des', '', 'ecb', '');
		/* 创建初始向量，并且检测密钥长度。 
		* Windows 平台请使用 MCRYPT_RAND。 */
		// get_iv_size 返回打开的算法的初始向量大小,从随机源创建初始向量
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
		// 返回打开的模式所能支持的最长密钥
		$ks = mcrypt_enc_get_key_size($td);
		/* 创建密钥 */
		$key = substr(md5($this->key), 0, $ks);
		/* 初始化解密模块 */
		mcrypt_generic_init($td, $key, $iv);
		/* 解密数据 */
		$decrypted = mdecrypt_generic($td, $this->base64url_decode($decrypt));
		/* 结束解密，执行清理工作，并且关闭模块 */
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $decrypted;
	}
	// 加密
	public function encrypt($encrypt) {
		/* 打开加密算法和模式 */
		$td = mcrypt_module_open('des', '', 'ecb', '');
		/* 创建初始向量，并且检测密钥长度。 
		* Windows 平台请使用 MCRYPT_RAND。 */
		// get_iv_size 返回打开的算法的初始向量大小,从随机源创建初始向量
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
		// 返回打开的模式所能支持的最长密钥
		$ks = mcrypt_enc_get_key_size($td);
		/* 创建密钥 */
		$key = substr(md5($this->key), 0, $ks);
		/* 初始化加密 */
		mcrypt_generic_init($td, $key, $iv);
		/* 加密数据 */
		$encrypted = $this->base64url_encode(mcrypt_generic($td, $encrypt));
		/* 结束，执行清理工作，并且关闭模块 */
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $encrypted;
	}

	// 增加对url友好的支持
	public function base64url_encode($data) { 
	  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	} 
	public function base64url_decode($data) { 
	  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
	}
}