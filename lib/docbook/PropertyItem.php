<?php

namespace m\Docbook;
use \m as m;

class PropertyItem {

	public $Name;
	public $Access;
	public $Type;
	public $Text;

	public function __construct($block) {

		$this->Name = $block->Name;
		$this->Text = $block->Text;

		$this->Access = $block->Tags->Property->Access;
		$this->Type = $block->Tags->Property->Type;

		return;
	}

}
