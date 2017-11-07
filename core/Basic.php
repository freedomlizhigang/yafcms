<?php
/*
* 后台继承的基类，这个类里加载的内容比较多一些
*/
// 加载公用函数
Yaf\Loader::import(APP_PATH."/app/common/com.func.php");
// file.php是session及cache中会用到的一些函数
Yaf\Loader::import(APP_PATH."/app/common/file.php");
class Basic extends Yaf\Controller_Abstract
{
    // 初始化一些常用类
    protected $cache = '';
    protected $session = '';
    protected function init()
    {
        // 关闭自动模板输出
        Yaf\Dispatcher::getInstance()->disableView();
        // 设置session，一定要在加载公用函数后，在这里实例化只会被实例化一次，这样实例化session是为在公用函数中使用
        $this->cache = new Cache();
        if(!Yaf\Registry::has('session'))
        {
            Yaf\Registry::set('session',new Session());
        }
        // $this->cache = new Cache();
        $this->session = Yaf\Registry::get('session');
    }
    // 取GET参数
    protected function getGet($name = null)
    {
        $security = new Security();
        if(is_null($name))
        {
            $data = $this->getRequest()->getQuery();
        }
        else
        {
            $name = explode('.',$name);
            if (count($name) > 1) {
                $data = $this->getRequest()->getQuery($name[0])[$name[1]];
            }
            else
            {
                $data = $this->getRequest()->getQuery($name[0]);
            }
            
        }
        return $security->checkinput($data,$name[1]);
    }
    // 取POST参数
    protected function getPost($name = null)
    {
        $security = new Security();
        if(is_null($name))
        {
            $data = $this->getRequest()->getPost();
        }
        else
        {
            $name = explode('.',$name);
            if (count($name) > 1) {
                $data = $this->getRequest()->getPost($name[0])[$name[1]];
            }
            else
            {
                $data = $this->getRequest()->getPost($name[0]);
            }
            
        }
        return $security->checkinput($data,$name[1]);
    }
    // 返回上一页
    public function goback($url = '')
    {
        if ($url == '') {
            $url = $this->getRequest()->getRequestUri();
        }
        $this->redirect($url);
    }
    // 输出模板
    protected function tpl($tmp = '',$data = '')
    {
        // $this->getView()->assign($data);
        $this->getView()->display($tmp);
    }
    // 判断请求是否是POST
    protected function is_post()
	{
		return $this->getRequest()->isPost();
	}
}