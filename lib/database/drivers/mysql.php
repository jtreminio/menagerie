<?php

namespace m\database\drivers {
	use \m as m;
	
	class mysql extends m\database\driver {

		public $dbp = null;

		public function connect($config) {
			$this->dbp = mysql_connect(
				$config->hostname,
				$config->username,
				$config->password
			);
			
			if(!$this->dbp)
			$this->throwError('unable to connect');
			
			if(!mysql_select_db($config->database,$this->dbp))
			$this->throwError("unable to select db {$this->database}");
		
			return true;
		}		
		
		public function disconnect() {
			if(is_resource($this->dbp)) {
				mysql_close($this->dbp);
			}
			
			$this->dbp = null;
			return;
		}
		
		public function escape($input) {
			return mysql_real_escape_string($input,$this->dbp);
		}

		public function id() {
			return mysql_insert_id($this->dbp);
		}
		
		public function query($sql) {
			return new mysql\query($this,$sql,$result);;
		}

	}
	
}

namespace m\database\drivers\mysql {
	use \m as m;
	
	class query extends m\database\query {
	
		public $sql;
		public $rows;
		private $result;
	
		public function __construct($driver,$sql,$result) {
			parent::__construct($driver);

			if(func_num_args() != 3)
			throw new Exception('invalid parametre count');
		
			// store the compiled sql statement so it can be looked at
			// during debugging or whatnot. might only want to do this if
			// a debugging constant is set. dunno.
			$this->sql = $sql;

			// perform the query against the database.
			$result = mysql_query($sql,$driver->dbp);

			if(!$result) {
				// in the case that the query failed somehow. malformed
				// queries tend do this. that's why the sql property
				// is there.
				$this->ok = false;
				$this->result = false;
				$this->rows = 0;
			} else {
				// if the query was successful then we can continue on with
				// working on the result.
				$this->ok = true;
				$this->result = $result;
				$this->rows = (($rows = mysql_num_rows($result))?
					($rows):
					(mysql_affected_rows($result))
				);
			}
							
			return;
		}
		
		public function free() {
			if(!$this->result) return false;
			
			mysql_free_result($this->result);
			$this->result = null;
			
			return true;
		}
		
		public function next() {
			if(!$this->result) return false;
			
			$object = mysql_fetch_object($this->result);
			if(!$object) $this->free();
			
			return $object;			
		}

	}
		
}

?>