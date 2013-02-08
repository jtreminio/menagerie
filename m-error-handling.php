<?php

/*
setting up application logging.

create the logging instance for writing messages to the debugging log.
*/

m\Ki::Queue('m-init',function(){
	$file = m\Option::Get('log-filename');
	$events = m\Option::Get('log-events');
	$format = m\Option::Get('log-format');
	if(!file || !is_array($events) || !count($events)) return;

	m\Stash::Set('log',($log = new m\Log([
		'Filename' => $file,
		'Events'   => $events,
		'Format'   => $format
	])));

	return;
});

/*
default exception handler.

this is a prototype. it needs to be worked on quite a bit more but this is the
general idea of what it is to do. i would like to do surface dectection and all
that later.
*/

set_exception_handler(function($e){
	$verbose = m\Option::Get('menagerie-error-verbose');

	if($verbose) {
		echo 'An error is preventing normal operations. (Verbose).', PHP_EOL;
		echo $e;
	} else {
		echo 'An error is preventing normal operations. (Silent).';
	}

	return;
});
