<?php

namespace m\Request;
use \m as m;

class Router {

	/*//
	@property protected m\Request Request
	a request object representing the current request.
	//*/

	protected $Request;

	/*//
	@property protected RouteClass
	the class name of the router it will use.
	//*/

	protected $RouteClass;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function __construct() {
		$this->Request = new m\Request;
		$this->LoadRouteClass();
		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method protected void LoadRouteClass
	load the class that will be used to run the selected route.
	//*/

	protected function LoadRouteClass() {

		// check the namespace to pull routes out of.
		$namespace = m\Option::Get('menagerie-router-namespace');
		if(!$namespace) throw new \Exception('No namespace to pull routes from.');

		// try and autoload the class in.
		$this->RouteClass = "{$namespace}\\{$this->Request->RouteName}";
		m_load_class($this->RouteClass); // throws an exception on failure.

		// test that the class is somehow a subclass of our request router so
		// that we can trust it can do what it needs to do.
		if(!(new \ReflectionClass($this->RouteClass))->isSubclassOf('m\Request\Route'))
		throw new \Exception('Selected router is not a valid route.');

		return;
	}

	/*//
	@method public void Execute
	run the rout.
	//*/

	public function Execute() {
		$route = new $this->RouteClass;
		return $route->Execute();
	}

}
