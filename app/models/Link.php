<?php
class LinkModel extends Model {
	public function __construct(){
		$this->db_config = Yaf\Registry::get("dbconfig");
		$this->db_setting = 'default';
		$this->table_name = 'link';
		parent::__construct();
	}
}