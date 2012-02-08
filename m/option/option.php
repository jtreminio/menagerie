<?php

namespace m {

	class option {
		
		static $storage = array();

		static function get($key) {
			if(array_key_exists($key,self::$storage)) return self::$storage[$key];
			else return null;				
		}

		static function set() {
			$argv = func_get_args();
			if(!count($argv)) throw new Exception('expected [string,mixed] or [array(string=>mixed,...)]');
			
			if(is_array($argv[0])) {
				foreach($argv[0] as $key => $value) {
					self::set($key,$value);
				}
			} else {
				self::$storage[$argv[0]] = $argv[1];
			}
			
			return;
		}

	}

}

?>