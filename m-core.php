<?php


/*//
@function mixed m_exec_time
@arg boolean Suffix default true

returns a value (float or string) that represents how long it has been
since the framework first initalized. can be used to debug what is wasting
too much time in the framework or your application.
//*/

function m_exec_time($suffix=true) {
	return (($suffix)?
		(round(gettimeofday(true) - m\timeinit,3) . 'sec'):
		(round(gettimeofday(true) - m\timeinit,3))
	);
}

///////////////////////////////////////////////////////////////////////////
// constant utilities /////////////////////////////////////////////////////

// some small convience functions that will make user code a little cleaner
// since they will do some of the common "duh, check that" tests too.

/*//
@function boolean m_define
@arg string ConstantName
@arg mixed ConstantValue

create a constant checking first that it does not actually exist before
attempting to create it. makes your if statements a little shorter and
maybe prevent throwing an E_NOTICE if you just plain forgot to check first.
//*/

function m_define($const,$value) {
	if(!defined($const)) {
		define($const,$value);
		return true;
	} else {
		return false;
	}
}

/*//
@function boolean m_defined_as
@arg string ConstantName
@arg mixed Value

this will in one quick motion check that this will check that the constant
you want exists and that it is equal to the value (and type) that you want
it to be. if it does you get true, if not you get false.
//*/

function m_defined_as($const,$value) {
	if(defined($const) && constant($const) === $value) return true;
	else return false;
}

/*//
@function boolean m_defined_false
@arg string ConstantName

this will check that the constant you want exists and that it is exactly
set to Boolean False and Boolean False alone.
//*/

function m_defined_false($const) {
	if(defined($const) && constant($const) === false) return true;
	else return false;
}

/*//
@function boolean m_defined_true
@arg string ConstantName

this will check that the constant you want exists and that it is exactly
set to Boolean True and Boolean True alone.
//*/

function m_defined_true($const) {
	if(defined($const) && constant($const) === true) return true;
	else return false;
}

///////////////////////////////////////////////////////////////////////////
// file utilities /////////////////////////////////////////////////////////

/*//
@function boolean m_load
@arg string|array ClassName

this will attempt to load a class by means of the autoloader. in the event
that the class could not be loaded it will throw an exception. it can be
given either a single class name as a string, or an array of them.
//*/

function m_load_class($input) {
	if(is_string($input))
	$input = array($input);

	foreach($input as $class) {
		if(!class_exists($class,true))
		throw new Exception("unable to load class {$class}");
	}

	return true;
}

/*//
@function string m_repath_uri
@arg string Input

given a string which is assumed to be a filepath of some sort, convert any
wrong way slashes into forward slashes for use in URIs.
//*/

function m_repath_uri($input) {
	return str_replace('\\','/',$input);
}

/*//
@function string m_repath_fs
@arg string Input

given a string which is assumed to be a filepath of some sort, convert any
slashes into the proper directory separator in use by the current server
operationg system.
//*/

function m_repath_fs($input) {
	return preg_replace('/[\\/\\\\]/',DIRECTORY_SEPARATOR,$input);
}

/*//
@function boolean m_require
@arg string Filename
@arg array|object Scope

attempt to require a file into PHP but inside of a clean scope jail so that
it cannot pollute the framework or application with variables or modify
any that may be in that current state. it also does checks that many people
may forget to do such as checking that the file even exists and is
readable by the system.

it also has a few custom loading mechanisms to make loading common things
easier.

* "-/share/thirdparty.php"
  Filenames which are perfixed with a dash-forward-slash will be prefixed
  with the root path of the framework. you should use forward slashes to
  denote directories in this case.

* "-lSurface"
  Things prefixed with dash-l is shorthand for loading libraries and will
  engage Menagerie's autoloading mechanisms to load what you requested.
  basically, its -l<ClassName>.

* anything else will be treated as a normal filepath on the system.

if a file was loaded it will return true, if not false. the only time an
error will be thrown is if the autoloading mechanism was used with -l in
which case an exception will be thrown if it failed.

//*/

function m_require($__m_filename,$__m_scope=null) {

	// custom behaviours /////////////////////////////////////////////////

	if(strpos($__m_filename,'-') === 0) {

		// support loading via the autoloading mechanism.
		if(preg_match('/^-l\h?(.+?)$/',$__m_filename,$match)) {
			return m_load_class("m\\{$match[1]}");
		}

		// support some shorthand for referencing files from where the
		// framework currently resides.
		if(preg_match('/^-\//',$__m_filename)) {
			$__m_filename = m_repath_fs(preg_replace(
				'/^-\//',
				sprintf('%s/',m\FrameworkRoot),
				$__m_filename
			));
		}
	}

	//////////////////////////////////////////////////////////////////////

	// check if the file we want exists.
	if(!file_exists($__m_filename) || !is_readable($__m_filename))
	return false;

	// populate the local scope if data was supplied. note this should
	// have been an associative array to actually receive proper data.
	if(is_array($__m_scope))
	extract($__m_scope);

	require($__m_filename);
	return true;
}
