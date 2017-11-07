<?php

class ComPlugin extends Yaf\Plugin_Abstract
{
	// 分发开始前注册这个session，分发结束后注册不上~，坑
	public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
	{
	}
}