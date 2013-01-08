<?php

namespace m\Database\Drivers {
	use \m as m;

	class MySQLi extends m\Database\Driver {

		public $dbp = null;

		public function Connect($config) {

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

		public function Disconnect() {
			if(is_object($this->dbp)) {
				$this->dbp->close();
			}

			$this->dbp = null;
			return;
		}

		public function Escape($input) {
			return $this->dbp->real_escape_string($input);
		}

		public function ID() {
			return $this->dbp->insert_id;
		}

		public function Query($sql) {
			return new MySQLi\Query($this,$sql);
		}

	}

}

namespace m\Database\Drivers\MySQLi {
	use \m as m;

	class Query extends m\Database\Query {

		public $SQL;
		public $Rows = 0;
		private $Result;

		public function __construct($driver,$sql) {
			parent::__construct($driver);

			if(func_num_args() != 2)
			throw new \Exception('invalid parametre count');

			// store the compiled sql statement so it can be looked at
			// during debugging or whatnot. might only want to do this if
			// a debugging constant is set. dunno.
			$this->SQL = $sql;

			// perform the query against the database.
			$result = $driver->dbp->Query($sql);

			if(!$result) {
				// in the case that the query failed somehow. malformed
				// queries tend do this. that's why the sql property
				// is there.
				$this->OK = false;
				$this->Result = false;
				$this->Rows = 0;
			} else {
				// if the query was successful then we can continue on with
				// working on the result.
				$this->OK = true;
				$this->Result = $result;
				$this->Rows = ((is_object($result))?($result->num_rows):(0));
			}

			return;
		}

		public function Free() {
			if(!is_object($this->Result)) return false;

			$this->Result->free();
			$this->Result = null;

			return true;
		}

		public function Next() {
			if(!is_object($this->Result)) return false;

			$object = $this->Result->fetch_object();
			if(!$object) $this->Free();

			return $object;
		}

	}

}

?>