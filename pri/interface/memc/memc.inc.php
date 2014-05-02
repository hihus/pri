<?php
/**
 * Memcache基础类
 * @author hihu
 */
class memc_inc extends pri_interface{
	var $servers, $servers_r;
	var $memobj = null, $memobj_r = null;

	function memc_inc($servers = null, $servers_r = null){
		if($servers){
			$this->servers = $servers;
			$this->memobj = new Memcache;
		}
	}

	/**
	 * 加载指定单元Memcache对象
	 * @param string $unit Memcache单元
	 * @return memc 引用
	 */
	function loadMemc($unit){
		if(!$this->$unit){
			$conf = $this->getSelfConfig('MEMCACHE_UNIT');
			$conf = isset($conf[$unit]) ? $conf[$unit] : false;
			if(!$conf) die("loadMemc '{$unit}' conf is null"); //没有找到memcache配置
			$this->$unit = new memc_inc($conf);
			$this->$unit->pri = &$this->pri;
		}
		return $this->$unit;
	}

	function init(){
		return true;
	}

	/**
	 * 指定单元Memcache对象
	 * @param string $unit Memcache单元
	 * @return memc 引用
	 */
	function setMemc($unit){
		if(!$this->memobj)
			$this->memobj = new Memcache;
		if($this->memobj->connection)
			$this->memobj->close();	
		$conf = $this->getSelfConfig('MEMCACHE_UNIT');
		$conf = isset($conf[$unit]) ? $conf[$unit] : false;
		$this->servers = $conf;
		return $this;
	}

	function add($key, $val, $flag=0, $expire=0){
		if($this->verify_input($key) === false) {
			return false;
		}

		$this->connect();
		$ret = $this->memobj->add($key, $val, $flag, $expire);

		return $ret;
	}

	function set($key, $val, $flag=0, $expire=0){
		if($this->verify_input($key) === false) {
			return false;
		}

		$this->connect();
		$ret = $this->memobj->set($key, $val, $flag, $expire);
		if( !$ret )
		{
			$ret = $this->memobj->replace($key, $val, $flag, $expire);
		}

		return $ret;
	}

	function replace($key, $val,  $flag= 0,  $expire= 0){
		if($this->verify_input($key) === false) {
			return false;
		}
	
		$this->connect();
		$ret = $this->memobj->replace($key, $val, $flag, $expire);
		if( !$ret )
		{
			$ret = $this->memobj->set($key, $val, $flag, $expire);
		}

		return $ret;
	}

	function read($key){
		return $this->get($key);
	}

	function get($key){

		$this->connect();

		$value = $this->memobj->get($key);

		//未取到值并且是子机房
		if(!$value && $this->servers_r)
		{
				$value = $this->memobj_r->get($key);
				if($value)
					$this->set($key, $value, 30*60);
		}

		return $value;
	}

	function delete($key,$timeout=0){
		if($this->verify_input($key) === false) {
			return false;
		}
		$this->connect();
		$value = $this->memobj->delete($key, $timeout);
		return $value;
	}

	function increment($key, $num=1){

		$this->connect();
		$value = $this->memobj->increment($key, $num);

		return $value;
	}

	function decrement($key, $num=1){
		$this->connect();
		$value = $this->memobj->decrement($key, $num);

		return $value;
	}

	function connect(){

		if(empty($this->memobj->connection) || !$this->memobj->connection){
			foreach($this->servers as $one_server){
				$this->memobj->addServer($one_server['host'], $one_server['port'], $one_server['pconnect']);
				if (!$this->memobj->connection){
					$this->memcache_log_error($one_server);
				}
			}
		}

		if($this->servers_r && (empty($this->memobj_r->connection) || !$this->memobj_r->connection)){
			foreach($this->servers_r as $one_server){
				$this->memobj_r->addServer($one_server['host'], $one_server['port'], $one_server['pconnect']);
				if (!$this->memobj_r->connection){
					$this->memcache_log_error($one_server);
				}
			}
		}
	}

	function getExtendedStats(){
		$this->connect();
		return $this->memobj->getExtendedStats();
	}

	function getStats(){
		$this->connect();
		return $this->memobj->getStats();
	}

