<?php

namespace m;

/*//
@class m\Stash

a static singleton class for passing objects throughout the current instance of
the application. the stash is designed to hold objects of importance that should
only ever exist once (e.g. singletons).
//*/

final class Stash {

	/*//
	@property static array Instances
	an array indexing all the data stored by the stash.
	//*/

	static $Instances = array();

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function __construct() { return; }

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method static mixed Get
	@arg string Key

	retrieve a specific value from the stash.
	//*/

	static function Get($key) {
		if(self::Has($key)) return self::$Instances[$key];
		else return false;
	}

	/*//
	@method static boolean Has
	@arg string Key

	check if there is already a key in the stash with this name.
	//*/

	static function Has($key) {
		if(array_key_exists($key,self::$Instances)) return true;
		else return false;
	}

	/*//
	@method static mixed Set
	@arg string Key
	@arg mixed Data
	@arg boolean Overwrite

	store data under a specific key in the stash. by default the stash will
	not overwrite anything already stored under that key and will throw an
	exception if you try.
	//*/

	static function Set($key,$obj,$overwrite=false) {
		// unless explicitly stated do not overwrite existing objects
		// by default. since the idea here is a singleton manager.
		if(!$overwrite && self::Has($key))
		throw new \Exception("already have an object named {$key}");

		return self::$Instances[$key] = $obj;
	}

	/*//
	@method static void Destroy
	@arg string Key

	remove something from the stash. if the something is an object and the
	destructor is accessable we will force the object to destroy itself.
	//*/

	static function Destroy($key) {
		if(self::Has($key)) {
			if(is_object(self::$Instances[$key])){
				if(is_callable([self::$Instances[$key],'__destruct']))
				self::$Instances[$key]->__destruct();
			}

			unset(self::$Instances[$key]);
		}

		return;
	}

}


?>