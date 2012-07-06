<?php

namespace m\database {

	abstract class driver {
	
		protected $name;
		protected $config;

		protected function throwError($message) {
			throw new \Exception("{$message} [{$this->name}]");
		}
	
		public function __construct($name,$config) {
			$this->name = $name;
			$this->config = $config;
			return;
		}
		
		public function __destruct() {
			$this->disconnect();
			return;
		}
		
		abstract public function connect();
		abstract public function disconnect();
		abstract public function escape($input);
		abstract public function query($sql);

		// highly recommended additional methods:
		// public function id(void); // return the last inserted id.
	
	}
	
	abstract class query {

		abstract public function free();
		abstract public function next();
	
		// highly recommended additional methods:
		// public function id(void); // return the last inserted id.

		public function glomp() {
			$list = array();
			while($dump = $this->next()) {
				$list[] = $dump;
			}
			
			return $list;
		}
		
	}
}

?>