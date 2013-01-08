<?php

/*//
@package Docbook
@project Menagerie
@version 1.0.0
@author Bob Majdak Jr <bob@theorangehat.net>
//*/

/*//
@namespace m\Docbook
//*/
namespace m\Docbook;
use \m as m;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

require_once(m_repath_fs(sprintf(
	'%s/share/php-markdown-extra/markdown.php',
	m\root
)));

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

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
		$project->Packages[$tags->Package->Value] = new PackageItem($block);

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

		$ns->Classes[$block->Tags->Class->Name] = $class = new ClassItem($ns,$block);

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
	@method public void Sort

	will sort the entire documentation tree alphabetical.
	//*/

	public function Sort() {

		// sort projects.
		ksort($this->Projects);

		// sort packages.
		foreach($this->Projects as $proj) {
			ksort($proj->Packages);

		// sort package namespaces and options.
		foreach($proj->Packages as $pkg) {
			ksort($pkg->Namespaces);
			ksort($pkg->Options);

		// sort namespace classes and functions.
		foreach($pkg->Namespaces as $ns) {
			ksort($ns->Classes);
			ksort($ns->Functions);
			ksort($ns->Constants);

		// sort class methods and properties.
		foreach($ns->Classes as $class) {
			ksort($class->Methods);
			ksort($class->Properties);

		} } } }

		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function PrintTextHeaderBlock($text,$char='=') {

		$string = sprintf(
			'%s %s ',
			str_repeat($char,2),
			$text
		);

		// fill the line.
		$string .= str_repeat($char,(80-strlen($string))).PHP_EOL.PHP_EOL;

		// overbar.
		$string = str_repeat($char,80).PHP_EOL.$string;

		echo $string;
	}

	public function PrintTextHeaderBar($text,$char='=') {

		$string = sprintf(
			'%s %s ',
			str_repeat($char,2),
			$text
		);

		// fill the line.
		$string .= str_repeat($char,(80-strlen($string))).PHP_EOL.PHP_EOL;

		echo $string;
	}

	/*//
	@method public void WriteMarkdownDocument

	this will write a really nice file documenting the entire project. the
	method names are currently misleading as i decided to drop markdown.
	//*/

	public function WriteMarkdownDocument() {

		foreach($this->Projects as $project) {
			ob_start();
			$this->PrintMarkdownDocumentHeader($project);
			$this->PrintMarkdownDocumentOverview($project);
			$this->PrintMarkdownDocumentPackageView($project);
			$text = ob_get_clean();

			// render text version
			$filename = sprintf(
				'%s/%s.txt',
				$this->OutputDirectory,
				strtolower($project->Name)
			);
			file_put_contents($filename,$text);

			// render html version
			/*
			$filename = sprintf(
				'%s/%s.html',
				$this->OutputDirectory,
				strtolower($project->Name)
			);
			file_put_contents($filename,Markdown($text));
			*/

		}

		return;
	}

	protected function PrintMarkdownDocumentHeader($project) {

		$this->PrintTextHeaderBlock("{$project->Name} API Documentation",'#');

		m_printfln('* Last Generated: %s',date('l F jS Y, H:i T (U)'));
		m_printfln('* By: %s',trim(`whoami`));
		m_printfln('');

		return;
	}

	protected function PrintMarkdownDocumentOverview($project) {

		$this->PrintTextHeaderBlock('Package Listing');

		foreach($project->Packages as $package) {
			m_printfln('* %s',$package->Name);
		}

		m_printfln('');
		m_printfln('');
		m_printfln('');

		return;
	}

	protected function PrintMarkdownDocumentPackageView($project) {

		foreach($project->Packages as $package) {
			$this->PrintTextHeaderBlock("Package: {$package->Name}",'#');

			if($package->Text) m_printfln($package->Text);
			else m_printfln('**No Package Description**');
			m_printfln('');

			$this->PrintMarkdownDocumentPackageOptions($package);
			$this->PrintMarkdownDocumentPackageNamespaces($package);
		}

		m_printfln('');

		return;
	}

	protected function PrintMarkdownDocumentPackageOptions($package) {

		$this->PrintTextHeaderBlock('Options','/');

		if(!count($package->Options)) {
			m_printfln('None');
			m_printfln('');
			return;
		}

		foreach($package->Options as $option) {
			$this->PrintTextHeaderBlock($option->Name,'-');

			m_printfln(' - Type: %s',$option->Type);
			m_printfln(' - Default: %s',(($option->DefaultValue)?($option->DefaultValue):('_None_')));
			m_printfln('');
			m_printfln('%s',wordwrap($option->Text,80));
			m_printfln('');
			m_printfln('');
		}

		m_printfln('');


		return;
	}

	protected function PrintMarkdownDocumentPackageNamespaces($package) {

		$this->PrintTextHeaderBlock('Namespaces Provided','/');
		foreach($package->Namespaces as $namespace)
			m_printfln('* %s',$namespace->Name);

		m_printfln('');
		$this->PrintTextHeaderBlock('Classes','/');
		foreach($package->Namespaces as $namespace)
			$this->PrintMarkdownDocumentNamespaceClasses($namespace);

		m_printfln('');

		return;
	}

	protected function PrintMarkdownDocumentNamespaceClasses($ns) {

		if(!count($ns->Classes)) {
			m_printfln('None');
			m_printfln('');
			return;
		}

		foreach($ns->Classes as $class) {
			$this->PrintTextHeaderBlock("{$ns->Name}\\{$class->Name}",'=');

			m_printfln('%s',(($class->Text)?
				(wordwrap($class->Text,80)):
				('*No Class Description*'))
			);
			m_printfln('');

			$this->PrintMarkdownDocumentClassProperties($class);
			$this->PrintMarkdownDocumentClassMethods($class);

		}

		m_printfln('');

		return;
	}

	protected function PrintMarkdownDocumentClassProperties($class) {

		$this->PrintTextHeaderBar('Properties','=');

		if(!count($class->Properties)) {
			m_printfln('None');
			m_printfln('');
			return;
		}

		foreach($class->Properties as $property) {
			$this->PrintTextHeaderBlock("{$class->Namespace}\\{$class->Name}::{$property->Name}",'-');

			m_printfln(' - Access: %s',$property->Access);
			m_printfln(' - Type: %s',$property->Type);
			m_printfln('');

			if($property->Text) {
				m_printfln('%s',wordwrap($property->Text,80));
				m_printfln('');
			}

			m_printfln('');
		}

		return;
	}

	protected function PrintMarkdownDocumentClassMethods($class) {

		$this->PrintTextHeaderBar('Methods','=');

		if(!count($class->Methods)) {
			m_printfln('None');
			m_printfln('');
			return;
		}

		foreach($class->Methods as $method) {

			$this->PrintTextHeaderBlock("{$class->Namespace}\\{$class->Name}::{$method->Name}",'-');

			// if the method has no arguments print a one line prototype of
			// the method.
			if(!count($method->ArgList)) {
				m_printfln(
					"\t%s %s %s(void);",
					$method->Access,
					$method->ReturnType,
					$method->Name
				);
			}

			// if the method does have arguments then print a multiline
			// prototype of the method.
			else {
				m_printfln(
					"\t%s %s %s(",
					$method->Access,
					$method->ReturnType,
					$method->Name
				);

				$argstring = array();
				foreach($method->ArgList as $arg)
					$argstring[] = sprintf(
						"\t\t%s %s%s",
						$arg->Type,
						$arg->Name,
						(($arg->DefaultValue)?(" = {$arg->DefaultValue}"):(''))
					);

				m_printfln('%s',join(','.PHP_EOL,$argstring));
				m_printfln("\t);");

			}
			m_printfln('');


			if($method->Text) {
				m_printfln('%s',wordwrap($method->Text,80));
				m_printfln('');
			}

			m_printfln('');
		}

		return;
	}

}