<?php

namespace m\Database\Drivers {
	use \m as m;

	class MySQL extends m\Database\Driver {

		public $dbp = null;

		public function Connect($config) {
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

		public function Disconnect() {
			if(is_resource($this->dbp)) {
				mysql_close($this->dbp);
			}

			$this->dbp = null;
			return;
		}

		public function Escape($input) {
			return mysql_real_escape_string($input,$this->dbp);
		}

		public function ID() {
			return mysql_insert_id($this->dbp);
		}

		public function Query($sql) {
			return new MySQL\Query($this,$sql);;
		}

	}

}

namespace m\Database\Drivers\MySQL {
	use \m as m;

	class Query extends m\Database\Query {

		public $SQL;
		public $Rows;
		private $Result;

		public function __construct($driver,$sql) {
			parent::__construct($driver);

			if(func_num_args() != 2)
			throw new Exception('invalid parametre count');

			// store the compiled sql statement so it can be looked at
			// during debugging or whatnot. might only want to do this if
			// a debugging constant is set. dunno.
			$this->SQL = $sql;

			// perform the query against the database.
			$result = mysql_query($sql,$driver->dbp);

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
				$this->Rows = (($rows = mysql_num_rows($result))?
					($rows):
					(mysql_affected_rows($result))
				);
			}

			return;
		}

		public function Free() {
			if(!$this->Result) return false;

			mysql_free_result($this->Result);
			$this->Result = null;

			return true;
		}

		public function Next() {
			if(!$this->Result) return false;

			$object = mysql_fetch_object($this->Result);
			if(!$object) $this->Free();

			return $object;
		}

	}

}

?>