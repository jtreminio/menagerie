<?php

/*//
@package Database
@project Menagerie
@version 1.0.1
@author Bob Majdak Jr <bob@theorangehat.net>
//*/

/*//
@namespace m
@extern
//*/

namespace m;
use \m as m;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

Ki::Queue('m-config',function(){
	Option::Define([
		'database-connections' => [],
		'database-log-queries' => false
	]);

	return;
});

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/*//
@class Database

provides connection and query access to various database sources. also maintains
connections throughout an application instance so that they can be reused rather
than waste time/resources establishing new connections every time a new instance
of this query interface is created.
//*/

class Database {

	/*//
	@property static array DBX
	holds all the database connections opened this application instance.
	//*/

	static $DBX = array();

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@property private object Driver
	the database driver instance used by this object.
	//*/

	private $Driver;

	/*//
	@property private Boolean Reused
	marks if the connection is being reused from a previous instance or not.
	not really important other than for seeing that the connection cache is
	working.
	//*/

	private $Reused;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method private boolean ValidateConfig
	@arg object ConfigEntry
	@flags internal

	well tell you if the configuration for this database entry looks sane
	enough to attempt to use.
	//*/

	private function ValidateConfig($config) {
		$require = array(
			'driver',
			'hostname',
			'username','password',
			'database'
		);

		foreach($require as $property) {
			if(!property_exists($config,$property))
			return false;
		}

		return true;
	}

	/*//
	@method private boolean LoadDriver
	@arg string ConfigName
	@arg object ConfigEntry
	@flags internal

	will attempt to load the driver requested by the configuration.
	//*/

	private function LoadDriver($name,$config) {
		$driver = "m\\Database\\Drivers\\{$config->driver}";

		try { m_load_class($driver); }
		catch(Exception $e) { return false; }

		return true;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function __construct($which=null) {
		if(!$which) $which = 'default';

		//. if the requested database has already been connect to before
		//. then we should totally reuse that connection and run with it.
		if(array_key_exists($which,self::$DBX)) {
			$this->Driver = self::$DBX[$which];
			$this->Reused = true;
			return;
		}

		$this->Reused = false;

		//. get the database configuration from the option api.
		$config = Option::Get('database-connections');
		if(!$config or !is_array($config))
			throw new \Exception('database configuration is nowhere near valid');

		//. check that we have the requested config.
		if(!array_key_exists($which,$config))
			throw new \Exception("no valid database configuration for {$which}");

		//. check that the config is good.
		$cfg = (object)$config[$which];
		if(!$this->ValidateConfig($cfg))
			throw new \Exception("invalid configuration");

		//. check that we have the required driver.
		if(!$this->LoadDriver($which,$cfg))
			throw new \Exception("no driver for {$cfg->Driver}");

		//. check that we can connect.
		$start = microtime(true);
		if(!$this->Connect($cfg)) {
			throw new \Exception("unable to connect to database {$which}");
		}
		$this->Driver->ConnectTime = microtime(true) - $start;

		// keep for later reuse.
		self::$DBX[$which] = $this->Driver;

		return;
	}

	public function __call($func,$argv) {
		// function connect();
		// function disconnect();
		// ... and anything else not specified but may be provided by drivers.

		if(!method_exists($this->Driver,$func))
			throw new \Exception('requested method not found in driver.');

		return call_user_func_array(array($this->Driver,$func),$argv);
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method public m\DatabaseQuery Queryf
	@arg string format

	perform a query against the current database and return an object that
	defines the status of the query result.
	//*/

	public function Queryf($fmt) {
		$argv = func_get_args();
		unset($argv[0]);

		/*
		allow sprintf style use of this method, however all arguments to
		be subsituted into the final string are escaped automatically.
		it is intentional that the container (first argument) is not
		escaped. there will have to be a tutorial to explain how to
		properly use this method for optimal SQL injection protection.

			`SELECT * FROM users WHERE u_email LIKE "%s";`,
			`who@where.what`
		*/

		// protect arguments against injection.
		foreach($argv as &$arg)
		$arg = $this->Driver->Escape($arg);

		// compile the finished query string.
		$sql = vsprintf($fmt,$argv);

		// do a query tracking the time it took.
		$start = microtime(true);
		$q = $this->Driver->Query($sql);
		$querytime = microtime(true) - $start;

		// account for the query and time.
		$this->Driver->QueryTime += $querytime;
		$this->Driver->QueryCount++;

		// log the query.
		if(Option::Get('database-log-queries')) {
			Ki::Flow('log-debug',sprintf(
				"{%.3f} %s%s",
				$querytime,
				trim($sql),
				PHP_EOL
			));
		}

		return $q;
	}

}

?>