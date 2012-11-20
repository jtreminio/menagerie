<?php

namespace m\Docbook;

class NamespaceItem {

	public $Name;
	public $Classes;
	public $Functions;
	public $Constants;

	public function __construct($name) {
		$this->Name = $name;
		$this->Classes = array();
		$this->Functions = array();
		$this->Constants = array();
		return;
	}

}
