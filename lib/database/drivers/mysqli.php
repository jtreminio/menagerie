<?php

namespace m\database\drivers {
	use \m as m;
	
	class mysqli extends m\database\driver {
		
		private $dbp = null;

		public function connect() {

			$this->dbp = new \MySQLi(
				$this->config->hostname,
				$this->config->username,
				$this->config->password
			);
			
			if($this->dbp->connect_errno)
			$this->throwError('unable to connect');
			
			if(!$this->dbp->select_db($this->config->database))
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
			$result = $this->dbp->query($sql);
			if(!$result) return false;
			
			$query = new mysqli\query($this,$sql,$result);
			return $query;			
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
			
			$this->sql = $sql;
			$this->result = $result;
			$this->rows = $result->num_rows;
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