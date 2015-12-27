<?php
/**
 *  mysql.class.php 数据库实现类
 *
 * @copyright			(C) 2005-2014 PHPCMS
 * @license				http://www.phpcms.cn/license/
 * @lastmodify			2014-12-05
 */

final class Db_Mysql {
	
	/**
	 * 数据库配置信息
	 */
	private $config = null;
	
	/**
	 * 数据库连接资源句柄
	 */
	public $link = null;
	

	/*
	* 数据库执行结果类PDOStatement
	*/
	public $pdos = null;

	
	public function __construct() {

	}
	
	/**
	 * 配置数据库连接参数，并不进行连接
	 * @param $config	数据库连接参数
	 * 			
	 * @return void
	 */
	public function open($config) {
		$this->config = $config;
		if($config['autoconnect'] == 1) {
			$this->connect();
		}
	}

	/**
	 * 真正开启数据库连接
	 * 			
	 * @return void
	 */
	public function connect() {
		try {
			$port = $this->config['port']?intval($this->config['port']):3306;
			// 判断是否是长连接
			if ($this->config['pcontent'] != 0) {
				$this->link = new PDO('mysql:host='.$this->config['hostname'].';dbname='.$this->config['database'].';port='.$port, $this->config['username'], $this->config['password'],array(PDO::ATTR_PERSISTENT => true));
			}
			else
			{
				$this->link = new PDO('mysql:host='.$this->config['hostname'].';dbname='.$this->config['database'].';port='.$port, $this->config['username'], $this->config['password']);
			}
			// 禁用模拟预处理，确保sql语句和值在传递到mysql前不会被PHP解析
			$this->link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			// 设置字符集
			$this->link->query('SET NAMES ' . $this->config['charset']);
			return $this->link;
		} catch (PDOException $e) {
            echo '<pre>';
            echo '<b>Connection failed:</b> ' . $e->getMessage();
            echo '</pre>';
            die();
        }
	}

	/**
	 * 数据库查询执行方法
	 * @param $sql 要执行的sql语句
	 * @return 查询资源句柄
	 */
	private function execute($sql) {
		if(!is_object($this->link)) {
			$this->connect();
		}
        $this->pdos = $this->link->prepare($sql);
        $bool = $this->pdos->execute();
        if ('00000' !== $this->pdos->errorCode()) {
            $this->halt($sql);
        }
        return $bool;
	}


	public function halt($msg = '', $sql = '') {
		// 判断是否开启了调试
		if($this->config['debug']) {
	        $error_info = $this->pdos->errorInfo();
	        $s = '<pre>';
	        $s .= '<b>Error:</b>' . $error_info[2] . '<br />';
	        $s .= '<b>Errno:</b>' . $error_info[1] . '<br />';
	        $s .= '<b>Sql:</b>' . $sql.'</pre>';
	        exit($s);
	    }else{
	    	return false;
	    }
    }

