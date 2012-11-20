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

$filepath = m_repath_fs(sprintf('%s/input.txt',dirname(__FILE__)));

if(!file_exists($filepath) || !is_readable($filepath))
throw new Exception('no input lulz');

$filelist = file($filepath,(FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));
foreach($filelist as $file) {
	$file = m_repath_fs(sprintf('%s/%s',dirname(m\root),$file));

	m_printfln('>> %s',$file);
	$sensei->SetFilename($file);

	$sensei->Document();
	$sensei->WriteMarkupDocument();
}
