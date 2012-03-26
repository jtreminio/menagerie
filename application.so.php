<?php

namespace {

	function m_exec_time($suffix=true) {
		return (($suffix)?
			(round(gettimeofday(true) - m\timeinit,3) . 'sec'):
			(round(gettimeofday(true) - m\timeinit,3))
		);
	}

	/*// Constant Utilities
	  // some small wrapping functions to help make your code a little
	  // cleaner when dealing with testing constants.
	  //*/
	  
	function m_define($const,$value) {
		if(!defined($const)) {
			define($const,$value);
			return true;
		} else {
			return false;
		}
	}

	function m_defined_as($const,$value) {
		if(defined($const) && constant($const) == $value) return true;
		else return false;
	}

	function m_defined_false($const) {
		if(defined($const) && constant($const) === false) return true;
		else return false;
	}

	function m_defined_true($const) {
		if(defined($const) && constant($const) === true) return true;
		else return false;
	}
	
	/*// File Utilities
	  // doing some things with files and the loading of files
	  //*/
	  
	function m_load($input) {
		if(is_string($input))
		$input = array($input);
		
		foreach($input as $class) {
			if(!class_exists($class,true))
			throw new Exception("unable to load class {$class}");
		}
		
		return;
	}

	function m_require($__m_filename,$__m_scope=null) {

		// support some shorthand for referencing files from where the framework
		// currently resides.
		if(preg_match('/^-\//',$__m_filename))
		$__m_filename = preg_replace(
			'/^-\//',
			sprintf('%s/',m\root),
			$__m_filename
		);

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

}

?>