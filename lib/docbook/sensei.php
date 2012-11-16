<?php

namespace m\Docbook;

/*//
@class Sensei

documentation generator class for my invented documentation block syntax used
in this project. why didn't i just use phpdoc? i do what i want!
//*/
class Sensei {

	protected $CurrentFile;
	protected $OutputDirectory;

	/*//
	@method public void SetOutputDirectory
	@arg string Directory

	set where generated documentation files should go.
	//*/

	public function SetOutputDirectory($dir) {

		if(!is_dir($dir) || !is_writable($dir))
		throw new \Exception("directory {$dir} not found or is not writable.");

		$this->OutputDirectory = $dir;

		return;
	}

	/*//
	@method public void SetFilename
	@arg string Filename

	set the filename to work on next.
	//*/

	public function SetFilename($file) {

		if(!is_file($file) || !is_readable($file))
		throw new \Exception("file ({$file}) not found or not readable.");

		$this->CurrentFile = $file;

		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method public void Documentize

	generate documents for the file currently pointed at.
	//*/

	public function Document() {
		$blocks = Docblock::GetListFromFile($this->CurrentFile);

		print_r($blocks);

		return;
	}

}