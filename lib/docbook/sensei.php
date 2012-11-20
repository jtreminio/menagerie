<?php

namespace m\Docbook;
use \m as m;

/*//
@class Sensei

documentation generator class for my invented documentation block syntax used
in this project. why didn't i just use phpdoc? i do what i want!
//*/
class Sensei {

	protected $CurrentFile;
	protected $OutputDirectory;

	public $Projects;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function __construct() {
		$this->Projects = array();
		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

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
	@method public void Document

	generate documents for the file currently pointed at.
	//*/

	public function Document() {
		$blocks = Docblock::GetListFromFile($this->CurrentFile);

		$package   =
		$namespace =
		$class     =
		$null      = null;

		foreach($blocks as $block) {
			switch($block->Type) {

				case 'Package':
					$package = $this->HandlePackage($block);
					break;

				case 'Namespace':
					$namespace = $this->HandleNamespace($package,$block);
					break;

				case 'Class':
					$class = $this->HandleClass($namespace,$block);
					break;

				case 'Property':
					$this->HandleProperty($class,$block);
					break;

				case 'Method':
					$this->HandleMethod($class,$block);
					break;

				case 'Option':
					$this->HandleOption($package,$block);
					break;

			}
		}

		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method protected void HandlePackage
	@arg m\Docbook\Docblock Block
	@flags internal

	will create project and package entries in the documentation index when
	package blocks are encountered.
	//*/

	protected function HandlePackage($block) {
		$tags = $block->GetTagMap();

		if(!property_exists($tags,'Project'))
		throw new \Exception('package needs a project');

		// create or get the project this belongs to.
		if(!array_key_exists($tags->Project->Value,$this->Projects))
		$this->Projects[$tags->Project->Value] = new ProjectItem($tags->Project->Value);

			$project = $this->Projects[$tags->Project->Value];

		// create or get the package this package is.
		if(!array_key_exists($tags->Package->Value,$project->Packages))
		$project->Packages[$tags->Package->Value] = new PackageItem($tags->Package->Value);

			$package = $project->Packages[$tags->Package->Value];

		return $package;
	}

	/*//
	@method protected void HandleNamespace
	@arg m\Docbook\PackageItem Package
	@arg m\Docbook\Docblock Block
	@flags internal

	will create a namespace entry in the given package index when the namespace
	blocks are encountered.
	//*/

	protected function HandleNamespace($package,$block) {
		if(!$package)
		throw new \Exception('namespace hit with no prior package block');

		$tags = $block->GetTagMap();

		if(!array_key_exists($tags->Namespace->Value,$package->Namespaces))
		$package->Namespaces[$tags->Namespace->Value] = new NamespaceItem($tags->Namespace->Value);

			$namespace = $package->Namespaces[$tags->Namespace->Value];

		return $namespace;
	}

	/*//
	@method protected void HandleOption
	@arg m\Docbook\PackageItem Package
	@arg m\Docbook\Docblock Block
	@flags internal

	will create an option entry in the given package index when the option
	blocks are encountered.
	//*/

	protected function HandleOption($package,$block) {
		if(!$package)
		throw new \Exception('option hit with no prior package block');

		$package->Options[$block->Tags->Option->Name] = new OptionItem($block);

		return;
	}

	/*//
	@method protected void HandleClass
	@arg m\Docbook\ClassItem Class
	@arg m\Docbook\Docblock Block

	will create a class entry in the given namespace index when class blocks
	are encountered.
	//*/

	protected function HandleClass($ns,$block) {
		if(!$ns)
		throw new \Exception('class hit with no prior namespace block');

		$ns->Classes[$block->Tags->Class->Name] = $class = new ClassItem($block);

		return $class;
	}

	/*//
	@method protected void HandleProperty
	@arg m\Docbook\ClassItem Class
	@arg m\Docbook\Docblock Block

	will create a property entry in the given class indes when method blocks are
	encountered.
	//*/

	protected function HandleProperty($class,$block) {

		if(!$class)
		throw new \Exception('property hit with no prior class block');

		$class->Properties[$block->Tags->Property->Name] = new PropertyItem($block);

		return;
	}

	/*//
	@method protected void HandleMethod
	@arg m\Docbook\ClassItem Class
	@arg m\Docbook\Docblock Block

	will create a method entry in the given class indes when method blocks are
	encountered.
	//*/

	protected function HandleMethod($class,$block) {

		if(!$class)
		throw new \Exception('method hit with no prior class block');

		$class->Methods[$block->Tags->Method->Name] = new MethodItem($block);

		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method public void WriteMarkupDocument

	this will write a really nice file documenting the entire project using
	markup syntax. protip: make your comments markup.
	//*/

	public function WriteMarkupDocument() {

		ob_start();
		$this->PrintMarkupDocumentHeader();
		echo ob_get_clean();

		return;
	}

	protected function PrintMarkupDocumentHeader() {

		return;
	}

}