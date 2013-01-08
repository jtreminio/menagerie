<?php

define('Menagerie',true);
define('MenagerieVersion','1.0.0');
define('MenagerieTime',1355346883);

////////////////////////////////////////////////////////////////////////////////
// these constants are flagged case-insensitive just to ease the transition i
// am making in the codebase. at some future commit they should be set back to
// case sensitive only.

define('m\Root',dirname(__FILE__),true);
define('m\TimeInit',gettimeofday(true),true);

////////////////////////////////////////////////////////////////////////////////
// core library

require(sprintf('%s/application.so.php',m\Root));

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

/*
autoloading step one.
this custom autoloader will allow for a non-standard loading of classes based
on a non-standard file system that i decided i wanted. this autoloader will
handle most of Menagerie's core loading.

lets say you want to load the Database library...
- $surface = new m\Database;
- loaded from m/lib/Database/Database.php

this way all libraries can be completely self-contained in the lib directory.
since the database namespace contains child namespaces for the drivers if i
decied an app should never have database support i could delete that one
directory and have it all, rather than having to delete. also third party or
optional libraries could be distributed as extract and go into the lib directory
and be easy to remove again later.
*/

spl_autoload_register('m_autoloader');
function m_autoloader($classname){

	$classname = str_replace('\\','/',$classname);
	// ^^^^^ https://bugs.php.net/bug.php?id=60996

	// given:  m/Library
	// return: <m\Root>/m/lib/Library/Library.php
	$filepath = sprintf(
		'%s%s%s',
		dirname(m\Root),
		DIRECTORY_SEPARATOR,
		preg_replace('/^m\//','m/lib/',sprintf(
			'%s/%s.php',
			$classname,
			basename($classname)
		))
	);

	// if that file did not exist try:
	// <m\Root>/m/lib/Library.php
	if(!file_exists($filepath))
	$filepath = sprintf('%s.php',dirname($filepath));

	// if that file did not exist, this custom autloader is done.
	if(!file_exists($filepath))
	return false;

	// but if it did load it.
	else {
		require_once($filepath);

		// if loading a library late then allow it to process any config and
		// setup hooks it might have contained.
		if(defined('m\Ready')) {
			m\Ki::flow('m-config');
			m\Ki::flow('m-setup');
		}

		return true;
	}

}

/*
autoload step two.
if the custom autoloader failed to load a class from our custom file system then
allow falling back to PSR-0 style class loading with PHP's default autoload
handler.

This autoloader will handle most of the application library loading of classes
that belong to the project using the framework but not inside of it. this is
also the reason we modified the include path earlier.
*/

spl_autoload_register(function($classname){
	spl_autoload($classname);

	// if loading a library late then allow it to process any config and setup
	// hooks it might have contained.
	if(defined('m\Ready') && class_exists($classname)) {
		m\Ki::flow('m-config');
		m\Ki::flow('m-setup');
	}

	return;
});

////////////////////////////////////////////////////////////////////////////////
// init handler ////////////////////////////////////////////////////////////////

m\Ki::queue('m-init',function(){

	// make a session available.
	if(!session_id()) session_start();

	// require a few of the core Menagerie libraries that provide operational
	// utility.
	m_require('-lKi');
	m_require('-lLog');
	m_require('-lPlatform');
	m_require('-lRequest');

	return;
});

////////////////////////////////////////////////////////////////////////////////
// config handler //////////////////////////////////////////////////////////////

// m\Ki::queue('m-config',function(){ });

////////////////////////////////////////////////////////////////////////////////
// setup handler ///////////////////////////////////////////////////////////////

// m\Ki::queue('m-setup',function(){ });

////////////////////////////////////////////////////////////////////////////////
// ready handler ///////////////////////////////////////////////////////////////

m\Ki::queue('m-ready',function(){
	define('m\Ready',gettimeofday(true),true);
	return;
});

////////////////////////////////////////////////////////////////////////////////
// application config file /////////////////////////////////////////////////////

// applications may configure themselves by setting whatever values are needed
// in the application.conf.php file.
$configfile = sprintf('%s/application.conf.php',m\Root);

if(file_exists($configfile))
require($configfile);

////////////////////////////////////////////////////////////////////////////////
// ki train ////////////////////////////////////////////////////////////////////

// m-init.
// core initialization ki for the framework. mostly should be used only for
// loading additional libraries.
m\Ki::flow('m-init');

// m-config.
// allow libraries which have been loaded to configure themselves with default
// values or register options that they may have.
m\Ki::flow('m-config');

// m-setup.
// used by libraries to setup any instances of objects that may be used by the
// application. for example the m\User library would use this time to see if a
// user is logged in and store that user in the Stash for later use.
m\Ki::flow('m-setup');

// m-ready.
// a notification that we are ready to roll.
m\Ki::flow('m-ready');
