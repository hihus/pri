<?php
/**
 * database基础类
 * @author hihu
 */
class db_inc extends pri_interface {

	var $connection;
	var $query_id = '';
	var $sql_time = 0;
	var $debug = 0;
	var $db_host = '';
	var $db_port = 3306;
	var $db_user = '';
	var $db_password = '';
	var $db_database = '';
	var $sql_debug = 0; //SQL附加信息

	function db_inc($unit = null){
		if(is_array($unit))
			$this->setDB($unit);
	}

	function _init(){
		if (in_array($this->pri->getServer(), array('test')))
			$this->sql_debug = 1;
	}

	/**
	 * 加载指定单元数据库对象
	 * @param string $unit 数据库单元
	 * @return DB 引用
	 */
	function loadDB($unit){
		$conf = $this->getSelfConfig('DATABASE_CONF', $unit);
		if($conf){
			if(!$this->$unit){
				$this->$unit = new db_inc($conf);
				$this->$unit->pri = $this->pri;
				if(method_exists($this->$unit, '_init'))
					$this->$unit->_init();
			}
			return $this->$unit;
		}else{
			return false;
		}
	}

	/**
	 * 指定单元数据库对象
	 * @param string $unit 数据库单元
	 * @return DB 引用
	 */
	function setDB($unit){
		$conf = is_array($unit) ? $unit : $this->getSelfConfig('DATABASE_CONF', $unit);
		if($conf){
			if (!empty($unit['port'])) $this->db_port = $conf['port'];
			$this->db_host = $conf['host'];
			$this->db_user = $conf['user'];
			$this->db_password = $conf['pwd'];
			$this->db_database = $conf['db'];
			return $this;
		}else{
			return false;
		}
	}

	function connect(){
		if (!$this->connection || !@mysql_ping($this->connection)) {
			@mysql_close($this->connection);
			$this->connection = mysql_connect($this->db_host.':'.$this->db_port, $this->db_user, $this->db_password,true);
			if(!is_resource($this->connection)) {
				$this->error('Cannot connect to database host (' . $this->db_host . ') by user (' . $this->db_user . ').');
			} else {
				if (!@mysql_select_db($this->db_database, $this->connection)) {
					$this->error('Cannot select database (' . $this->db_database . ').');
				}
				mysql_query("SET NAMES 'UTF8'");			
			}
		}
	}


	/*-------------------------------------------------------------------------*/
	// resource query(string sqlstr)
	// ------------------
	// Execute an SQL query.
	/*-------------------------------------------------------------------------*/
	function query($sqlstr,$is_echo=true){
		$debug = "query sql: {$sqlstr}\n";
		$this->parent->utility->setTimerPoint('db_query');

		if($this->sql_debug){
			$sqlstr .= ' -- from test on ' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
		}
		//$this->_page_analyzer->logFunctionCall(__CLASS__, __FUNCTION__, $sqlstr);
		$this->connect();
		if ($this->debug)
		{
			list ($start_time, $decimal) = explode(' ', microtime());
			$start_time += $decimal;
		}

		$this->query_id = @mysql_query($sqlstr, $this->connection);

		$debug .= 'query timer: ' . $this->parent->utility->getTimerDiff('db_query') . "ms\n";
		$this->parent->debug->dump($debug, 88);// ?DEBUG=88 打印sql

		if ($this->debug)
		{
			list($end_time, $decimal) = explode(' ', microtime());
			$end_time += $decimal - $start_time;
		}

		if($this->query_id)
		{
			return $this->query_id;
		}
		else {
			if($is_echo === true) {
				$exit = true;
			} else {
				$exit = false;
			}
			$this->error(sprintf("Invalid SQL@%s:%s [%s] %.256s:%s\n" ,$_SERVER["SERVER_ADDR"], date('Y-m-d G:i:s'),  $_SERVER['PHP_SELF'], $sqlstr, is_resource($this->connection) ? mysql_error($this->connection) : mysql_error()), $exit);
		}
		return false;
	}


	/*-------------------------------------------------------------------------*/
	// int get_inc_id(void)
	// ------------------
	// Returns the newly inserted auto_increment id.
	/*-------------------------------------------------------------------------*/
	function get_inc_id(){
		$this->connect();
		return mysql_insert_id($this->connection);
	}


