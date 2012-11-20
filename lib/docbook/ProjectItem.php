<?php

namespace m\Docbook;

class ProjectItem {

	public $Name;
	public $Packages;

	public function __construct($name) {
		$this->Name = $name;
		$this->Packages = array();
		return;
	}

}
