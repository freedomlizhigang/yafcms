<?php

use Yunhack\PHPValidator\Validator;
class IndexController extends Api {
	public function indexAction($a = '' ,$s = ''){
		/*$data = $this->getGet();
		Validator::make($data, [
		    "tel" => "string|mobile|to_type:scale:4|alias:user_account",
		    "age" => "integer_str|between:18,30|to_type:integer",
		    "vip_no" => "required_without_all:tel,age|length_between:12,32|regex:/^2016-07-29:[a-zA-Z0-9]*$/"
		],[
		    "vip_no.regex" => "错误的vip编号格式！"
		]);

		if (Validator::has_fails()) {
		    echo Validator::error_msg();
		    // exit;
		} else {
		    echo "参数校验通过(ok)";
		}*/
		
		$admin = new \AdminsModel();
		// $admin->transaction->beginTransaction();
		$all = $admin->get();
		// foreach ($all as $k => $v) {
		// 	echo $v->name."<br />";
		// }
		// $admin->transaction->commit();

		// $site = new \SiteModel();
		// $res = $site->select('','*');
		// var_dump($res);
		// echo "<br />";echo "<br />";

		// foreach ($res as &$value) {
		// 	var_dump($value['name']);
		// 	$res[0]['name'] .= '7777';
		// 	$res[1]['name'] = '7777';
			
		// 	echo "<br />";echo "<br />";
		// }
		// var_dump($res);

		// $db = \Yaf\Registry::get("dbconfig")['medoo'];
		// $medoo = new Medoo($db);
		// $all = $medoo->select('admins','*');

		

		// $admin = new AdminModel();
		// $all = $admin->select('*');
		// foreach ($all as $k => $v) {
		// 	echo $v['name']."<br />";
		// }
		$this->resJson(1,'sfda',$all);
	}
	/*
	* 上传图片
	* 先生成当天的上传子目录mkdir()，以此来分类图片
	*/
	public function uploadAction()
	{
		$config['upload_path'] = './uploads/'.date('Ymd').'/';
		$config['allowed_types'] = array('jpg','jpeg','gif','png','doc','docx','xls','xlsx','ppt','pptx','pdf','txt','rar','zip','swf','apk','mp4');
		$config['max_size'] = '102400'; //kb
		// $config['file_name'] = '';  //重命名方法
		$config['file_ext_tolower'] = true; //文件后缀名将转换为小写
		$config['encrypt_name'] = true; //文件名将会转换为一个随机的字符串
		$config['max_filename_increment'] = 10000; //同名文件最大自增数
		$upload = new Upload($config);
		/* 返回标准数据 */
        $return  = array('error' => 0, 'info' => '上传成功', 'data' => '');
        /* 记录附件信息 */
        // 字段是imgFile，由KindEditor生成的上传字段
		if(!$upload->do_upload('imgFile'))
        {
        	$return['error'] = 1;
            $return['message'] = $upload->display_errors();
        }
        else
        {
        	$return['url'] = '/uploads/'.date('Ymd').'/'.$upload->data()['file_name'];
        }
        exit(json_encode($return));
	}
}

?>