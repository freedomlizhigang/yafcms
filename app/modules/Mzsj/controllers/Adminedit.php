<?php
use modules\Mzsj\Api\Mzsj;
class AdmineditController extends Mzsj
{
	function init()
	{
		parent::init();
		$this->admin_db = new AdminModel();
	}
	/*
	* 修改个人信息
	*/
	public function editadminAction()
	{
		$aid = $this->session->get('mz_uid');
		if (isset($_POST['dosubmit']))
		{
			$data = $this->checkinput($_POST['info']);
			$res = $this->admin_db->update($data,array('adminid'=>$aid));
			if ($res)
			{
				$msg['content'] = '更新资料成功！';
			}
			else
			{
				$msg['content'] = '更新资料失败！';
			}
			// 记录信息，更新缓存
			$this->addlog('adminid = '.$aid);
			$this->cacheall->setadmincache();
			$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/'.$this->Aname;
			$this->msg($msg['content']);
		}
		else
		{
			$infos = $this->admin_db->get_one(array('adminid'=>$aid),'*');
			$this->getView()->assign('info',$infos);
			$this->ttpl('修改个人信息','adminedit/editadmin.html');
		}
	}
	/*
	* 修改密码
	*/
	public function editpasswordAction()
	{
		if (isset($_POST['dosubmit']))
		{
			// 找出老密码
			$aid = $this->session->get('mz_uid');
			$infos = $this->admin_db->get_one(array('adminid'=>$aid),'*');
			// 取得修改的数据，进行密码比较，确定老密码正确后进行修改
			$data = $this->checkinput($_POST);
			if ($infos['password'] != md5(md5($data['oldpassword'].$infos['encrypt'])))
			{
				$this->msg('老密码不正确，请再次修改！');
			}
			$edit['encrypt'] = create_randomstr();
			$edit['password'] = md5(md5($data['password'].$edit['encrypt']));
			$res = $this->admin_db->update($edit,array('adminid'=>$aid));
			if ($res)
			{
				// 记录信息
				$this->addlog('adminid = '.$aid);
				$this->session->del('mz_uid');
				$this->session->del('mz_uname');
				$this->session->del('mz_rid');
				$this->msg('更新密码成功，请重新登陆！','/mzsj/public/login','1');
			}
			else
			{
				$msg['content'] = '更新密码失败！';
				$msg['url'] = '/'.$this->Mname.'/'.$this->Cname.'/'.$this->Aname;
				$this->msg($msg['content'],$msg['url']);
			}
		}
		else
		{
			$this->ttpl('修改密码','adminedit/editpassword.html');
		}
	}
}