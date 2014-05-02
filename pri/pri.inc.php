<?php
/*
* author : hihu
* date   : 2014-04-27 
* main   : core class for the framework
*/
class pri {
	private static $_instance;
	private $_config = array();
	private $_modules = array();
	private $_libs = array();
	private $_server;

	private function __construct(){
		$this->getServer();
		$this->setDefaultConfig();
		$this->setDefaultModule();
	}

	private function __clone(){
		// some code here ...
	}

	public static function getInstance(){
		if(! (self::$_instance instanceof self )){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function load($module,$path = false){
		if($module == '') return false;
		if(isset($this->_modules[$module])){
			return $this->$module;
		}
		if(!$path){
			$path = PRI_INTERFACE_ROOT.$module.'/'.$module.'.inc.php';
			$class_name = $module.'_inc';
		}else{
			$class_name = $module;
		}
		if(!@file_exists($path) || !include($path)){
			die("error when load the ".$module." module~");
		}
		
		$this->$module = new $class_name();
		$this->$module->pri = $this;
		if(method_exists($this->$module,'init')){
			$this->$module->init();
		}
		$this->_modules[$module] = 1;
		$this->$module->mod_name = $module;
		return $this->$module;
	}

	public function loadf($func,$mod_name = false,$path = false){
		if($func == '') return false;
		if(!$mod_name){
			$backtrace = debug_backtrace();
			$mod_name = isset($backtrace[1]['class']) ? $backtrace[1]['class'] : die("can't loadf func~");
		}
		$mod_name = substr($mod_name,0,-4);
		if(isset($this->$mod_name->mod->$func)){
			return $this->$mod_name->mod->$func;
		}
		if(!$path){
			$path = PRI_FUNC_ROOT.$mod_name.'/'.$func.'.func.php';
			$class_name = $mod_name.'_'.$func;
		}else{
			$class_name = $func;
		}
		if(!@file_exists($path) || !include($path)){
			die("error in load ".$this->mod_name."'s ".$func." extends~");
		}
		
		$this->$mod_name->mod->$func = new $class_name();
		$this->$mod_name->mod->$func->pri = $this;
		return $this->$mod_name->mod->$func;
	}

	public function getConfig($name = '',$from = 'pri',$path = false){
		if(!isset($this->_config[$from])){
			$path = dirname(__FILE__).'/config/'.$this->_server.'/'.$from.'.conf.php';
			$this->_config[$from] = include($path);
			if(!$this->_config[$from]){
				die("error config for ".$name." ".$form." ~");
			}
		}
		if($name != '' && isset($this->_config[$from][$name])){
			return $this->_config[$from][$name];
		}else if($name == ''){
			return $this->_config[$from];
		}
		return false;
	}
	protected function setDefaultConfig(){
		return $this->getConfig('','pri');
	}
	protected function setDefaultModule(){
		$conf = $this->getConfig('default_mod');
		foreach ($conf as $k => $v) {
			$this->load($v);
		}
		return true;
	}
	//返回服务器环境，暂时为test环境
	public function getServer(){
		if(!$this->_server){
			$this->_server = 'test';
		}
		return $this->_server;
	}
	//rpc 调用
	public function httpQuery($mod,$func,$args){
		return $this->http->getReq($mod,$func,$args);
	}
	//end of class
}

class pri_interface {
	var $mod;
	public function getSelfConfig($name = ''){
		return $this->pri->getConfig($name,$this->mod_name);
	}
	public function getConfig($name= '',$mod = 'kpi'){
		return $this->pri->getConfig($name,$mod);
	}
	protected function _callModule(){
		$trace = debug_backtrace();
		$args = $trace[1]['args'];
		$func = $trace[1]['function'];
		if(!$this->mod){
			$this->_lazyInit($this->mod_name);
		}
		$status = $this->useHttp();
		if($status){
			return $this->pri->httpQuery($this->mod_name,$func,$args);
		}else{
			return call_user_func_array(array(&$this->mod,$func), $args);
		}
	}
	public function _lazyInit($mod,$path = false){
		if(!$path){
			$path = PRI_MODULE_ROOT;
		}
		$mod_path = $path.$mod.'/'.$mod.'.mod.php';
		if(file_exists($mod_path) && include($mod_path)){
			$class = $mod."_mod";
			$this->mod = new $class();
			$this->mod->pri = $this->pri;
			if(method_exists($this->mod,'init')){
				$this->mod->init();
			}
			return true;
		}
		die("can't _mod the ".$mod."~ ");
	}
	protected function useHttp(){
		$conf = $this->getSelfConfig('user_http');
		if($conf > 0 && PRI_HTTP_SWITCH){
			return true;
		}else{
			return false;
		}
	}
	//end of class
}

//new the global instance for pri
$GLOBALS['pri'] = pri::getInstance();