	/*-------------------------------------------------------------------------*/
	// mixed result(string sqlstr)
	// ------------------
	// Select a single line or a single cell.
	// eg: $SDB->result("SELECT name FROM user WHERE uid=5")
	//	   (returns a string)
	//	 $SDB->result("SELECT name,email FROM user WHERE login_hash='$hash'")
	//	   (returns an object)
	// Returns NULL if no such entry could be found.
	/*-------------------------------------------------------------------------*/
	function result($sqlstr){
		$this->connect();
		$result = $this->query($sqlstr . " LIMIT 1", $this->connection);

		if (!is_resource($result) || mysql_num_rows($result) == 0)
		{
			return NULL;
		}

		if (mysql_num_fields($result) <= 1)
		{
			$arr = mysql_fetch_row($result);
			mysql_free_result($result);
			return $arr[0];
		}
		else
		{
			$obj = mysql_fetch_object($result);
			mysql_free_result($result);
			return $obj;
		}
	}

	/*-------------------------------------------------------------------------*/
	// array fetch_array(resource id)
	// ------------------
	// Fetch a row as an array from result by both assoc and num.
	/*-------------------------------------------------------------------------*/
	function fetch_array($id = NULL){
		$this->connect();
		$queryId = $id ? $id : $this->query_id;
		if(is_resource($queryId)) {
			return 	mysql_fetch_array($queryId);
		} else {
			return NULL;
		}
	}

	/*-------------------------------------------------------------------------*/
	// array fetch_assoc(resource id)
	// ------------------
	// Fetch a row as an array from result by assoc.
	/*-------------------------------------------------------------------------*/
	function fetch_assoc($id = NULL){
		$this->connect();

		$queryId = $id ? $id : $this->query_id;
		if(is_resource($queryId)) {
			return 	mysql_fetch_assoc($queryId);
		} else {
			return NULL;
		}		
	}

	/*-------------------------------------------------------------------------*/
	// array fetch_row(resource id)
	// ------------------
	// Fetch a row as an array from result.
	/*-------------------------------------------------------------------------*/
	function fetch_row($id = NULL){
		$this->connect();

		$queryId = $id ? $id : $this->query_id;
		if(is_resource($queryId)) {
			return 	mysql_fetch_row($queryId);
		} else {
			return NULL;
		}				
	}

	/*-------------------------------------------------------------------------*/
	// object fetch_object(resource id)
	// ------------------
	// Fetch a row as an object from result.
	/*-------------------------------------------------------------------------*/
	function fetch_object($id = NULL){
		$this->connect();

		$queryId = $id ? $id : $this->query_id;
		if(is_resource($queryId)) {
			return 	mysql_fetch_object($queryId);
		} else {
			return NULL;
		}						
	}

	/*-------------------------------------------------------------------------*/
	// int num_rows(resource id)
	// ------------------
	// Returns number of collected rows.
	/*-------------------------------------------------------------------------*/
	function num_rows($id = NULL){
		$this->connect();

		$queryId = $id ? $id : $this->query_id;
		if(is_resource($queryId)) {
			return 	mysql_num_rows($queryId);
		} else {
			return 0;
		}								
	}

	/*-------------------------------------------------------------------------*/
	// int affected_rows(resource id)
	// ------------------
	// Returns number of affected rows.
	/*-------------------------------------------------------------------------*/
	function affected_rows(){
		return mysql_affected_rows($this->connection);
	}

	/*-------------------------------------------------------------------------*/
	// close_database()
	// ------------------
	// Close Database.
	/*-------------------------------------------------------------------------*/
	function close_database(){
		if($this->connection)
		{
			$return = @mysql_close($this->connection);
			$this->connection = false;
			return $return;
		}
	}