	/**
	 * 执行sql查询
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件[例`name`='$name']
	 * @param $limit 		返回结果范围[例：10或10,10 默认为空]
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @param $key 			返回数组按键名排序
	 * @return array		查询结果集数组
	 */
	public function select($data, $table, $where = '', $limit = '', $order = '', $group = '', $key = '') {
		$where = $where == '' ? '' : ' WHERE '.$where;
		$order = $order == '' ? '' : ' ORDER BY '.$order;
		$group = $group == '' ? '' : ' GROUP BY '.$group;
		$limit = $limit == '' ? '' : ' LIMIT '.$limit;
		$field = explode(',', $data);
		array_walk($field, array($this, 'add_special_char'));
		$data = implode(',', $field);
		$sql = 'SELECT '.$data.' FROM `'.$this->config['database'].'`.`'.$table.'`'.$where.$group.$order.$limit;
		$this->execute($sql);
		return $this->pdos->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * 获取单条记录查询
	 * @param $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
	 * @param $table 		数据表
	 * @param $where 		查询条件
	 * @param $order 		排序方式	[默认按数据库默认方式排序]
	 * @param $group 		分组方式	[默认为空]
	 * @return array/null	数据查询结果集,如果不存在，则返回空
	 */
	public function get_one($data, $table, $where = '', $order = '', $group = '') {
		$where = $where == '' ? '' : ' WHERE '.$where;
		$order = $order == '' ? '' : ' ORDER BY '.$order;
		$group = $group == '' ? '' : ' GROUP BY '.$group;
		$limit = ' LIMIT 1';
		$field = explode( ',', $data);
		array_walk($field, array($this, 'add_special_char'));
		$data = implode(',', $field);
		$sql = 'SELECT '.$data.' FROM `'.$this->config['database'].'`.`'.$table.'`'.$where.$group.$order.$limit;
		$this->execute($sql);
		return $this->pdos->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * 遍历查询结果集
	 * @param $type		返回结果集类型	
	 * 					MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
	 * @return array
	 */
	public function fetch_next() {
		$res = $this->pdos->fetchAll(PDO::FETCH_ASSOC);
		if(!$res) {
			$this->free_result();
		}
		return $res;
	}
	
	/**
	 * 释放查询资源
	 * @return void
	 */
	public function free_result() {
		if(is_object($this->pdos)) {
			$this->link = null;
			$this->pdos = null;
		}
	}
	
	/**
	 * 直接执行sql查询
	 * @param $sql							查询sql语句
	 * @return	boolean/query resource		如果为查询语句，返回资源句柄，否则返回true/false
	 */
	public function query($sql) {
		$this->execute($sql);
		return $this->pdos->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * 执行添加记录操作
	 * @param $data 		要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param $table 		数据表
	 * @return boolean
	 */
	public function insert($data, $table, $return_insert_id = false, $replace = false) {
		if(!is_array( $data ) || $table == '' || count($data) == 0) {
			return false;
		}
		
		$fielddata = array_keys($data);
		$valuedata = array_values($data);
		array_walk($fielddata, array($this, 'add_special_char'));
		array_walk($valuedata, array($this, 'escape_string'));
		
		$field = implode (',', $fielddata);
		$value = implode (',', $valuedata);

		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$sql = $cmd.' `'.$this->config['database'].'`.`'.$table.'`('.$field.') VALUES ('.$value.')';
		$this->execute($sql);
		// 改过后，一直是返回最后一个ID
		return $return_insert_id ? $this->insert_id() : $this->pdos;
	}

	/**
	 * 执行批量添加记录操作
	 * @param $data 		要增加的数据，参数为二级数组。二维数组中：key为字段值，数组值为数据取值
	 * @param $table 		数据表
	 * @return boolean
	 */
	public function insert_all($data, $table, $return_insert_id = false, $replace = false) {
		if(!is_array( $data ) || $table == '' || count($data) == 0) {
			return false;
		}
		
		// 取出所有的数据
		$valuedata = array_values($data);
		$str = '(';
		foreach ($valuedata as $k => $v) {
			$vs = array_values($v);
			array_walk($vs, array($this, 'escape_string'));
			$str .= implode (',', $vs);
			$str .= '),(';
		}
		$str = substr($str,0,strlen($str) - 2);
		// 取字段
		$fielddata = array_keys($valuedata[0]);
		array_walk($fielddata, array($this, 'add_special_char'));
		$field = implode (',', $fielddata);

		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$sql = $cmd.' `'.$this->config['database'].'`.`'.$table.'`('.$field.') VALUES '.$str.'';
		$this->execute($sql);
		// 返回最后添加的那一行或者总行数，默认返回受影响的行数
		return $return_insert_id ? $this->insert_id() : $this->pdos;
	}
	
	/**
	 * 获取最后一次添加记录的主键号
	 * @return int 
	 */
	public function insert_id() {
		return $this->link->lastInsertId();
	}
	
	/**
	 * 执行更新记录操作
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='phpcms',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'phpcms','password'=>'123456')]
	 *						数组可使用array('name'=>'+=1', 'base'=>'-=1');程序会自动解析为`name` = `name` + 1, `base` = `base` - 1
	 * @param $table 		数据表
	 * @param $where 		更新数据时的条件
	 * @return boolean
	 */
	public function update($data, $table, $where = '') {
		if($table == '' or $where == '') {
			return false;
		}
		$where = ' WHERE '.$where;
		$field = '';
		if(is_string($data) && $data != '') {
			$field = $data;
		} elseif (is_array($data) && count($data) > 0) {
			$fields = array();
			foreach($data as $k=>$v) {
				switch (substr($v, 0, 2)) {
					case '+=':
						$v = substr($v,2);
						if (is_numeric($v)) {
							$fields[] = $this->add_special_char($k).'='.$this->add_special_char($k).'+'.$this->escape_string($v, '', false);
						} else {
							continue;
						}
						
						break;
					case '-=':
						$v = substr($v,2);
						if (is_numeric($v)) {
							$fields[] = $this->add_special_char($k).'='.$this->add_special_char($k).'-'.$this->escape_string($v, '', false);
						} else {
							continue;
						}
						break;
					default:
						$fields[] = $this->add_special_char($k).'='.$this->escape_string($v);
				}
			}
			$field = implode(',', $fields);
		} else {
			return false;
		}

		$sql = 'UPDATE `'.$this->config['database'].'`.`'.$table.'` SET '.$field.$where;
		return $this->execute($sql);
	}
	
	/**
	 * 执行删除记录操作
	 * @param $table 		数据表
	 * @param $where 		删除数据条件,不充许为空。
	 * 						如果要清空表，使用empty方法
	 * @return boolean
	 */
	public function delete($table, $where) {
		if ($table == '' || $where == '') {
			return false;
		}
		$where = ' WHERE '.$where;
		$sql = 'DELETE FROM `'.$this->config['database'].'`.`'.$table.'`'.$where;
		$this->execute($sql);
		// 返回受影响的总行数，因为，即使受影响行数为0，也会执行成功
		return $this->pdos->rowCount();
	}
	
	/**
	 * 获取最后数据库操作影响到的条数
	 * @return int
	 */
	public function affected_rows() {
		return $this->pdos->rowCount();
	}
	
	/**
	 * 获取数据表主键
	 * @param $table 		数据表
	 * @return array
	 */
	public function get_primary($table) {
		$this->execute("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_next()) {
			if($r['Key'] == 'PRI') break;
		}
		return $r['Field'];
	}

	/**
	 * 获取表字段
	 * @param $table 		数据表
	 * @return array
	 */
	public function get_fields($table) {
		$fields = array();
		$this->execute("SHOW COLUMNS FROM $table");
		while($r = $this->fetch_next()) {
			$fields[$r['Field']] = $r['Type'];
		}
		return $fields;
	}

	/**
	 * 检查不存在的字段
	 * @param $table 表名
	 * @return array
	 */
	public function check_fields($table, $array) {
		$fields = $this->get_fields($table);
		$nofields = array();
		foreach($array as $v) {
			if(!array_key_exists($v, $fields)) {
				$nofields[] = $v;
			}
		}
		return $nofields;
	}

	/**
	 * 检查表是否存在
	 * @param $table 表名
	 * @return boolean
	 */
	public function table_exists($table) {
		$tables = $this->list_tables();
		return in_array($table, $tables) ? 1 : 0;
	}
	
	public function list_tables() {
		$tables = array();
		$this->execute("SHOW TABLES");
		while($r = $this->fetch_next()) {
			$tables[] = $r['Tables_in_'.$this->config['database']];
		}
		return $tables;
	}

	/**
	 * 检查字段是否存在
	 * @param $table 表名
	 * @return boolean
	 */
	public function field_exists($table, $field) {
		$fields = $this->get_fields($table);
		return array_key_exists($field, $fields);
	}

	public function num_rows($sql) {
		$this->pdos = $this->execute($sql);
		return $this->pdos ? $this->pdos->rowCount() : 0;
	}

	public function num_fields($sql) {
		$this->pdos = $this->execute($sql);
		return $this->pdos ? count($this->pdos->fetch(PDO::FETCH_ASSOC)) : null;
	}

	public function version() {
		return $this->link->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	public function close() {
		$this->link = null;
	}

	/**
	 * 对字段两边加反引号，以保证数据库安全
	 * @param $value 数组值
	 */
	public function add_special_char(&$value) {
		if('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos ( $value, '`')) {
			//不处理包含* 或者 使用了sql方法。
		} else {
			$value = '`'.trim($value).'`';
		}
		if (preg_match("/\b(select|insert|update|delete)\b/i", $value)) {
			$value = preg_replace("/\b(select|insert|update|delete)\b/i", '', $value);
		}
		return $value;
	}
	
	/**
	 * 对字段值两边加引号，以保证数据库安全
	 * @param $value 数组值
	 * @param $key 数组key
	 * @param $quotation 
	 */
	public function escape_string(&$value, $key='', $quotation = 1) {
		if ($quotation) {
			$q = '\'';
		} else {
			$q = '';
		}
		$value = $q.$value.$q;
		return $value;
	}
}
?>