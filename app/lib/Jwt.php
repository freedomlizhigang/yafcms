<?php
/*
* JWT功能类库，依赖加密解密类Crypt
* 最好是把生成的token存在用户表里，以保证唯一性
 */
class Jwt {
	// 引入类
	private $crypt;
	// 有效时间，默认7天
	private $ttl = 0;
	// 配置
	public function __construct()
	{
		// 取得有效时间
		$this->ttl = isset(Yaf\Registry::get('config')->jwt->ttl) ? Yaf\Registry::get('config')->jwt->ttl : 515088;
		$this->crypt = new Crypt();
	}
	/*
	* 生成token
	* 参数为数组,可以传一些自定义的数据如id=1,roleid=2
	 */
	public function encode($data = [])
	{
		$token = '';
		if (count($data) === 0 || !is_array($data)) {
			return false;
		}
		$data = array_merge($data,['st'=>time(),'ot'=>(time() + $this->ttl)]);
		$str = '';
		// 加密
		foreach ($data as $k => $v) {
			$data[$k] = $this->crypt->encrypt(trim($v));
			$str .= $k."|";
		}
		$data['sub'] = $this->crypt->encrypt(trim($str,'|'));
		// 返回以 . 分隔的字符串
		return implode('.', $data);
	}
	/*
	* 解密token
	* return array()
	 */
	public function decode($token)
	{
		// 转成数组
		$data = explode('.', $token);
		foreach ($data as $k => $v) {
			$data[$k] = trim($this->crypt->decrypt($v));
		}
		// 解析sub内容。生成真正的返回数据
		$sub = explode('|',$data[count($data)-1]);
		$tmp = [];
		foreach ($sub as $k => $v) {
			$tmp[$v] = $data[$k];
		}
		return $tmp;
	}
	/*
	* 取某一项的值
	* $token
	* $sub 要取值的名称
	 */
	public function getsub($token = '',$sub = '')
	{
		$data = $this->decode($token);
		if (isset($data[$sub])) {
			return $data[$sub];
		}
		else
		{
			return false;
		}
	}
	/*
	* 刷新token
	 */
	public function reftoken($token)
	{
		$data = $this->decode($token);
		$data['st'] = time();
		$data['ot'] = time() + $this->ttl;
		$str = '';
		// 加密
		foreach ($data as $k => $v) {
			$data[$k] = $this->crypt->encrypt(trim($v));
			$str .= $k."|";
		}
		$data['sub'] = $this->crypt->encrypt(trim($str,'|'));
		// 返回以 . 分隔的字符串
		return implode('.', $data);
	}
}