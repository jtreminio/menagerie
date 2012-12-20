<?php

namespace m\Request\Input;
use \m as m;

class Post extends m\Request\Input {

	public function __construct($opt=null) {
		parent::__construct('post',$opt);
		return;
	}

}
