<?
// Khai báo các hằng số sử dụng trong class
define("SPHINX_DATABASE_ACTIVE", true);
define('SPHINX_DATABASE_SERVER_KEYWORD',1);


class dbInitSphinx{
	
	var $array_server	= array ();
	var $server			= "127.0.0.1";
	var $port			= "9306";
	var $username		= "";
	var $password		= "";
	var $database		= "";
	var $links			= array();
	
	function __construct($server_number=0){
		
		$this->server 	= "127.0.0.1";
		if(defined('USE_SPHINX_3')) $this->port = "9307";
		
	}
	
	static function dump_log($string){
		file_put_contents(ROOT . "/ipstore/sphinx.log",$string . "\n",FILE_APPEND);
	}

	//Hàm tính time
    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
	
}

class db_sphinx{
	
	var $link	= false;
	var $result	= false;
	var $error	= true;
	var $query = '';
	static $current_link;
    static $master_link;
    static $db_select;
    static $count_connection = 0;
	
	function __construct($query, $server_number=0){
		
		$query	= trim($query);
		$this->query = $query;
		$debug = debug_backtrace();
		if(!SPHINX_DATABASE_ACTIVE || $query == "") return false;
		$db_init	= new dbInitSphinx($server_number);
		if (self::$current_link && self::$current_link instanceof mysqli && mysqli_ping(self::$current_link)) {
			debug()->addMessage("db", 'Connect có sẵn');

			$this->links = &self::$current_link;
			$connect_successful = true;
		} else {
			debug()->addMessage('db', 'Connect SPHINX');
			debug()->start(md5($query) . ':Connect', 'Connect');
			$start_connect = $db_init->microtime_float();

			$this->links = mysqli_connect(
				$db_init->server . ":" . $db_init->port,
				$db_init->username,
				$db_init->password,
				$db_init->database
			);

			$arrConnInfo = array();
			$arrConnInfo['start'] 		= $start_connect;
			$arrConnInfo['end'] 		= $db_init->microtime_float();
			$arrConnInfo['duration'] 	= $arrConnInfo['end'] - $arrConnInfo['start'];
			$arrConnInfo['file_line'] 	= debug()->filterTrackerFileLine($debug);
			$arrConnInfo["host"] 		= $db_init->server;
			$arrConnInfo["type"] 		= "SPHINX_SERVER:" . $db_init->server;
			debug()->addMessage("connect", $arrConnInfo);
			debug()->end(md5($query) . ':Connect');


			if ($this->links) {
				$connect_successful = true;
				self::$current_link = &$this->links;
				db_init::$links[] = &$this->links;
				self::$count_connection++;

				debug()->addMessage('db', "Connection: " . self::$count_connection);

				mysqli_query($this->links, "SET NAMES 'utf8'");

			} //Save log error slave
			else {

				debug()->addMessage("db", "Connection fail");

				$filename = ROOT . "/ipdberror/" . $dbinit->slave_server . "_error.cfn";
				dbInitSphinx::dump_log(date("d/m/Y h:i:s A") . " " . mysqli_connect_error() . " - " . $_SERVER['REMOTE_ADDR'] . " " . $_SERVER['SCRIPT_FILENAME'] . "\n", "SPHINX_LOG");
				exit("error connect db search");
			}
		}

		global $query_analyze_list;
        global $query_analyze_array; //khai bao bien array chua cac query de phan tich cho de (toản)

        if (!isset($query_analyze_list)) {
            $query_analyze_list = "";
        }
        if (!isset($query_analyze_array)) {
            $query_analyze_array = array();
        }

        //echo $query . "<br>";
        $time_start = $db_init->microtime_float();
        //hàm này để bắt log xem từ file nào dòng nào gọi đến xử lý

        $file_line = array();

        foreach ($debug as $k => $v) {
            if (isset($v['file']) && isset($v['line'])) {
                $v['file'] = str_replace('\\', '/', $v['file']);

                if (false === strpos($v['file'], '/vendor/')) {
                    $v['file'] = str_replace(ROOT, "", $v['file']);
                    $v['file'] = str_replace(str_replace('\\', '/', ROOT), "", $v['file']);
                    $file_line[] = $v['file'] . ':' . $v['line'];
                }
            }

        }
        $arrTemp[0] = array('file_line' => $file_line);

        //Execute Query
//        $query = preg_replace('/(\v|\s)+/', ' ', $query);

        $start_query = $db_init->microtime_float();

        debug()->start($query, substr($query, 0, 50) . '...');
        $this->result = mysqli_query($this->links,
            $query );
        debug()->end($query);

        $time_end = $db_init->microtime_float();
        $time = $time_end - $time_start;

        $arrQueryInfo = array();

		//lấy ra các tham số query từ file nào dòng nào hàm nào hay class nào
		if (isset($arrTemp[0]) && is_array($arrTemp[0])) {
			$arrTemp[0]["time"] = $time;
			$arrTemp[0]["query"] = $query;
			$arrQueryInfo = $arrTemp[0];
			if (isset($arrQueryInfo["object"])) {
				unset($arrQueryInfo["object"]);
			}
			$arrQueryInfo["host"] = $db_init->server;
			$arrQueryInfo["type"] = "SPHINX";
			$arrQueryInfo['connection_id'] = self::$count_connection;


			//Check slow querry
			if ($time >= MYSQL_MAX_TIME_SLOW) {
				@file_put_contents(ROOT . "/ipstore/slow_sphinx.cfn", json_encode($arrTemp) . PHP_EOL,
					FILE_APPEND);
			}

		}
		unset($arrTemp);

        //1s mới đưa vào log
        if ($time >= 1) {

            $query_analyze_list .= "Sphinx : " . $db_init->server . "\n";
            $query_analyze_list .= $query . "\n";
            $query_analyze_list .= $time . "\n";

        }//if ($time >= 0.3)

        // Dump error sql log
        if (!$this->result) {
            $error = mysqli_error($this->links);
            mysqli_close($this->links);
            dbInitSphinx::dump_log("\n\n\n" . $query . "\n\n\n" . date("d/m/Y h:i:s A") . " - " . $error . " - " . @SLAVE_SERVER_IP . " - " . @$_SERVER['REMOTE_ADDR'] . " " . @$_SERVER['SCRIPT_NAME'] . "?" . @$_SERVER['QUERY_STRING'] . "\n");
            die("Error in query string " . $error);
        }

        $arrQueryInfo['start'] = $start_query;
        $arrQueryInfo['end'] = $db_init->microtime_float();
        $arrQueryInfo['duration'] = $arrQueryInfo['end'] - $arrQueryInfo['start'];
		$arrQueryInfo["infosphinx"] = $this->getInfoQuery();
        $query_analyze_array[] = $arrQueryInfo;
        debug()->addMessage("queries", $arrQueryInfo);

		unset($db_init);
		
	}
	
