<?php

namespace m {

	class ki {

		static $queue = array();

		public $call;
		public $persist;

		public function __construct($call,$persist=false) {

			if(!is_callable($call))
			throw new Exception('specified value not callable');

			$this->call = $call;
			$this->persist = $persist;
			$this->alias = md5(microtime().rand(1,1000));

			return;
		}

		public function exec($argv) {
			return call_user_func_array($this->call,$argv);
		}

		static function flow($key,$argv=null) {
			if(!array_key_exists($key,self::$queue)) return 0;

			if(!is_array($argv) && !is_object($argv))
			$argv = array($argv);

			$count = 0;
			foreach(self::$queue[$key] as $iter => $ki) {
				$ki->exec($argv);
				if(!$ki->persist) {
					unset(self::$queue[$key][$iter]);
				}
				++$count;
			}

			return $count;
		}

		static function queue($key,$call,$persist=false) {
			if(!array_key_exists($key,self::$queue))
			self::$queue[$key] = array();

			self::$queue[$key][] = new self($call,$persist);
			return;
		}

	}

}

?>