<?php

require(sprintf('%s/start.php',dirname(__FILE__)));

if(m\Option::Get('menagerie-router-magic')) {
	$request = new m\Request;

	// check the namespace to pull routes out of.
	$namespace = m\Option::Get('menagerie-router-namespace');
	if(!$namespace) throw new \Exception('No namespace to pull routes from.');

	// this should not be too much bother in terms of accessing things that
	// should not be accessed since namespaces do not support any syntax like
	// the ..\ etc.
	$class = "{$namespace}\\{$request->RouteName}";
	m_load_class($class); // throws an exception on failure.


	// test that the class is somehow a subclass of our request router so that
	// we can trust it can do what it needs to do.
	if(!(new ReflectionClass($class))->isSubclassOf('m\Request\Route'))
	throw new \Exception('Selected router is not a valid route.');

	// run the route.
	$router = new $class;
	$router->Execute();
} else {

}

m\Ki::Flow('m-shutdown');