	function getInfoQuery(){

		if(!$this->links) return false;
		
		$metainfo	= @mysqli_query($this->links,"SHOW META");
		$srchmeta	= array();
		while($meta = @mysqli_fetch_assoc($metainfo)) $srchmeta[$meta["Variable_name"]]	= $meta["Value"];
		
		return $srchmeta;
		
	}

	/**
     * Convert result set to array
     */
    function convert_result_set_2_array($result_set)
    {
        $array_return = array();

        //Move first resultset
        if (mysqli_num_rows($result_set) > 0) {
            mysqli_data_seek($result_set, 0);
        } else {
            return $array_return;
        }

        while ($row_t = mysqli_fetch_assoc($result_set)) {
            $array_return[] = $row_t;
        }

        return $array_return;
    }

	    /**
     * Fetch result to array
     *
     * @param bool $single - Fetch one hay fetch all
     * @return array
     */
    public function fetch($single = false)
    {
        $result = $this->result;

        if( $this->result instanceof mysqli_result ) {
            $result = $this->convert_result_set_2_array($this->result);
        }

        if( $single ) {
            if( !empty($result) ) {
                return $result[0];
            }

            return array();
        }

        return $result;
    }
	
	function close(){
		
		@mysqli_free_result($this->result);
		if($this->link) @mysqli_close($this->link);
		
	}
	
}


class db_sphinx_multi{

	var $link	= false;
	var $db_init= false;
	var $mysqli	= false;
	var $result	= false;
	var $query	= "";
	var $error	= true;
	var $i		= 0;

	function __construct($query, $server_number=0){

		$query	= trim($query);
		$this->query = $query;
		if(!SPHINX_DATABASE_ACTIVE || $query == "") return false;

		$db_init	= new dbInitSphinx($server_number);
		$this->db_init	= $db_init;
		$this->mysqli	= new mysqli($db_init->server, $db_init->username, $db_init->password, $db_init->database, $db_init->port);
		if($this->mysqli->connect_errno){
			dbInitSphinx::dump_log("Cannot connect sphinx realtime database (" . $db_init->server . ":" . $db_init->port . ")", "SPHINX_LOG");
			return;
		}

		$this->i++;
		$this->query	= $query;
		$this->mysqli->query("SET NAMES 'utf8'");
		if($this->mysqli->multi_query($this->query)){
			$this->result	= $this->mysqli->store_result();
			$this->error	= false;
		}
		else{
			dbInitSphinx::dump_log("Error in query string result " . $this->i . " (" . $db_init->server . ":" . $db_init->port . "): " . $this->mysqli->error . "\n" . @$_SERVER["SERVER_NAME"] . @$_SERVER["REQUEST_URI"] . "\n" . $this->query, "SPHINX_LOG");
			@$this->mysqli->close();
		}

	}

	function more_results(){

		return ($this->mysqli->more_results() ? true : false);

	}

	function next_result(){

		$this->i++;
		if($this->mysqli->next_result()) $this->result = $this->mysqli->store_result();
		else dbInitSphinx::dump_log("Error in query string result " . $this->i . " (" . $this->db_init->server . ":" . $this->db_init->port . ")\n" . @$_SERVER["SERVER_NAME"] . @$_SERVER["REQUEST_URI"] . "\n" . $this->query, "SPHINX_LOG");

	}

	function close(){

		@$this->mysqli->free();
		@$this->mysqli->close();

	}

}


class db_sphinx_execute{
	
	var $link	= false;
	var $result	= false;
	var $error	= true;
	var $query = '';

	function __construct($query, $server_number=0){
		
		if(!SPHINX_DATABASE_ACTIVE) return false;
		$this->query = $query;
		$db_init	= new dbInitSphinx($server_number);
		if($this->link = @mysqli_connect($db_init->server . ":" . $db_init->port, $db_init->username, $db_init->password)){
			@mysqli_select_db($this->link,$db_init->database);
			@mysqli_query($this->link,"SET NAMES 'utf8'");
			$this->result	= @mysqli_query($this->link,$query);
			if(!$this->result) dbInitSphinx::dump_log("Error in query string (" . $db_init->server . ":" . $db_init->port . "): " . mysqli_error($this->link) . "\n" . @$_SERVER["SERVER_NAME"] . @$_SERVER["REQUEST_URI"] . "\n" . $query, "SPHINX_LOG");
			else $this->error	= false;
			@mysqli_close($this->link);
		}
		else dbInitSphinx::dump_log("Cannot connect sphinx realtime database (" . $db_init->server . ":" . $db_init->port . ")", "SPHINX_LOG");
		unset($db_init);
		
	}
	
}
?>