<?php

/*
default exception handler.

this is a prototype. it needs to be worked on quite a bit more but this is the
general idea of what it is to do. i would like to do surface dectection and all
that later.
*/

set_exception_handler(function($e){
	$verbose = m\Option::Get('menagerie-error-verbose');

	if($verbose) {
		echo 'An error is preventing normal operations.', PHP_EOL;
		echo $e;
	} else {
		echo 'An error is preventing normal operations.';
	}

	return false;
});
