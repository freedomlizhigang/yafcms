<?php

class Security {
	// 输入安全处理
	public function checkinput($data,$name = null){
		// 先判断是不是数组
		if (is_array($data)) {
			foreach($data as $k => $v)
			{
				if(is_array($v))
				{
					exit($k.'是个数组，不能直接取多级数组！');
				}
				// 开始进行安全处理,1、去除前后空格；2、格式化js代码；3、部分字段去除php/html/xml标记，以及html转义;
				$data[$k] = $this->trim_str($v);
				$data[$k] = $this->trim_script($v);
				$data[$k] = $this->safe_replace($v,$k);
				$data[$k] = $this->istextarea($v,$k);
				$data[$k] = $this->trim_str($v);
			}
		}
		else {
			// 开始进行安全处理,1、去除前后空格；2、格式化js代码；3、部分字段去除php/html/xml标记，以及html转义;
			$data = $this->trim_str($data);
			$data = $this->trim_script($data);
			$data = $this->safe_replace($data,$name);
			$data = $this->istextarea($data,$name);
			$data = $this->trim_str($data);
		}
		return $data;
	}
	
	/*
	除富文本字段外所有字段去除php/html/xml标记，富文本进行html转义
	*/
	public function istextarea($data,$k = ''){
		if ('content' != substr($k, 0,7)) {
			$data = $this->new_html_special_chars($this->trim_script($data));
		}else{
			$data = $this->remove_xss(strip_tags($data, '<p><a><br><img><ul><li><div>'));
		}
		return $data;
	}
	
	/**
	* 返回经htmlspecialchars处理过的字符串或数组
	* @param $obj 需要处理的字符串或数组
	* @return mixed
	*/
	public function new_html_special_chars($string) {
		$encoding = 'utf-8';
		if(strtolower(CHARSET)=='gbk') $encoding = 'ISO-8859-15';
		if(!is_array($string)) return htmlspecialchars($string,ENT_QUOTES,$encoding);
		foreach($string as $key => $val) $string[$key] = $this->new_html_special_chars($val);
		return $string;
	}
	
	/**
	* 格式化文本域内容
	*
	* @param $string 文本域内容
	* @return string
	*/
	public function trim_str($string) {
		$string = nl2br(str_replace (' ', '&nbsp;', trim($string)));
		return $string;
	}
	/**
	* 安全过滤函数
	*
	* @param $string
	* @return string
	*/
	public function safe_replace($string,$name = null) {
		if ($name == 'title' || $name == 'name' || $name == 'keywords' || $name == 'describe' || $name == 'tags') {
			$string = str_replace('%20','',$string);
			$string = str_replace('%27','',$string);
			$string = str_replace('%2527','',$string);
			$string = str_replace('*','',$string);
			$string = str_replace('"','&quot;',$string);
			$string = str_replace("'",'',$string);
			$string = str_replace('"','',$string);
			$string = str_replace(';','',$string);
			$string = str_replace('<','&lt;',$string);
			$string = str_replace('>','&gt;',$string);
			$string = str_replace("{",'',$string);
			$string = str_replace('}','',$string);
			$string = str_replace('\\','',$string);
		}
		return $string;
	}
	/**
	* 转义 javascript 代码标记
	*
	* @param $str
	* @return mixed
	*/
	public function trim_script($str) {
		if(is_array($str)){
			foreach ($str as $key => $val){
				$str[$key] = $this->trim_script($val);
			}
		}else{
			$str = preg_replace ( '/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str );
			$str = preg_replace ( '/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str );
			$str = preg_replace ( '/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str );
			$str = str_replace ( 'javascript:', 'javascript：', $str );
		}
		return $str;
	}
	/**
	* xss过滤函数
	*
	* @param $string
	* @return string
	*/
	public function remove_xss($string) { 
		$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);

		$parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');

		$parm2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');

		$parm = array_merge($parm1, $parm2); 

		for ($i = 0; $i < sizeof($parm); $i++) { 
			$pattern = '/'; 
			for ($j = 0; $j < strlen($parm[$i]); $j++) { 
				if ($j > 0) { 
					$pattern .= '('; 
					$pattern .= '(&#[x|X]0([9][a][b]);?)?'; 
					$pattern .= '|(&#0([9][10][13]);?)?'; 
					$pattern .= ')?'; 
				}
				$pattern .= $parm[$i][$j]; 
			}
			$pattern .= '/i';
			$string = preg_replace($pattern, ' ', $string); 
		}
		return $string;
	}

}
