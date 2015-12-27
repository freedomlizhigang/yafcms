<?php
class Cache {
	/*缓存默认配置*/
	protected $_setting = array(
								'suf' => '.cache.php',	/*缓存文件后缀*/
								'type' => 'array',		/*缓存格式：array数组，serialize序列化，null字符串*/
							);
	/**
	 * 构造函数
	 * @param	array	$setting	缓存配置
	 * @return  void
	 */
	public function __construct() {
	}
	
	/**
	 * 写入缓存
	 * @param	string	$name		缓存名称
	 * @param	mixed	$data		缓存数据
	 * @param	array	$setting	缓存配置
	 * @param	string	$module		所属模型
	 * @return  mixed				缓存路径/false
	 */

	public function set($name, $data, $module = 'commons') {
		if(empty($type)) $type = 'data';
		$filepath = APP_PATH.'/cache/caches_'.$module.'/';
		$filename = $name.$this->_setting['suf'];
	    if(!is_dir($filepath)) {
			mkdir($filepath, 0777, true);
	    }
	    
	    if($this->_setting['type'] == 'array') {
	    	$data = "<?php\nreturn ".var_export($data, true).";\n?>";
	    } elseif($this->_setting['type'] == 'serialize') {
	    	$data = serialize($data);
	    }
	    
	    //开启互斥锁
		$file_size = file_put_contents($filepath.$filename, $data, LOCK_EX);
	    return $file_size ? $file_size : 'false';
	}
	
	/**
	 * 获取缓存
	 * @param	string	$name		缓存名称
	 * @param	array	$setting	缓存配置
	 * @param	string	$module		所属模型
	 */
	public function get($name, $module = 'commons') {
		$filepath = APP_PATH.'/cache/caches_'.$module.'/';
		$filename = $name.$this->_setting['suf'];
		if (!file_exists($filepath.$filename)) {
			return false;
		} else {
		    if($this->_setting['type'] == 'array') {
		    	$data = @require($filepath.$filename);
		    } elseif($this->_setting['type'] == 'serialize') {
		    	$data = unserialize(file_get_contents($filepath.$filename));
		    }
		    return $data;
		}
	}
	
	/**
	 * 删除缓存
	 * @param	string	$name		缓存名称
	 * @param	array	$setting	缓存配置
	 * @param	string	$module		所属模型
	 * @return  bool
	 */
	public function delete($name, $module = 'commons')
	{
		$filepath = APP_PATH.'/cache/caches_'.$module.'/';
		$filename = $name.$this->_setting['suf'];
		if(file_exists($filepath.$filename)){
			return @unlink($filepath.$filename) ? true : false;
		} else {
			return false;
		}
	}
}

?>