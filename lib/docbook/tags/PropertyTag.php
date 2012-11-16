<?php

namespace m\Docbook\Tags;

class PropertyTag extends GenericTag {

	public $Access;
	public $Type;
	public $Name;
	public $DefaultValue;

	public function Parse() {

		if(!preg_match(
			'/^(public|protected|private|static) ([^\h]+) (.+?)(?: default (.+?))?$/',
			$this->TagContent,
			$match
		)) throw new Exception(
			"@property access type name[ default value]",
			$this
		);

		$this->Access = $match[1];
		$this->Type = $match[2];
		$this->Name = $match[3];
		$this->DefaultValue = (array_key_exists(4,$match))?($match[4]):(null);

		return;
	}


}
