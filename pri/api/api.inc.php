<?php
ini_set('display_errors',1);
set_magic_quotes_runtime(0);
include(dirname(dirname(__FILE__)).'/pri.inc.php');

class pri_api{
	function __construct(){
		if(isset($_GET['_pri_mod']) && isset($_GET['_pri_func'])){
			$this->pri = $pri = $GLOBALS['pri'];
			$this->mod = $pri->load($_GET['_pri_mod']);
			$this->mod->_lazyInit($_GET['_pri_mod']);
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
		$args = unserialize(urldecode($_POST['_pri_data']));
		if(!is_array($args)) $args = array($args);
		echo serialize(call_user_func_array(array(&$this->mod->mod,$this->func),$args));
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
			$conf = $this->mod->getSelfConfig('rpc');
			if(isset($conf['sign']) && $conf['sign'] == $_GET['_pri_sign']){
				return true;
			}
		}
		return false;
	}
}

$api = new pri_api();
$api->run();