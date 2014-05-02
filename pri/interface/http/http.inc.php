<?php

/**
* http接口调用基础类
* @author hihu
*/
class http_inc extends pri_interface{
	private $_socks = array();
	private $_http;
	function init(){
		$this->init_curl();
	}
	function __destruct(){
		curl_close($this->_http);
	}
	function init_curl(){
		$this->_http = curl_init();
		curl_setopt($this->_http, CURLOPT_POST, 1);
		curl_setopt ($this->_http, CURLOPT_HEADER , 1 );
	}
	function getReq($mod,$func,$args){
		$rs = NULL;
		$rpc = $this->getConfig('rpc',$mod);
		$req_type = isset($rpc['req_type']) ? $rpc['req_type'].'://': 'http://';
		$host = isset($rpc['host']) ? $rpc['host']: 'pri.'.$this->getConfig('domain',$mod);
		$port = isset($rpc['port']) ? ':'.$rpc['port']: '';
		$timeout = isset($rpc['timeout']) ? $rpc['timeout'] : 300;
		$sign  = isset($rpc['sign']) ? $rpc['sign'] : '';
		$_GET['_pri_sign'] = $sign;
		if(isset($rpc['api']) && $rpc['api'] != ''){
			$uri = $rpc['api'];
		}else{
			$_GET['_pri_mod'] = $mod;
			$_GET['_pri_func'] =$func;
			$uri = '/'.PRI_API_INDEX;
		}
		$uri = $this->addGet($uri);
		// $headers = array(
		// 	'Host'=>$host.$port,
		// 	'sign'=> isset($rpc['sign'],
		// 	);
		// $headers = $this->buildHeader($headers);
		// $_POST['_pri_sign'] = $rpc['sign'];
		//$_POST['_pri_data'] = serialize($args);
		// $data = $this->buildParams($_POST);
		// $data = 'POST ' . $uri . " HTTP/1.1\r\n".$headers . "\r\n" . $data;
		$url = $req_type.$host.$port.'/'.$uri;
		$header = array(
			'Host'=>$host.$port,
			);
		$data = serialize($args);
		return $this->getResult($url,$header,$data);
	}

	function addGet($uri){
		if(!empty($_GET)){
			$uri .= '?'.http_build_query($_GET);
		}
		return $uri;
	}
	function buildHeader($arr){
		$h = '';
		foreach($arr as $k => $v)
			$h .= $k . ': ' . $v . "\r\n";
		return $h;
	}
	function buildParams($arr){
		return $this->buildHeader($arr);
	}
	function getResult($url,$header,$data){
		$rs = $this->getCurlResult($url,$header,$data);
		var_dump($rs);
	}
	function getCurlResult($url,$header,$data){
		curl_setopt($this->_http, CURLOPT_URL, $url);
		$_POST['_pri_data'] = serialize($data);
		$_POST['_sec_req_pri'] = '1';
		curl_setopt ($this->_http, CURLOPT_HTTPHEADER, $header);
		curl_setopt($this->_http, CURLOPT_POSTFIELDS, $_POST);
		return  curl_exec($this->_http);
	}
}