	/*-------------------------------------------------------------------------*/
	// void error(string msg)
	// ------------------
	// Record an error message and crash.
	/*-------------------------------------------------------------------------*/
	function error($msg,$exit=true){
		$errno = mysql_errno();

		$error_string = 'Time>>>>' .date('Y-m-d H:i:s') . "\n";
		$error_string .= 'Page>>>>' . $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'] . "\n";
		$error_string .= 'IP>>>>' . $IP . "\n";
		$error_string .= 'Error_No.>>>>'.$errno."\n";
		$error_string .= 'Database_Host>>>>'.$this->db_host."\n";
		$error_string .= 'Web_message>>>>' . $msg . "\n";
		$error_string .= 'SQL_message>>>>' . mysql_error() . "<br>\n";

		// $fp = @fopen(PRI_DATA_ROOT . 'mysql_errorlog_' . date('Y-m-d') . '.dat' , 'a');
		// @flock($fp, LOCK_EX);
		// @fwrite($fp, $error_string);
		// @flock($fp, LOCK_UN);
		// @fclose($fp);

		$url = $_SERVER['HTTP_HOST']."/".$_SERVER['REQUEST_URI'];
		$url = base64_encode($url);
		$msg = base64_encode($msg);
		print '很抱歉，有错误产生! 请稍候再试，我们会尽快修复！' . $error_string;
		if($exit) {
			exit;
		}
	}


	/**
	 * execute add query on target table
	 * @param string $table	- target table name
	 * @param array $values - key-value array
	 */
	function executeAddStatement($table, $values) {
		if (!count($values))
		{
			return false;
		}

		$query = 'INSERT INTO `'.$table.'` (';
		foreach ($values AS $key => $value)
		{
			$query .= '`'.$key.'`,';
		}
		$query = rtrim($query, ',').') VALUES (';

		foreach ($values AS $key => $value)
		{
			$query .= '\''.$value.'\',';
		}

		$query = rtrim($query, ',').')';

		return $this->query($query);
	}

	/**
	* execute replace into clause on target table
	* @param string $table	- target table name
	* @param array $values - key-value array
	*/
	function executeReplaceStatement($table, $values) {
		if (!count($values))
		{
			return false;
		}

		$query = 'REPLACE INTO `'.$table.'` (';
		foreach ($values AS $key => $value)
		{
			$query .= '`'.$key.'`,';
		}
		$query = rtrim($query, ',').') VALUES (';

		foreach ($values AS $key => $value)
		{
			$query .= '\''.$value.'\',';
		}

		$query = rtrim($query, ',').')';

		return $this->query($query);		
	}

	/**
	 * execute update query on target table
	 * @param string $table	- target table
	 * @param array $values - key-value array
	 * @param string $where - condition clause
	 */
	function executeUpdateStatement($table, $values, $conditionArray = false) {
		if(!count($values))
		{
			return false;
		}

		$query = 'UPDATE `'.$table.'` SET ';
		foreach ($values AS $key => $value)
		{
			$query .= '`'.$key.'` = \''.$value.'\',';
		}

		$query = rtrim($query, ',');

		if ($conditionArray)
		{
			$query .= ' where ';
			foreach ($conditionArray AS $key => $value)
			{
				$query .= '`'.$key.'` = \''.$value.'\',';
			}
			$query = rtrim($query, ',');
		}

		return $this->query($query);
	}

	/**
	 * delete entries from target table
	 * @param $table
	 * @param $conditionArray - key-value array
	 */
	function executeDeleteStatement($table, $conditionArray, $limit = false) {
		if(!count($conditionArray)) return false;

		$query = 'DELETE FROM `'.$table.'` where ';

		foreach ($conditionArray AS $key => $value)
		{
			$query .= '`'.$key.'` = \''.$value.'\',';
		}

		$query = rtrim($query, ',');

		if($limit) {
			$query .= ' limit '.intval($limit);
		}

		return $this->query($query);
	}

	/**
	 * Fetch one piece of record from database
	 * @param $table
	 * @param $conditionArray
	 * @return object or false
	 */
	function fetchSingleRecord($table, $conditionArray, $cols = false) {
		if(!count($conditionArray))
		{
			return false;
		}

		if($cols == false)
		{
			$query = 'SELECT * FROM `'.$table.'` where ';
		} else {
			$colStr = '';
			foreach($cols as $column) {
				if($column && $column != '')
					$colStr .= '`'.$column.'`,';
			}
			$colStr = rtrim($colStr, ',');

			$query = 'SELECT '.$colStr.' FROM `'.$table.'` where ';
		}

		$cnt = 0;
		foreach ($conditionArray AS $key => $value)
		{
			if($cnt != 0) 
			{
				$query .= ' AND ';
			}
			$query .= '`'.$key.'` = \''.$value.'\'';
			$cnt++;
		}

		return $this->result($query);
	}

}

