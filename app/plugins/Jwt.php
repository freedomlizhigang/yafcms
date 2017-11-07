<?php
class JwtPlugin extends Yaf\Plugin_Abstract
{
	// 路由完成之前检查权限
	public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {
		// 取得uri，也就是网址，根据网址来决定是否进行进行权限判断
		$uri = explode('/',trim($request->getRequestUri(),'/'));
		if ($uri[0] == 'api' && $uri[1] != 'login') {
			// 加载文件
			LoadClass('lib/Jwt');
			$jwt = new Jwt();
			// 取得token
			$token = $request->get('token');
			$data = $jwt->decode($token);
			var_dump($data);
			$id = $jwt->getsub($token,'id');
			var_dump($id);
			var_dump($jwt->reftoken($token));
		}		
     }
	// 路由完成之后检查权限
	public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
	{
	}
}