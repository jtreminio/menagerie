<?php

namespace m\Request;

abstract class Route {

	abstract public function Response();

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@property public int ResponseCode
	the http response code to send when sending the response.
	//*/

	public $ResponseCode = 200;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	final public function Execute() {

		// allow the route to do its stuff, more or less anything it wants.
		ob_start();
		$this->Response();
		$response = ob_get_clean();

		// after which send the respose code and response.
		http_response_code($this->ResponseCode);
		echo $response;

		return;
	}
}