	/**
	 * 判断是否需要记录日志
	 * @return 
	 */
	function is_write_log() {
		if(MEMCACHE_PV_ON == 1 && rand(1, OPERATION_FREQ) == 5) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * verify whether the key is empty
	 */ function verify_input($key) {
		if(defined('MEMLOGENABLED') && MEMLOGENABLED === true) {
			if(empty($key) || $key == '') {
				$arr = debug_backtrace();
				$this->memcache_empty_error($arr);
				return false;
			} else {
				return true;
			}
		}
		return true;
	}

	/**
	 * 记录错误日志
	 *
	 * @param  $one_server
	 */
	function memcache_log_error($one_server){
		return true;
		$dir = PRI_DATA_ROOT;
		$date = date("Ymd");
		$log_file =  $dir . 'mem_log_' . $date . '.dat';
		$fp = fopen($log_file, 'a+');
		$date1 = date("Y-m-d H:i:s");
		$line = "{$date1} >> could not connect cache server >> {$one_server['host']} >> {$one_server['port']}\n";
		print $line; //debug
		fwrite($fp, $line);
		fclose($fp);
	}

	/**
	 * write the error log
	 */
	function memcache_empty_error($error_arr) {
		if(empty($error_arr) || count($error_arr) > 8) {
			return;
		}
		return true;

		$dir = PRI_DATA_ROOT;
		$date = date("Ymd");
		$log_file =  $dir . 'mem_log_' . $date . '.dat';
		$fp = fopen($log_file, 'a+');
		$date1 = date("Y-m-d H:i:s");
		$line = "{$date1} >> memory cache key could not be null. \n";

		foreach ($error_arr as $entry) {
			if(is_array($entry)) {
				$line .= "in file ".$entry["file"]." >>>method ".$entry["function"]." >>>line ".$entry["line"]."\n";
			} elseif(is_string($entry)) {
				$line .= $entry;
			}
		}
		print $line; //debug
		fwrite($fp, $line);
		fclose($fp);
	}

	/**
	 * memcache 记录pv 
	 * @param string $keys key值
	 * @param int $nums 递增的数量 默认为1
	 * @return bool 成功 true 失败 false
	 */
	function write_pv_memcache($key, $num = 1, $unit = 'servers57'){
		$num=intval($num);
		if($num <= 0 || empty($key)){
			return false;
		}

		$memc = $this->loadMemc($unit);
		$memc->connect();
		if($memc->memobj->increment($key, $num) === false){
			$expire = 86400-(time()-strtotime(date('Y-m-d')));
			return ($memc->memobj->set($key, $num, false, $expire)) === true;
		}else{
			return true;
		}

	}

	/**
	* 推进队列
	* @param int $k key
	* @param int $v value
	* @param bool $uniq value唯一
	* @return int
	*/
	function listPush($k, $v, $uniq = 0, $unit = 'servers57'){
		$memc = $this->loadMemc($unit);
		$memc->connect();
		$expire = 86400-(time()-strtotime(date('Y-m-d')));

		$ret = false;
		if($uniq){
			$ukey = $k . '_' . md5($v);
			$ret = $memc->memobj->increment($ukey, 1);
			if(!$ret)
				$memc->memobj->set($ukey, 1, 0, $expire);
		}

		if(!$ret){
			$id = $memc->memobj->increment($k, 1);
			if(!$id){
				$id = 1;
				$memc->memobj->set($k, $id, 0, $expire);
			}
			$ikey = $k . '_' . $id;
			return $memc->memobj->set($ikey, $v, 0, $expire) ? 1 : 0;
		}else{
			return 2;
		}
	}

	/**
	* Value Count
	*/
	function listValueCount($k, $v, $unit = 'servers57'){
		$memc = $this->loadMemc($unit);
		$memc->connect();
		$k .= '_' . md5($v);
		return $memc->memobj->get($k);
	}

	/**
	* 队列长度
	* @param int $k key
	* @return int
	*/
	function listCount($k, $unit = 'servers57'){
		$memc = $this->loadMemc($unit);
		$memc->connect();
		return $memc->memobj->get($k);
	}

	/**
	* 取出队列
	* @param int $k key
	* @param int $offs 起始位置
	* @param int $length 条数
	* @return array 结果集
	*/
	function listGet($k, $offs = 1, $length = 100, $unit = 'servers57'){
		$memc = $this->loadMemc($unit);
		$memc->connect();
		$maxid = $memc->memobj->get($k);
		if($maxid){
			$lists = array();
			for($i = $offs; $i < ($offs + $length) && $i <= $maxid; $i ++)
				$lists[] = $memc->memobj->get($k . '_' . $i);
			return $lists;
		}else{
			return false;
		}
	}

	/**
	* 删除队列
	* @param int $k key
	* @return bool
	*/
	function listDel($k, $unit = 'servers57'){
		$memc = $this->loadMemc($unit);
		$memc->connect();
		$maxid = $memc->memobj->get($k);
		if($maxid){
			for($i = 1; $i <= $maxid; $i ++){
				$ki = $k . '_' . $i;
				$v = $memc->memobj->get($ki);
				if($v){
					//删除uniq用key值
					$uk = $k . '_' . md5($v); 
					$memc->memobj->delete($uk); 
				}
				//删除队列id值
				$memc->memobj->delete($ki);
			}
			//删除主key
			$memc->memobj->delete($k);
			return true;
		}else{
			return false;
		}

	}

	/**
	 * 取当前时间 
	 * @return float
	 */
	function microtime_float()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}

}

?>