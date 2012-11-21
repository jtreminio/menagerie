<?php

require(sprintf(
	'%s/application.php',
	dirname(dirname(__FILE__))
));
m\CLI::Only();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

$cli = new m\CLI;
$sensei = new m\Docbook\Sensei;
$sensei->SetOutputDirectory(dirname(__FILE__));

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// allow a custom input file.
if($cli->input) $filepath = $cli->input;
else $filepath = m_repath_fs(sprintf('%s/input.txt',dirname(__FILE__)));

if(!file_exists($filepath) || !is_readable($filepath))
throw new Exception('no input lulz');

// read file.
$filelist = file($filepath,(FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));

// convert to filesystem paths and expand any globs.
$newlist = array();
foreach($filelist as $fiter => $file) {
	$file = m_repath_fs(sprintf(
		'%s/%s',
		dirname(m\root),
		$file
	));

	if(strpos($file,'*') !== false) {
		$newlist = array_merge($newlist,glob($file));
	} else {
		$newlist = $file;
	}
}
$filelist = $newlist;

foreach($filelist as $file) {
	m_printfln('>> %s',$file);
	$sensei->SetFilename($file);
	$sensei->Document();
}

$sensei->Sort();
$sensei->WriteMarkdownDocument();
