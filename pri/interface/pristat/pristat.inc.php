<?php
class pristat_inc extends pri_interface{
	public $timers = array();
	function init(){
		if(!defined('STAT_FUNC')) define('STAT_FUNC',1);
	}

	function call_stat_start($mod,$func,$status){
		$this->timers[$mod.'_'.$func.'_'.$status] = $this->getMillisecond();
	}

	function call_stat_end($mod,$func,$status){
		$time = $this->getMillisecond() - $this->timers[$mod.'_'.$func.'_'.$status];
		$this->log_stat(array($mod,$func),$time);
	}
	function log_stat($info,$time){
		switch(STAT_FUNC){
			case 1 : $this->echo_log($info,$time);break;
			case 2 : $this->file_log($info,$time);break;
			case 3 : $this->db_log($info,$time);break;
			case 4 : $this->mem_log($info,$time);break;
		}
	}
	function echo_log($info,$time){
		echo "the pri called $info[0]->$info[1] use time : $time ms \n ";
		return true;
	}
	//待实现
	function file_log($info,$time){
		return true;
	}
	function db_log($info,$time){
		return true;
	}
	function mem_log($info,$time){
		return true;
	}
	function getMillisecond() {
	    list($s1, $s2) = explode(' ', microtime());
	    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
	}
	//end
}