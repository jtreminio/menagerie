<?php

////////////////////////////////////////////////////////////////////////////////
// autoloading /////////////////////////////////////////////////////////////////

/*
the first step in getting autoloading to work is to include the directory which
contains the framework in the PHP include path. this will enable magic lookups
of classes for both the menagerie framework and any application libraries you
namespace nextdoor to m.
*/

set_include_path(sprintf(
	'%s%s%s',
	get_include_path(),
	PATH_SEPARATOR,
	dirname(dirname(__FILE__))
));

////////////////////////////////////////////////////////////////////////////////
// menagerie namespace autoloader //////////////////////////////////////////////

/*
this autoloader will allow us to autoload files in a non-standard way (from lib
folder) for classes in the menagerie namespace.

first it will attempt this transformation:

 = <FrameworkRoot>/lib/<ClassFullname>/<ClassBasename>.php
 - new m\Database               => m/lib/Database/Database.php
 - new m\Database\Drivers\MySQL => m/lib/Database/Drivers/MySQL/MySQL.php

failing that, it will attempt:

 = <FrameworkRoot>/lib/ClassFullname>.php
 - new m\Database\Query         => m/lib/Database/Query.php
 - new m\Database\Drivers\MySQL => m/lib/Database/Drivers/MySQL.php

with this autoloader we can create directory structures within the framework
of self-contained and easy to nagivate libraries. for example the actual
database library included in menagerie looks like this:

 = <FrameworkRoot/lib/<Library>/<SupportingFilesAndDirectories>
 - new m\Database               => m/lib/Database/Database.php
 - new m\Database\Driver        => m/lib/Database/Driver.php
 - new m\Database\Query         => m/lib/Database/Query.php
 - new m\Database\Drivers\MySQL => m/lib/Database/Drivers/MySQl.php

if the drivers became mad complex they could be broken up like this:

 - m/lib/Database/Drivers/MySQl/MySQL.php
 - m/lib/Database/Drivers/MySQL/Query.php

in that example, *everything* related to the database library is in a database
folder instead of having the main database class next to a database folder, and
this autoloader will catch them.
*/

function m_autoloader($classname){

	// do not even attempt this autoloader if the requested class is not in the
	// menagerie namespace.
	if(strpos($classname,'m\\') !== 0)
	return false;

	// convert the slashes to forward slash so that basename and dirname work
	// on them regardless of which OS we are currently on.
	// -- per https://bugs.php.net/bug.php?id=60996
	$classpath = str_replace('\\','/',$classname);

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	// try <FrameworkRoot>/lib/<ClassFullname>/<ClassBasename>.php
	$filepath = sprintf(
		'%s%s%s',
		dirname(m\FrameworkRoot),
		DIRECTORY_SEPARATOR,
		preg_replace('/^m\//','m/lib/',sprintf(
			'%s/%s.php',
			$classpath,
			basename($classpath)
		))
	);
	if(file_exists($filepath)) goto found;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	// try <FrameworkRoot>/lib/<ClassFullname>.php
	$filepath = sprintf('%s.php',dirname($filepath));
	if(file_exists($filepath)) goto found;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	// if we made it this far then we did not find any files that matched our
	// design pattern, and therefore should quit. if we found files we jumped
	// over this with the goto. now i can say i have used a goto for the first
	// time in my life.
	return false;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	found: {
		require_once($filepath);

		// stop now if the class was not loaded by the inclusion of this file.
		// btw, this means your code is bad.
		if(!class_exists($classname)) return false;

		// if loading a library late then allow it to process any config and
		// setup hooks it might have contained.
		if(defined('m\Ready')) {
			m\Ki::flow('m-config');
			m\Ki::flow('m-setup');
		}

		return true;
	}
}

spl_autoload_register('m_autoloader');


////////////////////////////////////////////////////////////////////////////////
// application autoloader //////////////////////////////////////////////////////

/*
this is a more standard autoloader which will work beside the special one for
the menagerie framework. it will still operate off the base directory set in
the include path above, but without the /lib/ and subdirectory support.

this is how people *expect* autoloaders to work with namespaces, and for any
application code using the framework it is a good system to use.

 = <ClassFullname>.php
 - new OrangeHat\Demo\Example => OrangeHat/Demo/Example.php

and this operates as a neighbour to the m directory.

 - new m\Database             => <AppRoot>/m/lib/Database/Database.php
 - new OrangeHat\Demo\Example => <Approot>/OrangeHat/Demo/Example.php

see?
*/

spl_autoload_register(function($classname){
	spl_autoload($classname);
	if(!class_exists($classname)) return false;

	// if loading a library late then allow it to process any config and setup
	// hooks it might have contained.
	if(defined('m\Ready') && class_exists($classname)) {
		m\Ki::flow('m-config');
		m\Ki::flow('m-setup');
	}

	return true;
});
