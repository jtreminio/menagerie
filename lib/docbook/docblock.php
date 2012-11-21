<?php

/*//
@namespace m\Docbook
//*/

namespace m\Docbook;
use \m as m;

/*//
@class Docblock
//*/

class Docblock {

	protected $Raw;

	public $Type;
	public $Name;
	public $Tags;
	public $Text;

	public function __construct($text) {
		$this->Raw = $text;

		$this->PrepareInputText();
		$this->ParseTags();
		$this->ParseText();

		unset($this->Raw);

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

	protected function ParseTags() {

		preg_match_all('/^@([a-zA-Z0-9-]+) (.+?)$/ms',$this->Raw,$match);

		// parse all the tags in this block.
		$this->Tags = array();
		foreach($match[1] as $tagiter => $tagname) {
			$tagname = ucwords(strtolower($tagname));
			$tclass = $this->GetTagClass($tagname);
			$this->Tags[] = new $tclass($tagname,$match[2][$tagiter]);
		}

		// find the "top level tags" and assign the type of this docblock
		// based on them.
		foreach($this->Tags as $tag) {
			switch($tag->Tag) {
				case 'Package':
				case 'Namespace':
				case 'Class':
				case 'Method':
				case 'Property':
				case 'Function':
				case 'Option':
					$this->Type = $tag->Tag;
					$this->Name = (property_exists($tag,'Name'))?
						($tag->Name):
						($tag->TagContent);
					break;
			}
		}

		// if this docblock had no defined toplevel tags in it then we will
		// just use the first tag in the block.
		if(count($this->Tags)) {
			if(!$this->Type && !$this->Name) {
				reset($this->Tags);
				$this->Type = current($this->Tags)->Tag;
				$this->Name = current($this->Tags)->TagContent;
			}
		}

		// although if this block did not even have any tags in it then where
		// do you go my lovely?
		else {
			$this->Type = 'generic';
			$this->Name = 'comment';
		}

		$this->Tags = $this->GetTagMap();

		return;
	}

	/*//
	@method protected void ParseText
	@flags internal

	will pull the plain text description out of the comment. currently not
	planning on reformatting it any, and saying that you should use markdown
	to format it.
	//*/

	protected function ParseText() {

		$input = trim(preg_replace('/^@.+?$/ms','',$this->Raw));

		// convert double new lines into something unique.
		$input = preg_replace('/(?:\r?\n){2}/ms','<sensei nl />',$input);

		// then convert all new lines that appear to continue a paragraph into
		// nothings.
		$input = preg_replace('/\r?\n([a-zA-Z0-9])/',' \1',$input);

		// then turn the double new lines back.
		$input = preg_replace('/<sensei nl \/>/ms',PHP_EOL.PHP_EOL,$input);

		// give lists in comments more kick.
		$input = preg_replace('/^([\+\*\-])/ms',"\t\\1",$input);


		$this->Text = $input;
		return;
	}

	/*//
	@method function GetTagClass
	@arg string TagName
	@flags internal

	will return the qualified name of the class to use to create an tag
	object. if no special class is found for a tag type then GenericTag is
	returned.
	//*/

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
	@method public array GetTagMap

	returns a map of the tags as an associative array, taking into account tags
	which can occur more than once and subarraying them, etc.
	//*/

	public function GetTagMap() {

		$map = array();

		foreach($this->Tags as $tag) switch($tag->Tag) {
			case 'Arg':
				if(!array_key_exists('Args',$map)) $map['ArgList'] = array();
				$map['ArgList'][] = $tag;
				break;
			default:
				$map[$tag->Tag] = $tag;
				break;
		}

		return new m\Object($map);
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
