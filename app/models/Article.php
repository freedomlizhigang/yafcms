<?php
class ArticleModel extends Model {
	public function __construct(){
		$this->db_config = Yaf\Registry::get("dbconfig");
		$this->db_setting = 'default';
		$this->table_name = 'article';
		parent::__construct();
	}
}