<?php

namespace m;
use \m as m;

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

		//. the PSR-0 autoloader should handle the driver being loaded
		//. when we ask for it like this.

		if(!class_exists($driver,true)) {
			return false;
		} else {
			$this->Driver = new $driver($name,$config);
			return true;
		}
	}


	/*// Public Database API.
	  // The methods you will use to interact with the database.
	  //*/

	/* database->__construct(string config);
	 *
	 * when a new database instance is created a new database connection
	 * will be created using the parameters defined from the application
	 * configuration file, and the entry in it specified.
	 *
	 * connections are held open by the database class in a static list
	 * so that if another database instance is created later, the
	 * database connection will be reused instead of recreated.
	 *
	 */

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
		$config = Option::Get('m-database');
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

		// do a query.
		$start = microtime(true);
			$q = $this->Driver->Query($sql);
		$querytime = microtime(true) - $start;

		$this->Driver->QueryTime += $querytime;
		$this->Driver->QueryCount++;

		// not sure i really want to keep this like this. but it is good
		// enough to get past a debugging i need to work through.
		if($log = m\Option::Get('database-query-log')) {
			if(is_writable($log)) {
				$fp = fopen($log,'a');
				fwrite($fp,sprintf("[%.3f] %s%s",$querytime,trim($sql),PHP_EOL));
				fclose($fp);
			} else {
				throw new \Exception('the query log you want to write is not writable.');
			}
		}

		return $q;
	}

}

?>