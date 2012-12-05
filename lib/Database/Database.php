<?php

namespace m {
	use \m as m;

	class Database {

		static $dbx = array();

		private $driver;

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

		private function LoadDriver($name,$config) {
			$driver = "m\\Database\\Drivers\\{$config->driver}";

			//. the PSR-0 autoloader should handle the driver being loaded
			//. when we ask for it like this.

			if(!class_exists($driver,true)) {
				return false;
			} else {
				$this->driver = new $driver($name,$config);
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
			if(array_key_exists($which,self::$dbx)) {
				$this->driver = self::$dbx[$which];
				return;
			}

			//. get the database configuration from the option api.
			$config = option::get('m-database');
			if(!$config or !is_array($config))
				throw new \Exception('database configuration is nowhere near valid');

			//. check that we have the requested config.
			if(!array_key_exists($which,$config))
				throw new \Exception("no valid database configuration for {$which}");

			//. check that the config is good.
			$cfg = (object)$config[$which];
			if(!$this->validateConfig($cfg))
				throw new \Exception("invalid configuration");

			//. check that we have the required driver.
			if(!$this->loadDriver($which,$cfg))
				throw new \Exception("no driver for {$cfg->driver}");

			//. check that we can connect.
			if(!$this->connect($cfg)) {
				throw new \Exception("unable to connect to database {$which}");
			}

			return;
		}

		public function __call($func,$argv) {
			// function connect();
			// function disconnect();
			// ... and anything else not specified but may be provided by drivers.

			if(!method_exists($this->driver,$func))
				throw new \Exception('requested method not found in driver.');

			return call_user_func_array(array($this->driver,$func),$argv);
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
			$arg = $this->driver->escape($arg);

			// compile the finished query string.
			$sql = vsprintf($fmt,$argv);

			// do a query.
			return $this->driver->query($sql);
		}

	}


}

?>