<?php

namespace m\Docbook\Tags;

class ClassTag extends GenericTag {

	public $Name;

	public function Parse() {

		// @class name

		$this->Name = $this->TagContent;

		return;
	}

}
