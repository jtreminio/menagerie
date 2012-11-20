<?php

namespace m\Docbook;

class PackageItem {

	public $Name;
	public $Namespaces;
	public $Options;

	public function __construct($name) {
		$this->Name = $name;
		$this->Namespaces = array();
		$this->Options = array();
		return;
	}

}
