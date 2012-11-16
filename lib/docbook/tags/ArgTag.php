<?php

namespace m\Docbook\Tags;

class ArgTag extends GenericTag {

	public $Type;
	public $Name;
	public $DefaultValue;

	public function Parse() {

		if(!preg_match(
			'/^([^\h]+) (.+?)(?: default (.+?))?$/',
			$this->TagContent,
			$match
		)) throw new Exception(sprintf(
			'@arg type name[ default value]',
			$this
		));

		$this->Type = $match[1];
		$this->Name = $match[2];
		$this->DefaultValue = (array_key_exists(3,$match))?($match[3]):(null);

		return;
	}


}
