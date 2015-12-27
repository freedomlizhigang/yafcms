<?php
/*
* 更新缓存的工具类
*/
namespace lib;
class CacheAll
{
	private $cache;
	public function __construct()
	{
		$this->cache = new \Cache();
	}
	/*
	* 更新及获得缓存 角色表缓存
	*/
	public function setrolecache($name = 'role',$dir = 'admin',$where = '',$fields = '*',$num = '100')
	{
		$role_db = new \RoleModel();
		$allrole = $role_db->select($where,$fields,$num);
		$tmp = array();
		foreach ($allrole as $key => $value) {
			$tmp[$value['roleid']] = $value;
		}
		$this->cache->set($name,$tmp,$dir);
	}
	/*
	* 管理员表缓存
	*/
	public function setadmincache($name = 'admin',$dir = 'admin',$where = '',$fields = '*',$num = '1000')
	{
		$admin_db = new \AdminModel();
		$alladmin = $admin_db->select($where,$fields,$num);
		$tmp = array();
		foreach ($alladmin as $key => $value) {
			$tmp[$value['adminid']] = $value;
		}
		$this->cache->set($name,$tmp,$dir);
	}
	/*
	* 站点缓存
	*/
	public function setsitecache($name = 'site',$dir = 'site',$where = '',$fields = '*',$num = '100')
	{
		$site_db = new \SiteModel();
		$alladmin = $site_db->select($where,$fields,$num);
		$tmp = array();
		foreach ($alladmin as $key => $value) {
			$tmp[$value['siteid']] = $value;
		}
		$this->cache->set($name,$tmp,$dir);
	}
	// 更新栏目缓存
	public function setcatecache($name = 'cate',$dir = 'category',$where='',$fields='*',$num='10000'){
		$cate_db = new \CategoryModel();
		$categorys = array();
		$categorys = $cate_db->select($where,$fields,$num,'catid ASC');
		// 将数组索引转化为typeid，phpcms v9的select方法支持定义数组索引，这个坑花了两小时
		$categorys = $this->get_categorys($categorys,'catid');
		if(is_array($categorys)) {
			foreach($categorys as $catid => $cat) {
				// 取得所有父栏目
				$arrparentid = $this->get_arrparentid($catid,$categorys);
				$arrchildid = $this->get_arrchildid($catid,$categorys);
				$child = is_numeric($arrchildid) ? 0 : 1;
				// 如果父栏目数组、子栏目数组，及是否含有子栏目不与原来相同，更新
				if($categorys[$catid]['arrparentid']!=$arrparentid || $categorys[$catid]['arrchildid']!=$arrchildid || $categorys[$catid]['child']!=$child){
					$cate_db->update(array('catid'=>$catid,'arrparentid'=>$arrparentid,'arrchildid'=>$arrchildid,'child'=>$child),array('catid'=>$catid));
				}
			}
		}
		//删除在非正常显示的栏目
		foreach($categorys as $cat) {
			if($cat['parentid'] != 0 && !isset($categorys[$cat['parentid']])) {
				$cate_db->delete(array('catid'=>$cat['catid']));
			}
		}
		$newlist = $cate_db->select($where,$fields,$num,'catid ASC');
		$tmparr = array();
		foreach ($newlist as $v) {
			$tmparr[$v['catid']] = $v;
		}

		$this->cache->set($name,$tmparr,$dir);
	}
	/**
	 * 以索引重排结果数组
	 * @param array $categorys
	 * $zhujian 主键
	 */
	private function get_categorys($categorys = array() ,$zhujian = '') {
		if (is_array($categorys) && !empty($categorys)) {
			$temparr = array();
			foreach ($categorys as $c) {
				// 以主键做为数组索引
				$temparr[$c[$zhujian]] = $c;
			}
		} 
		return $temparr;
	}
	/**
	 * 
	 * 获取父栏目ID列表
	 * @param integer $catid              栏目ID
	 * @param array $arrparentid          父目录ID
	 * @param integer $n                  查找的层次
	 */
	private function get_arrparentid($catid, $categorys, $arrparentid = '', $n = 1) {
		if($n > 5 || !is_array($categorys) || !isset($categorys[$catid])) return false;
		$parentid = $categorys[$catid]['parentid'];
		$arrparentid = $arrparentid ? $parentid.','.$arrparentid : $parentid;
		// 父ID不为0时
		if($parentid) {
			$arrparentid = $this->get_arrparentid($parentid, $categorys, $arrparentid, ++$n);
		} else {
			// 如果父ID为0
			$categorys[$catid]['arrparentid'] = $arrparentid;
		}
		$parentid = $categorys[$catid]['parentid'];
		return $arrparentid;
	}
	/**
	 * 
	 * 获取子栏目ID列表
	 * @param $catid 栏目ID
	 */
	private function get_arrchildid($catid, $categorys) {
		$arrchildid = $catid;
		if(is_array($categorys)) {
			foreach($categorys as $id => $cat) {
				if($cat['parentid'] && $id != $catid && $cat['parentid']==$catid) {
					$arrchildid .= ','.$this->get_arrchildid($id, $categorys);
				}
			}
		}
		return $arrchildid;
	}
}