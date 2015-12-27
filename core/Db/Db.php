<?php
/**
 *  db_factory.class.php 数据库工厂类
 *
 * @copyright			(C) 2005-2010 PHPCMS
 * @license				http://www.phpcms.cn/license/
 * @lastmodify			2010-6-1
 */

final class Db_Db {
	
	/**
	 * 当前数据库工厂类静态实例
	 */
	private static $db_factory;
	
	/**
	 * 数据库配置列表
	 */
	protected $db_config = array();
	
	/**
	 * 数据库操作实例化列表
	 */
	protected $db_list = array();
	
	/**
	 * 构造函数
	 */
	public function __construct() {
	}
	
	/**
	 * 返回当前终级类对象的实例
	 * @param $db_config 数据库配置
	 * @return object
	 */
	public static function get_instance($db_config = '') {
		if($db_config == '') {
			// 读取全局数据库配置
			$db_config = Yaf\Registry::get("dbconfig");
		}
		if(Db_Db::$db_factory == '') {
			Db_Db::$db_factory = new Db_Db();
		}
		if($db_config != '' && $db_config != Db_Db::$db_factory->db_config) Db_Db::$db_factory->db_config = array_merge($db_config, Db_Db::$db_factory->db_config);
		return Db_Db::$db_factory;
	}
	
	/**
	 * 获取数据库操作实例
	 * @param $db_name 数据库配置名称
	 */
	public function get_database($db_name) {
		if(!isset($this->db_list[$db_name]) || !is_object($this->db_list[$db_name])) {
			$this->db_list[$db_name] = $this->connect($db_name);
		}
		return $this->db_list[$db_name];
	}
	
	/**
	 *  加载数据库驱动
	 * @param $db_name 	数据库配置名称
	 * @return object
	 */
	public function connect($db_name) {
		$object = null;
		// 自动载入，直接实例化，默认pdo方式操作mysql数据库
		switch($this->db_config[$db_name]['type']) {
			case 'mysqli' :
				$object = new Db_Mysqli();
				break;
			default :
				$object = new Db_Mysql();
		}
		$object->open($this->db_config[$db_name]);
		return $object;
	}

	/**
	 * 关闭数据库连接
	 * @return void
	 */
	protected function close() {
		foreach($this->db_list as $db) {
			$db->close();
		}
	}
	
	/**
	 * 析构函数
	 */
	public function __destruct() {
		$this->close();
	}
}
?>