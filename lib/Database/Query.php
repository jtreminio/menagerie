<?php

namespace m\database {

	abstract class query {

		public $Driver;
		public $OK = null;

		abstract public function Free();
		abstract public function Next();

		public function __construct($driver) {
			$this->Driver = $driver;
			return;
		}

		public function __call($func,$argv) {
			if(method_exists($this->Driver,$func))
			return call_user_func_array(array($this->Driver,$func),$argv);

			else
			return;
		}

		public function glomp() {
			$list = array();
			while($dump = $this->Next()) {
				$list[] = $dump;
			}

			return $list;
		}

	}

}
