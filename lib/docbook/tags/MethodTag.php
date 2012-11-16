<?php

namespace m\Docbook\Tags;

class MethodTag extends GenericTag {

	public $Access;
	public $ReturnType;
	public $Name;

	public function Parse() {

		if(!preg_match(
			'/^(public|protected|private|static) ([^\h]+) (.+?)$/',
			$this->TagContent,
			$match
		)) throw new Exception(sprintf(
			'@method access return-type name',
			$this
		));

		$this->Access = $match[1];
		$this->ReturnType = $match[2];
		$this->Name = $match[3];

		return;
	}


}
