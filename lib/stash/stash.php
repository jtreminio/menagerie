<?php

namespace m {

	final class stash {
		public function __construct() { return; }

		static $instances = array();

		static function get($key) {
			if(self::has($key)) return self::$instances[$key];
			else return false;
		}

		static function has($key) {
			if(array_key_exists($key,self::$instances)) return true;
			else return false;
		}

		static function set($key,$obj,$overwrite=false) {
			// unless explicitly stated do not overwrite existing objects
			// by default. since the idea here is a singleton manager.
			if(!$overwrite && self::has($key))
			throw new \Exception("already have an object named {$key}");

			return self::$instances[$key] = $obj;
		}

		static function destroy($key) {
			if(self::has($key)) {
				self::$instances[$key]->__destruct();
				unset(self::$instances[$key]);
			}

			return;
		}

	}

}

?>