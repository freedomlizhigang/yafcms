<?php
class MenuModel extends Model {
	public function __construct() {
		// 导入数据库配置文件
		$this->db_config = Yaf\Registry::get("dbconfig");
		$this->db_setting = 'default';
		$this->table_name = 'menu';
		parent::__construct();
	}
}
?>