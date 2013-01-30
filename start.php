<?php

///////////////////////////////////////////////////////////////////////////////
// Menagerie //////////////////////////////////////////////////////////////////

define('Menagerie',true);
define('MenagerieVersion','1.1.0');
define('MenagerieTime',1359492856);

define('m\FrameworkRoot',dirname(__FILE__),true);
define('m\TimeInit',gettimeofday(true),true);

////////////////////////////////////////////////////////////////////////////////
// core support ////////////////////////////////////////////////////////////////

require(sprintf('%s/m-core.php',m\FrameworkRoot));
m_require('-/lib/Ki/Ki.php');
m_require('-/lib/Option/Option.php');

////////////////////////////////////////////////////////////////////////////////
// default settings ////////////////////////////////////////////////////////////

m\Option::Set(array(
	'menagerie-autoloader' => true,
	'app-name'             => 'Menagerie',
	'app-short-desc'       => 'Namespaced PHP 5.4 Framework',
	'app-long-desc'        => 'A lightweight PHP 5.4 namespaced framwork.',
	'app-keywords'         => ['PHP 5.4','Namespace','Framework']
));

////////////////////////////////////////////////////////////////////////////////
// configuration file //////////////////////////////////////////////////////////

if(!defined('MenagerieConfig')) m_require('-/menagerie.conf.php');
else m_require(MenagerieConfig);

////////////////////////////////////////////////////////////////////////////////
// the autoloader //////////////////////////////////////////////////////////////

if(m\Option::Get('menagerie-autoloader'))
require(sprintf('%s/m-autoloader.php',m\FrameworkRoot));

