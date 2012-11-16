<?php

/*//
@namespace m\Docbook
//*/

namespace m\Docbook;

/*//
@class Docblock
//*/

class Docblock {

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method static array GetListFromFile
	@arg string Filename

	return an array of Docblock objects from a file on disk.
	//*/

	static function GetListFromFile($filename) {

		if(!is_file($filename) || !is_readable($filename))
		throw new \Exception("file {$filename} not found or not readable.");

		return self::GetListFromInput(file_get_contents($filename));

	}

	/*//
	@method static array GetListFromInput
	@arg string InputData

	return an array of Docblock objects from input text data.
	//*/

	static function GetListFromInput($input) {

		preg_match_all(
			'/\/\*\/\/(.+?)\/\/\*\//ms',
			$input,
			$match
		);

		$blocks = array();
		foreach($match[1] as $text) {
			$blocks[] = new self($text);
		}

		return $blocks;
	}

}
