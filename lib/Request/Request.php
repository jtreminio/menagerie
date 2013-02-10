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

				// if there is no route key then assume we want the main
				// index, because get can be like that.
				if(!array_key_exists($routekey,$_GET)) $uri = '';

				$uri = $_GET[$routekey];
				unset($routekey);
				break;
			}

			case 'URI': {
				if(!array_key_exists('REQUEST_URI',$_SERVER))
				throw new \Exception('no request uri - unable to determine route');

				$uri = $_SERVER['REQUEST_URI'];
				break;
			}
		}

		// clean up the input.
		$uri = trim($uri);

		// detect index requests.
		if(!$uri || $uri == '/') {
			$this->RouteName = 'Index';
			return;
		}

		// broken requests to index.
		$path = explode('/',$uri);
		if(!count($path)) {
			$this->RouteName = 'Index';
			return;
		}

		// final request name cleanup.
		$this->RouteName = self::PathableToRoutable($path[0]);
		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method static string Pathable
	@arg string Input
	clean the input string to make it safe for URI segments.
	//*/

	static function Pathable($input) {
		return preg_replace('/[^a-z0-9-]/i','',$input);
	}

	/*//
	@method static string Routable
	@arg string Input
	clean the input string to make it safe for routable class lookups.
	//*/

	static function Routable($input) {
		return preg_replace('/[^a-z0-9_]/i','',$input);
	}

	/*//
	@method static string PathableToRoutable
	@arg string Input
	convert the input string from a pathable format to a routable format.
	//*/

	static function PathableToRoutable($input) {
		return
		self::Routable(
		str_replace(' ','',
		ucwords(
		str_replace('-',' ',
		self::Pathable($input)
		))));
	}

}
