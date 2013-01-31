<?php

namespace m;

class Ki {

	/*//
	@property static array Queue
	a singleton array holding the current queue of event handlers.
	//*/

	static $Queue = array();

	/*//
	@property public callable Call
	this is the callback that should be executed when this event item is
	triggered.
	//*/

	public $Call = null;

	/*//
	@property public boolean Persist
	mark if this event item should be kept in the queue after it is used.
	the default is that items in the queue are removd after they are used
	once. with this true they will stick around for each occurance of the
	event that happens throughout the entire application.
	//*/

	public $Persist = false;

	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////

	public function __construct($call,$persist=false) {
		if(!is_callable($call))
		throw new \Exception('specified value not callable');

		$this->Call = $call;
		$this->Persist = $persist;
		$this->Alias = md5(microtime().rand(1,1000));

		return;
	}

	//////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/*//
	@method public mixed Exec
	@arg mixed
	@return mixed

	run the callable associated with this ki event.
	//*/

	public function Exec($argv) {
		return call_user_func_array($this->Call,$argv);
	}

	/*//
	@method static int Float
	@arg string Key
	@arg objec/array Argv

	flow all the ki events for the specified Key. returns a count of how
	many events were executed.
	//*/

	static function Flow($key,$argv=null) {
		if(!array_key_exists($key,self::$Queue)) return 0;

		if(!is_array($argv) && !is_object($argv))
		$argv = array($argv);

		$count = 0;
		foreach(self::$Queue[$key] as $iter => $ki) {
			$ki->Exec($argv);

			if(!$ki->Persist)
			unset(self::$Queue[$key][$iter]);

			++$count;
		}

		return $count;
	}

	/*//
	@method static void Queue
	@arg string Key
	@arg callable Func
	@arg boolean Persist

	add a handler to the queue of ki events.
	//*/

	static function Queue($key,$call,$persist=false) {
		if(!array_key_exists($key,self::$Queue))
		self::$Queue[$key] = array();

		self::$Queue[$key][] = new self($call,$persist);
		return;
	}

}
