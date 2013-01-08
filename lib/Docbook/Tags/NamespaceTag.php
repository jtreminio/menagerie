<?php

namespace m\Docbook\Tags;

class NamespaceTag extends GenericTag {

	public $Name;

	public function Parse() {

		// @namespace name

		$this->Name = $this->TagContent;

		return;
	}

}
