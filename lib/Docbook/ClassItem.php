<?php

namespace m\Docbook;
use \m as m;

class ClassItem {

	public $Name;
	public $Namespace;
	public $Text;
	public $Properties;
	public $Methods;

	public function __construct($namespace,$block) {

		$this->Name = $block->Name;
		$this->Namespace = $namespace->Name;
		$this->Text = $block->Text;

		$this->Properties = array();
		$this->Methods = array();

		return;
	}

}
