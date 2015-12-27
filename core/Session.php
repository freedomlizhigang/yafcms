<?php
/**
 *  session mysql 数据库存储类
 *
 * @copyright			(C) 2005-2010 PHPCMS
 * @license				http://www.phpcms.cn/license/
 * @lastmodify			2010-6-8
 */
class Session implements SessionHandlerInterface{
	var $lifetime = 1800;
	var $db;
	var $table;
/**
 * 构造函数
 * 
 */
    public function __construct() {
		$this->db = new \SessiondbModel();
		$this->lifetime = \Yaf\Registry::get('config')['session_time'];
    	session_set_save_handler(array(&$this,'open'), array(&$this,'close'), array(&$this,'read'), array(&$this,'write'), array(&$this,'destroy'), array(&$this,'gc'));
    }
/**
 * session_set_save_handler  open方法
 * @param $save_path
 * @param $session_name
 * @return true
 */
    public function open($save_path, $session_name) {
		return true;
    }
/**
 * session_set_save_handler  close方法
 * @return bool
 */
    public function close() {
        return $this->gc($this->lifetime);
    } 
/**
 * 读取session_id
 * session_set_save_handler  read方法
 * @return string 读取session_id
 */
    public function read($id) {
		$r = $this->db->get_one(array('sessionid'=>$id), 'data');
		return $r ? $r['data'] : '';
    } 
/**
 * 写入session_id 的值
 * 
 * @param $id session
 * @param $data 值
 * @return mixed query 执行结果
 */
    public function write($id, $data) {
		if(strlen($data) > 255) $data = '';
		$ip = get_ip();
		$sessiondata = array(
							'sessionid'=>$id,
							'ip'=>$ip,
							'lastvisit'=>time(),
							'data'=>$data,
						);
		$this->db->insert($sessiondata,true,1);
		// 直接返回true，这里就是个坑，insert用PDO会默认处理成事务的，一直返回0,
		return true;
    }
/** 
 * 删除指定的session_id
 * 
 * @param $id session
 * @return bool
 */
    public function destroy($id) {
    	$this->db->delete(array('sessionid'=>$id));
		return true;
    }
/**
 * 删除过期的 session
 * 
 * @param $maxlifetime 存活期时间
 * @return bool
 */
   public function gc($maxlifetime) {
		$expiretime = time() - $maxlifetime;
		$this->db->delete("`lastvisit`<$expiretime");
		return true;
    }
}
?>