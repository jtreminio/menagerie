<?php

namespace m\Docbook;
use \m as m;

class ClassItem {

	public $Name;
	public $Text;
	public $Properties;
	public $Methods;

	public function __construct($block) {

		$this->Name = $block->Name;
		$this->Text = $block->Text;

		$this->Properties = array();
		$this->Methods = array();

		return;
	}

}
