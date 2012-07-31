<?php

namespace m\database {

	abstract class driver {
	
		protected $name;

		protected function throwError($message) {
			throw new \Exception("{$message} [{$this->name}]");
		}
	
		public function __construct($name) {
			$this->name = $name;
			return;
		}
		
		public function __destruct() {
			$this->disconnect();
			return;
		}
		
		abstract public function connect($config);
		abstract public function disconnect();
		abstract public function escape($input);
		abstract public function query($sql);

		// highly recommended additional methods:
		// public function id(void); // return the last inserted id.
	
	}

}

?>