<?php

namespace m\Docbook\Tags;

class Exception extends \Exception {

	public function __construct($need,$got) {
		parent::__construct(sprintf(
			"\n\nExpect: %s\nInput:  @%s %s\n\n",
			$need,
			$got->Tag,
			$got->TagContent
		));
	}

}
