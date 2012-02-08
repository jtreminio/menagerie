<?php

define('m\root',dirname(__FILE__));
define('m\timeinit',gettimeofday(true));

/*// autoload step one
  // allow subdirectories to be used for the class name to help keep
  // the project folder organized.
  //*/

spl_autoload_register(function($classname){

	// this custom autoloader will allow you to load classes from
	// a determined subfolder if it exists. for example, asking for
	// class m\db, we will attempt to load /m/db/db.php. if it does
	// not exist then we will allow the standard PSR-0 autoloader to
	// try and load /m/db.php.

	$classname = str_replace('\\','/',$classname);
	// ^^^^^ https://bugs.php.net/bug.php?id=60996
	
	$filepath = sprintf(
		'%s/%s/%s.php',
		dirname(m\root),
		$classname,
		basename($classname)
	);
	
	if(file_exists($filepath)) {
		require($filepath);
		return true;
	} else {
		return false;
	}

});

/*// autoload step two
  // if the custom autoloader fails allow php to continue on and
  // attempt the default autoloader that is PSR-0 compliant.
  //*/

spl_autoload_register();


/*// load configuration
  // the application.conf.php file stores all the application specific
  // options and settings.
  //*/

require(sprintf('%s/application.conf.php',m\root));

?>