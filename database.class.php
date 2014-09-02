<?
	class db {
		protected $resource;
		
		protected function __construct($config){
			extract($config);
			if(isset($hostname) && isset($username) && isset($password) && isset($database)){
				return $this->connect($config);
			}
		}
		
		public static function getInstance($config){
			extract($config);
			static $instances = array();
			$key = "$host:$user:$password:$database";
			if(!isset($instances[$key])){
				$instances[$key] = new db($config);
			}
			return $instances[$key];
		}
		
		public function connect($config){
			extract($config);
			if($this->resource = mysql_connect($hostname, $username, $password)){
				return mysql_select_db($database);
			}
			return false;
		}
		
		public function query($query){
			$args = func_get_args();
			if(count($args)>1){
				array_shift($args);
				$args = array_map('mysql_real_escape_string', $args);
				array_unshift($args, $query);
				$query = call_user_func_array('sprintf', $args);
			}
			if(!$result = mysql_query($query)){
				echo "QUERY ERROR: $query\n";
				throw new Exception('Query failed: '.mysql_error());
			}else{
				$rows = array();
				while($row = mysql_fetch_assoc($result)){
					$rows[] = $row;
				}
				if(count($rows) == 1){
					if(count($rows[0]) == 1){
						$rows[0] = array_values($rows[0]);
						return $rows[0][0];
					}else{
						return $rows[0];
					}
				}else{
					return $rows;
				}
			}
		}
		
		public function select_database($database){
			return mysql_select_db($database);
		}
		
		public function get_affected_rows(){
			return mysql_affected_rows($this->resource);
		}
		
		public function get_insert_id(){
			return mysql_insert_id($this->resource);
		}
		
		public function set_charset($charset){
			return mysql_set_charset($charset,$this->resource);
		}
	}
?>