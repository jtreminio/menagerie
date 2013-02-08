<?php

namespace m;

class Request {

	/*//
	@property public string RouteName
	holds the name of the current route which is derived from the uri and
	is considered the first directory of it.
	//*/

	public function __construct() {
		$this->DetermineRouteName();
		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	protected function DetermineRouteName() {
		switch(Option::Get('menagerie-router-type')) {
			case 'GET': {
				$routekey = Option::Get('menagerie-router-key');
				if(!array_key_exists($routekey,$_GET))
				throw new \Exception('no route key in get - unable to determine route');

				$uri = trim(trim($_GET[$routekey]),'/');
				break;
			}

			case 'URI': {
				if(!array_key_exists('REQUEST_URI',$_SERVER))
				throw new \Exception('no request uri - unable to determine route');

				$uri = trim(trim($_SERVER['REQUEST_URI']),'/');
				break;
			}
		}

		if(!$uri || $uri == '/') {
			$this->RouteName = 'Index';
			return;
		}

		$path = explode('/',$uri);
		if(!count($path)) {
			$this->RouteName = 'Index';
			return;
		}

		$this->RouteName = self::PathableToRoutable($path[0]);
		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	static function PathableToRoutable($uri) {
		return str_replace(' ','',ucwords(str_replace('-',' ',$uri)));
	}

}