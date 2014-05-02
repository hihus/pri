<?php
include(dirname(dirname(__FILE__)).'/pri.inc.php');
$pri = $GLOBALS['pri'];

class pri_api{
	function __construct(){
		if(isset($_GET['_pri_mod']) && isset($_GET['_pri_func'])){
			$this->mod = $pri->mod($_GET['_pri_mod']);
			$this->mod->_lazyInit();
			$this->func = $_GET['_pri_func'];
		}else{
			echo 'err load ~';
			exit;
		}
	}
	function run(){
		if($this->aginstAttact()){
			echo 'err attact ~';
			exit;
		}
		$args = unserialize($_GET['_pri_data']);
		echo serialize(call_user_func_array(array(&$this->mod,$this->func), $args));
		exit;
	}
	function aginstAttact(){
		if(!$this->checkSign()){
			return true;
		}
		return false;
	}
	function checkSign(){
		if(isset($_GET['_pri_sign'])){
			$conf = $this->mod->getSelfConfig('sign');
			if(isset($conf['sign']) && $conf['sign'] == $_GET['_pri_sign']){
				return true;
			}
		}
		return false;
	}
}

$api = new pri_api();
$api->run();