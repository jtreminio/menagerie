<?php

namespace m;
use \m as m;

class appcache {
	static $storage = array();

	static function drop($key) {

		if(array_key_exists($key,self::$storage))
		unset(self::$storage[$key]);

	}

	static function get($key) {

		if(array_key_exists($key,self::$storage))
		return self::$storage[$key];

		else
		return false;

	}

	static function set($key,$data) {

		self::$storage[$key] = $data;

	}
}
