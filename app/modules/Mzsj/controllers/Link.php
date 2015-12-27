<?php
use modules\Mzsj\Api\Mzsj;
class LinkController extends Mzsj
{
	public function init()
	{
		parent::init();
		$this->link_db = new \LinkModel();
	}
	/*
	* 友情链接列表
	*/
	public function indexAction()
	{
		$page = $this->getRequest()->get('page');
		$linklist = $this->link_db->listinfo('','linkid DESC',$page,20);
	    $pages = $this->link_db->pages;
	    $this->getView()->assign("linklist",$linklist);
    	$this->getView()->assign("pages",$pages);
		$this->ttpl("友情链接列表",'link/index.html');
	}
	/*
	* 添加友情链接
	*/
	public function addlinkAction()
	{
		if (isset($_POST['dosubmit']))
		{
			$data = $this->checkinput($this->getRequest()->getpost('info'));
			$data['starttime'] = $data['starttime'] ? strtotime($data['starttime']) : 0;
			$data['endtime'] = $data['endtime']? strtotime($data['endtime']) : 0;
			// 判断是否存在同名
			$ishav = $this->link_db->get_one("name = '".$data['name']."' || url = '".$data['url']."'",'*');
			if ($ishav){$this->msg('名称或者网址已经存在，请先检查！');}
			$res = $this->link_db->insert($data);
			if ($res) {
				$msg['content'] = '添加友情链接成功';
			}
			else
			{
				$msg['content'] = '添加友情链接失败';
			}
			// 添加日志
			$this->addlog('linkid = '.$res);
			$msg['url'] = 'Mzsj/Link/index';
			$this->msg($msg['content'],$msg['url']);
		}
		else
		{
			$this->ttpl('添加友情链接','link/addlink.html');
		}
	}
	/*
	* 修改友情链接
	*/
	public function editlinkAction()
	{
		$lid = $this->getRequest()->get('lid');
		if (!$lid){$this->msg('参数错误！');}
		if (isset($_POST['dosubmit']))
		{
			$data = $this->checkinput($this->getRequest()->getpost('info'));
			$data['starttime'] = $data['starttime'] ? strtotime($data['starttime']) : 0;
			$data['endtime'] = $data['endtime']? strtotime($data['endtime']) : 0;
			// 判断是否存在同名
			$ishav = $this->link_db->get_one("(name = '".$data['name']."' || url = '".$data['url']."') and linkid != '".$lid."'",'*');
			if ($ishav){$this->msg('名称或者网址已经存在，请先检查！');}
			$res = $this->link_db->update($data,array('linkid'=>$lid));
			if ($res) {
				$msg['content'] = '修改友情链接成功';
			}
			else
			{
				$msg['content'] = '修改友情链接失败';
			}
			// 添加日志
			$this->addlog('linkid = '.$res);
			$msg['url'] = 'Mzsj/Link/index';
			$this->msg($msg['content'],$msg['url']);
		}
		else
		{
			$linkinfo = $this->link_db->get_one(array('linkid'=>$lid),'*');
			$this->getView()->assign('linkinfo',$linkinfo);
			$this->ttpl('修改友情链接','link/editlink.html');
		}
	}
	/*
	* 删除友情链接
	*/
	public function dellinkAction()
	{
		$lid = $this->getRequest()->get('lid');
		$lids = $this->getRequest()->get('linkids');
		if (!$lid && !is_array($lids)) {
			$this->msg('参数错误！');
		}
		else
		{
			if ($lid && !is_array($lids)) {
				$this->link_db->delete(array('linkid'=>$lid));
				$this->addlog('linkid = '.$lid);
			}
			if (!$lid && is_array($lids)) {
				$lids = implode(',', $lids);
				$this->link_db->delete("linkid in ('".$lids."')");
				$this->addlog('linkid = '.$lids);
			}
			$this->msg('删除成功','Mzsj/Link/index');
		}
	}
}