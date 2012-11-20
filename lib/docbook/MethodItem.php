<?php

namespace m\Docbook;
use \m as m;

class MethodItem {

	public $Name;
	public $Access;
	public $ReturnType;
	public $ArgList;
	public $Text;

	public function __construct($block) {

		$this->Name = $block->Name;
		$this->Text = $block->Text;

		$this->Access = $block->Tags->Method->Access;
		$this->ReturnType = $block->Tags->Method->ReturnType;

		if(property_exists($block->Tags,'ArgList'))
			$this->ArgList = $block->Tags->ArgList;
		else $this->ArgList = array();

		return;
	}

}
