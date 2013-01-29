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

function m_autoloader($classname){

	// if this is not a framework class then do not use this this customized
	// autoloading structure.

	if(strpos($classname,'m\\') !== 0) return;

	// https://bugs.php.net/bug.php?id=60996
	$classname = str_replace('\\','/',$classname);

	// input: m\Surface
	// output: m\Surface\Surface.php
	$filepath = sprintf(
		'%s%s%s',
		dirname(m\FrameworkRoot),
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

spl_autoload_register('m_autoloader');
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