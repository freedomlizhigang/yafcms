<?php
use modules\Mzsj\Api\Mzsj;
class ContentController extends Mzsj 
{
	public function init()
	{
		parent::init();
		$this->cate_db = new CategoryModel();
		$this->art_db = new ArticleModel();
	}
	/*
	* 文章列表
	*/
	public function indexAction(){
		$sql = '';
		$q = trim($this->getRequest()->get('q',''));
		$catid = $this->getRequest()->get('catid','');
		if (!empty($q)) {
			$sql .= " title like '%".$q."%'";
		}
		if (!empty($catid)) {
			$sql .= ' catid = '.$catid;
		}
		$this->getView()->assign('catid',$catid);
		$page = (int)$this->getRequest()->get('page',1);
		$artlist = $this->art_db->listinfo($sql,'artid DESC',$page,20);
		$artlist = $this->fieldtoname($artlist,'status','statusname','正常','禁用');
		$artlist = $this->fieldtoname($artlist,'islink','islinkname','是','否');
		$artlist = $this->fieldtoname($artlist,'posid','posidname','是','否');
		$pages = $this->art_db->pages;
		$this->getView()->assign('artlist',$artlist);
		$this->getView()->assign('pages',$pages);
		// 栏目，单网页排除
		$tree = $this->list_to_tree($this->cate_db,'catid','parentid',0);
		$temparr = array();
		foreach ($tree as $v) {
			if($v['ispage'] != 1){
				$temparr[$v['catid']]['catid'] = $v['catid'];
				$temparr[$v['catid']]['catname'] = $v['catname'];
				$temparr[$v['catid']]['nbsp'] = $v['nbsp'];
			}
		}
		$tree = $temparr;
		$this->getView()->assign('catetree',$tree);
		$this->ttpl('文章列表','content/index.html');
	}
	/*
	* 添加文章
	*/
	public function addarticleAction()
	{
		if (isset($_POST['dosubmit'])) {
			$data = $this->checkinput($this->getRequest()->getpost('info'));
			// 更新时间
			$data['updatetime'] = $data['updatetime'] ? strtotime($data['updatetime']) : time();
			$data['inputtime'] = $data['inputtime'] ? strtotime($data['inputtime']) : time();
			// 判断是否有重名或者重名目录
			$ishavsql = "title = '".$data['title']."'";
			if (is_array($this->art_db->get_one($ishavsql,'title,artid')))
			{
				$this->msg('文章标题已经存在，请换一个再试！');
			}
			else
			{
				// 判断栏目下文章是否要审核，0为不审核
				$cate = $this->cache->get('cate','category');
				if ($cate[$data['catid']]['shenhe'] != 0)
				{
					$data['status'] = 0;
				}
				else
				{
					$data['status'] = 1;
				}
				// 添加文章
				$artid = $this->art_db->insert($data);
				if ($artid) {
					$msg['content'] = '添加文章成功！';
				}
				else
				{
					$msg['content'] = '添加文章失败！';
				}
				// 日志
				$this->addlog('artid = '.$artid);
				// 拼接跳转的URL
				$msg['url'] = '/Mzsj/Content/index';
				$this->msg($msg['content'],$msg['url']);
			}
		}
		else
		{
			// 栏目，单网页排除
			$tree = $this->list_to_tree($this->cate_db,'catid','parentid',0);
			$temparr = array();
			foreach ($tree as $v) {
				if($v['ispage'] != 1){
					$temparr[$v['catid']]['catid'] = $v['catid'];
					$temparr[$v['catid']]['catname'] = $v['catname'];
					$temparr[$v['catid']]['nbsp'] = $v['nbsp'];
				}
			}
			$tree = $temparr;
			$this->getView()->assign('catetree',$tree);
			$this->ttpl('添加文章','content/addarticle.html');
		}
	}
	public function editarticleAction()
	{
		$aid = (int)$this->getRequest()->get('aid',0);
		if ($aid) {
			if (isset($_POST['dosubmit']))
			{
				$data = $this->checkinput($this->getRequest()->getpost('info'));
				// 判断是否有重名或者重名目录
				$ishavsql = "title = '".$data['title']."'";
				$hav = $this->art_db->get_one($ishavsql,'title,artid,catid');
				if ($hav['artid'] != $aid && is_array($hav))
				{
					$this->msg('文章标题已经存在，请换一个再试！');
				}
				else
				{
					// 更新时间
					$data['updatetime'] = $data['updatetime'] ? strtotime($data['updatetime']) : time();
					$data['inputtime'] = $data['inputtime'] ? strtotime($data['inputtime']) : time();
					$res = $this->art_db->update($data,array('artid'=>$aid));
					if ($res) {
						$msg['content'] = "修改文章成功";
					}else{
						$msg['content'] = "修改文章失败";
					}
					// 记录用户行为
					$this->addlog("artid=$aid");
					// 拼接跳转的URL
					$msg['url'] = '/Mzsj/Content/index';
					$this->msg($msg['content'],$msg['url']);
				}
			}
			else
			{
				$artinfo = $this->art_db->get_one(array('artid'=>$aid),'*');
				$this->getView()->assign('artinfo',$artinfo);
				// 栏目，单网页排除
				$tree = $this->list_to_tree($this->cate_db,'catid','parentid',0);
				$temparr = array();
				foreach ($tree as $v) {
					if($v['ispage'] != 1){
						$temparr[$v['catid']]['catid'] = $v['catid'];
						$temparr[$v['catid']]['catname'] = $v['catname'];
						$temparr[$v['catid']]['nbsp'] = $v['nbsp'];
					}
				}
				$tree = $temparr;
				$this->getView()->assign('catetree',$tree);
				$this->ttpl('修改文章','content/editarticle.html');
			}
		}
		else
		{
			$this->msg('参数错误');
		}
	}
	/*
	* 删除文章功能
	*/
	public function delarticleAction()
	{
		$aid = (int)$this->getRequest()->get('aid');
		$aids = $this->getRequest()->get('aids');
		// 单个删除
		if ($aid && empty($aids))
		{
			$this->art_db->delete(array('artid'=>$aid));
			// 加记录
			$this->addlog('artid = '.$aid);
			$this->msg('删除文章成功');
		}
		// 批量删除
		elseif(!$aid && is_array($aids))
		{
			$str = implode(',',$aids);
			$this->art_db->delete("artid in (".$str.")");
			// 加记录
			$this->addlog('artid = '.$str);
			$this->msg('删除文章成功');
		}
		// 没有参数
		else
		{
			$this->msg('参数错误');
		}
	}
	/*
	* 文章审核
	*/
	public function shenheartAction()
	{
		$aid = $this->getRequest()->get('aid');
		$status = $this->getRequest()->get('status');
		if (empty($aid) || $status == null)
		{
			$this->msg('参数错误');
		}
		else
		{
			$data['status'] = ($status == 0) ? 1 : 0;
			$res = $this->art_db->update($data,array('artid'=>$aid));
			if ($res)
			{
				$this->addlog('artid = '.$aid);
				$this->msg('审核成功');
			}
			else
			{
				$this->msg('审核失败');
			}
		}
	}
	/*
	* 查看文章
	*/
	public function showartAction()
	{
		$aid = $this->getRequest()->get('aid');
		if (!$aid)
		{
			$this->msg('参数错误');
		}
		else
		{
			$artinfo = $this->art_db->get_one(array('artid'=>$aid),'*');
			// 富文本内容转义为html
			$artinfo['content'] = htmlspecialchars_decode($artinfo['content']);
			$this->getView()->assign('artinfo',$artinfo);
			$this->getView()->assign('catname',$this->cache->get('cate','category'));
			$this->ttpl('查看文章','content/showart.html');
		}
	}
	/*
	* 栏目列表
	*/
	public function cateAction()
	{
		$tree = $this->list_to_tree($this->cate_db,'catid','parentid',0);
		$tree = $this->numtoname($tree,'ismenu','是','否');
		$tree = $this->numtoname($tree,'ispage','是','否');
		$tree = $this->numtoname($tree,'islink','是','否');
		$this->getView()->assign('tree',$tree);
		$this->ttpl('栏目管理','content/cate.html');
	}
	/*
	* 添加栏目
	*/
	public function addcateAction($pid = 0)
	{
		$pid = (int)$this->getRequest()->getParam('pid',0);
		if (isset($_POST['dosubmit']))
		{
			$data = $this->checkinput($_POST['info']);
			// 判断是否有重名或者重名目录
			$ishavsql = "catname = '".$data['catname']."' or catdir = '".$data['catdir']."'";
			if (is_array($this->cate_db->get_one($ishavsql,'catname,catdir')))
			{
				$this->msg('栏目名称或栏目目录已经存在，请换一个再试！');
			}
			else
			{
				$catid = $this->cate_db->insert($data);
				if ($catid) {
					$msg['content'] = "添加栏目成功";
				}else{
					$msg['content'] = "添加栏目失败";
				}
				// 更新栏目缓存
				$this->cacheall->setcatecache();
				// 记录用户行为
				$this->addlog("catid=$catid");
				// 拼接跳转的URL
				$msg['url'] = '/Mzsj/Content/cate';
				$this->msg($msg['content'],$msg['url']);
			}
		}
		else
		{
			if($pid == 0){
                $level = 1;
            }else{
                $level = $this->cate_db->get_one(array('catid'=>$pid),'level');
                $level = $level['level'] + 1;
            }
           	$this->getView()->assign('level',$level);
           	$this->getView()->assign('pid',$pid);
			$this->ttpl('添加栏目','content/addcate.html');
		}
	}
	/*
	* 修改栏目
	*/
	public function editcateAction($cid = 0)
	{
		$cid = (int)$this->getRequest()->getParam('cid',0);
		if (!$cid) $this->msg('参数错误');
		if (isset($_POST['dosubmit']))
		{
			$data = $this->checkinput($_POST['info']);
			// 判断是否有重名或者重名目录
			$ishavsql = "catname = '".$data['catname']."' or catdir = '".$data['catdir']."'";
			$hav = $this->cate_db->get_one($ishavsql,'catname,catdir,catid');
			if ($hav['catid'] != $cid && is_array($hav))
			{
				$this->msg('栏目名称或栏目目录已经存在，请换一个再试！');
			}
			else
			{
				if(!empty($data['parentid']))
				{
					$update = $this->cate_db->get_one(array('catid'=>$data['parentid']),'*');
					$data['level'] = $update['level'] + 1;
				}
				else
				{
					$data['level'] = 1;
				}
				$res = $this->cate_db->update($data,array('catid'=>$cid));
				if ($res) {
					$msg['content'] = "修改栏目成功";
				}else{
					$msg['content'] = "修改栏目失败";
				}
				// 更新栏目缓存
				$this->cacheall->setcatecache();
				// 记录用户行为
				$this->addlog("catid=$cid");
				// 拼接跳转的URL
				$msg['url'] = '/Mzsj/Content/cate';
				$this->msg($msg['content'],$msg['url']);
			}
		}
		else
		{
			$tree = $this->list_to_tree($this->cate_db,'catid','parentid',0);
			$this->getView()->assign('catetree',$tree);
			$cateinfo = $this->cate_db->get_one(array('catid'=>$cid),'*');
			$this->getView()->assign('cateinfo',$cateinfo);
			$this->ttpl('修改栏目','content/editcate.html');
		}
	}
	/*
	* 删除栏目
	*/
	public function delcateAction()
	{
		if (isset($_POST['dosubmit'])) {
			$cids = $_POST['cids'];
			foreach ($cids as $c) {
				$this->delchildcat($c);
			}
			// 记录用户行为
			$this->addlog("catid=$cids");
		}
		else
		{
			// cid判断是删除一个还是多个
			$cid = (int)$this->getRequest()->getParam('cid',0);
			if ($cid) {
				// 循环删除所有子栏目
				$this->delchildcat($cid);
				// 记录用户行为
				$this->addlog("catid=$cid");
			}
			else
			{
				$this->msg('参数错误');
			}
		}
		// 更新栏目缓存
		$this->cacheall->setcatecache();
		$this->msg('删除栏目成功！');
	}
	// 循环删除子栏目
	public function delchildcat($cid = 0)
	{
		// 找出所有子栏目及自身
		$strchild = $this->cate_db->get_one(array('catid'=>$cid),'catid,arrchildid');
		$arrchild = explode(",", $strchild['arrchildid']);
		// 判断子栏目或者自身下是否有文章
		foreach ($arrchild as $c) {
			if ($this->art_db->get_one(array('catid'=>$c),'artid,catid')) {
				$this->msg('栏目ID为 '.$c.' 的栏目下有文章，请先清空此栏目下的文章！');
				break;
			}
			else
			{
				continue;
			}
		}
		$this->cate_db->delete("catid in(".$strchild['arrchildid'].")");
	}

}