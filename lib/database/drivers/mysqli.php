<?php

namespace m\database\drivers {
	use \m as m;
	
	class mysqli extends m\database\driver {
		
		public $dbp = null;

		public function connect($config) {

			$this->dbp = new \MySQLi(
				$config->hostname,
				$config->username,
				$config->password
			);
			
			if($this->dbp->connect_errno)
			$this->throwError('unable to connect');
			
			if(!$this->dbp->select_db($config->database))
			$this->throwError("unable to select db {$this->database}");
		
			return true;
		}		
		
		public function disconnect() {
			if(is_object($this->dbp)) {
				$this->dbp->close();
			}
			
			$this->dbp = null;
			return;
		}
		
		public function escape($input) {
			return $this->dbp->real_escape_string($input);
		}

		public function id() {
			return $this->dbp->insert_id;
		}
		
		public function query($sql) {
			return new mysqli\query($this,$sql,$result);
		}

	}
	
}

namespace m\database\drivers\mysqli {
	use \m as m;
	
	class query extends m\database\query {
	
		public $sql;
		private $result;
	
		public function __construct($driver,$sql,$result) {
			parent::__construct($driver);

			if(func_num_args() != 3)
			throw new \Exception('invalid parametre count');

			// store the compiled sql statement so it can be looked at
			// during debugging or whatnot. might only want to do this if
			// a debugging constant is set. dunno.
			$this->sql = $sql;

			// perform the query against the database.
			$result = $driver->dbp->query($sql);

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
				$this->rows = $result->num_rows;
			}

			return;
		}
		
		public function free() {
			if(!$this->result) return false;
			
			$this->result->free();
			$this->result = null;
			
			return true;
		}
		
		public function next() {
			if(!$this->result) return false;
			
			$object = $this->result->fetch_object();
			if(!$object) $this->free();
			
			return $object;			
		}

	}
		
}

?>