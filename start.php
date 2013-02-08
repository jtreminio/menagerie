<?php

///////////////////////////////////////////////////////////////////////////////
// Menagerie //////////////////////////////////////////////////////////////////

define('Menagerie',true);
define('MenagerieVersion','1.1.0');
define('MenagerieTime',1359492856);

define('m\FrameworkRoot',dirname(__FILE__),true);
define('m\TimeInit',gettimeofday(true),true);

if(!session_id()) session_start();

////////////////////////////////////////////////////////////////////////////////
// core support ////////////////////////////////////////////////////////////////

require(sprintf('%s/m-core.php',m\FrameworkRoot));
m_require('-/m-autoloader.php');
m_require('-/m-error-handling.php');

////////////////////////////////////////////////////////////////////////////////
// configuration file //////////////////////////////////////////////////////////

m_define('MenagerieConfig','-/menagerie.conf.php');

if(!m_require(MenagerieConfig))
throw new \Exception('unable to load config file');

////////////////////////////////////////////////////////////////////////////////
// default settings ////////////////////////////////////////////////////////////

m\Option::Define(array(
	// core framework options.
	'menagerie-error-verbose'    => false,
	'menagerie-core-library'     => [],
	'menagerie-router-magic'     => true,
	'menagerie-router-type'      => 'GET',
	'menagerie-router-key'       => 'm-route',
	'menagerie-router-namespace' => null,

	// logging options,
	'log-filename' => m_repath_fs(sprintf('%s/log/menagerie.log',m\FrameworkRoot)),
	'log-events'   => ['log-debug','log-warning','log-info'],
	'log-format'   => m\Log::TEXT,

	// application base options.
	'app-name'       => 'Menagerie',
	'app-short-desc' => 'Namespaced PHP 5.4 Framework',
	'app-long-desc'  => 'A lightweight PHP 5.4 namespaced framwork.',
	'app-keywords'   => ['PHP 5.4','Namespace','Framework']
));

////////////////////////////////////////////////////////////////////////////////
// core ki events //////////////////////////////////////////////////////////////

m\Ki::Queue('m-init',function(){

	// loading of additional libraries as a core feature set.

	$libs = m\Option::Get('menagerie-core-library');
	if(!is_array($libs)) return;

	foreach($libs as $lib) {
		if(!m_require("-l{$lib}"))
		throw new Exception("menagerie-core-library {$lib} not found");
	}

	return;
});

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

ini_set('auto_append_file',m_repath_fs(sprintf('%s/shutdown.php',m\FrameworkRoot)));

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// startup events
m\Ki::Flow('m-init');
m\Ki::Flow('m-config');
m\Ki::Flow('m-setup');
m\Ki::Flow('m-ready');

/*
m\Ki::Queue('m-main',function(){
	$surface = m\Stash::Get('surface');
	$surface->Set('test','value');

	echo 'this is the app code lol';
	return;
});

// the main event
m\Ki::Flow('m-main');

// shutdown events
m\Ki::Flow('m-shutdown');
*/