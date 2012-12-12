<?php

/*//
@package Appcache
@project Menagerie
@version 1.0.0
@author Bob Majdak Jr <bob@theorangehat.net>
//*/

/*//
@namespace m
@extern
//*/

namespace m;
use \m as m;

/*//
@class Appcache

a not-even-singleton class that is used to manage a global set of storage that
you can use as a means to cache various things in an application. this is a
simple easy way to add a first layer of cache to an application.

it is undecided if this will be extended to include Memcached support or if that
will be a separate package. this class might even end up disapearing or managed
by a new simple Cache class later. I DONT KNOW, ISNT IT FUN!?
//*/

class Appcache {

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@property static Storage

	a global storage location where the appcache shoves everything you want to
	keep.
	//*/

	static $Storage = array();

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@static void Drop
	@arg string Key

	delete something from the appecache.
	//*/

	static function Drop($key) {
		if(array_key_exists($key,self::$Storage))
		unset(self::$Storage[$key]);

		return;
	}

	/*//
	@static void Get
	@arg string Key

	fetch something from the appcache. if it does not exist then boolean false
	is returned instead.
	//*/

	static function Get($key) {

		if(array_key_exists($key,self::$Storage))
		return self::$Storage[$key];

		else
		return false;

	}

	/*//
	@static void Set
	@arg string Key
	@arg mixsed Data

	store something in the appcache.
	//*/

	static function Set($key,$data) {
		self::$Storage[$key] = $data;
		return;
	}

}
