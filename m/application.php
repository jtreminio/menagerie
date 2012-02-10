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
	// class m\db, we will attempt to load /m/db/db.php.

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
		
		if(defined('m\ready')) {
			m\ki::flow('m-config');
			m\ki::flow('m-setup');
			m\ki::flow('m-ready');
		}
		
		return true;
	} else {
		return false;
	}

});

/*// autoload step two
  // if the custom autoloader fails allow php to continue on and
  // attempt the default autoloader that is PSR-0 compliant. may
  // deity of choice have mercy on you if you add classes to to
  // the root m namespace.
  //*/

spl_autoload_register(function($classname){
	spl_autoload($classname);

	if(defined('m\ready') and class_exists($classname)) {
		m\ki::flow('m-config');
		m\ki::flow('m-setup');
		m\ki::flow('m-ready');
	}
	
	return;
});

/*// load configuration
  // the application.conf.php file stores all the application specific
  // options and settings.
  //*/

require(sprintf('%s/application.conf.php',m\root));

/*// when ready...
  // some things to do once the framework decides it is ready to
  // proceed with the rest of the application.
  //*/
  
m\ki::queue('m-ready',function(){
	define('m\ready',gettimeofday(true));
	return;
});

/*// init train
  // flow some ki to allow libraries to setup as they need.
  //*/

// init. things defined in an m-init ki block should be designed for
// setting or loading values core to the operation of the framework,
// and are designed to change how it behaves from the ground up.
m\ki::flow('m-init');

// config. things defined in an m-config ki block are for setting
// values that could be used at any point during an application, but
// primarily get used by libraries when they...
m\ki::flow('m-config');

// setup. things defined in an m-setup ki block are for initializing
// states and setting up any instances that need to be done for the
// rest of the application.
m\ki::flow('m-setup');

// ready. once the framework is ready this ki flows, setting any last
// late minute values before handing the process over to the
// application using the framework.
m\ki::flow('m-ready');

?>