<?php
class BmPlugin extends Yaf\Plugin_Abstract {

	protected $bm;

	public function __construct()
	{
		// 基准测试用的,mark为标记开始与结束时间
		$this->bm = new \Benchmark();
	}

	public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
		$this->bm->mark('total_time_start');
	}
	public function preDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
		$this->bm->mark($request->getModuleName().'/'.$request->getControllerName().'/'.$request->getActionName().'_start');
	}
	public function postDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
		$this->bm->mark($request->getModuleName().'/'.$request->getControllerName().'/'.$request->getActionName().'_end');
	}
	public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
		$this->bm->mark('total_time_end');
		$pro = new \Profiler($this->bm);
		$pro->run();
	}
}