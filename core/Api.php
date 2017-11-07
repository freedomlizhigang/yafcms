<?php
/*
*   Api调用的基类，从Basic类中简化来，没有必要时cache/session等都不进行加载
*/
// 加载公用函数
class Api extends Yaf\Controller_Abstract
{
    // 取POST参数
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
    // 判断请求是否是POST
    protected function is_post()
	{
		return $this->getRequest()->isPost();
	}
    // 输出json
    protected function resJson($code = 0,$msg = '',$result = '')
    {
        exit(json_encode(['code'=>$code,'msg'=>$msg,'result'=>$result]));
    }
}