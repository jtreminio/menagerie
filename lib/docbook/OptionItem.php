<?php

namespace m\Docbook;
use \m as m;

class OptionItem {

	public $Name;
	public $Type;
	public $DefaultValue;
	public $Text;

	public function __construct($block) {

		$this->Name = $block->Name;
		$this->Text = $block->Text;

		$this->Type = $block->Tags->Option->Type;
		$this->DefaultValue = $block->Tags->Option->DefaultValue;

		return;
	}

}
