<?php

namespace m\Docbook;

class ProjectItem {

	public $Name;
	public $Text;
	public $Packages;

	public function __construct($name) {
		$this->Name = $name;
		$this->Packages = array();
		return;
	}

}
