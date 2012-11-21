<?php

namespace m\Docbook;

class PackageItem {

	public $Name;
	public $Text;
	public $Namespaces;
	public $Options;

	public function __construct($block) {
		$this->Name = $block->Name;
		$this->Text = $block->Text;
		$this->Namespaces = array();
		$this->Options = array();
		return;
	}

}
