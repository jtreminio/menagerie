<?php

namespace m\Request\Input;
use \m as m;

class Get extends m\Request\Input {

	public function __construct($opt=null) {
		parent::__construct('get',$opt);
		return;
	}

}
