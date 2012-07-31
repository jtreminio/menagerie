<?php

namespace m\database {

	abstract class query {

		public $driver;

		abstract public function free();
		abstract public function next();

		public function __construct($driver) {
			$this->driver = $driver;
			return;
		}

		public function __call($func,$argv) {
			if(method_exists($this->driver,$func))
			return call_user_func_array(array($this->driver,$func),$argv);

			else
			return;
		}

		public function glomp() {
			$list = array();
			while($dump = $this->next()) {
				$list[] = $dump;
			}
			
			return $list;
		}
		
	}
	
}
