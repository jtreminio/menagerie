<?php

/*//
@namespace m\Docbook
//*/

namespace m\Docbook;

/*//
@class Docblock
//*/

class Docblock {

	protected $Raw;

	public $Type = 'unknown';
	public $Tags;
	public $Text;

	public function __construct($text) {
		$this->Raw = $text;

		$this->PrepareInputText();
		$this->ParseTagsAndText();

		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method protected void PrepareInputText
	@flags internal

	will take a docblock of text and do cleaning on it to prepare it for later
	parsing.
	//*/

	protected function PrepareInputText() {

		// shore up the lines.
		$this->Raw = preg_replace('/^\h+|\h+$/ms','',$this->Raw);

		return;
	}

	/*//
	@method protected void ParseTags
	@flags internal

	will parse all the @tags
	//*/

	protected function ParseTagsAndText() {

		preg_match_all('/^@([a-zA-Z0-9-]+) (.+?)$/ms',$this->Raw,$match);

		// parse all the tags in this block.
		$this->Tags = array();
		foreach($match[1] as $tagiter => $tagname) {
			$tclass = $this->GetTagClass($tagname);
			$this->Tags[] = new $tclass($tagname,$match[2][$tagiter]);
		}

		// find the "top level tags" and assign the type of this docblock
		// based on them.
		foreach($this->Tags as $tag) {
			switch($tag->Tag) {
				case 'namespace':
				case 'class':
				case 'method':
				case 'property':
				case 'function':
				case 'option':
					$this->Type = $tag->Tag;
					$this->Name = $tag->Name;
					break;
			}
		}

		return;
	}

	protected function GetTagClass($tagname) {
		$class = sprintf(
			'\m\Docbook\Tags\%sTag',
			ucfirst(strtolower($tagname))
		);

		if(!class_exists($class,true))
		return '\m\Docbook\Tags\GenericTag';

		else
		return $class;
	}

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
