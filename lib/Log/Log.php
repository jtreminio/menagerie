<?php

namespace m;

class Log {

	const TEXT = 1;
	const JSON = 2;

	/*//
	@property protected resource FP
	a flie pointer to the current log file.
	//*/

	protected $File;

	/*//
	@property protected string Fielename
	the name of the current file.
	//*/

	protected $Filename;

	/*//
	@property protected array Events
	the events this log should be listening for.
	//*/

	protected $Events = [];

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function __construct($opt) {
		$opt = new Object($opt,[
			'Filename' => null,
			'Events'   => null,
			'Format'   => self::TEXT
		]);

		// make sure we can write where we need.
		if(!file_exists($opt->Filename) && !is_writable(dirname($opt->Filename)))
		throw new \Exception("unable to create or write {$opt->Filename}");

		// make sure we have things to write.
		if(!is_array($opt->Events) || !count($opt->Events))
		throw new \Exception("no usable logging events.");

		// make sure we the system was able to actually open it.
		if(!$this->OpenFile($opt->Filename))
		throw new \Exception("unable to open {$opt->Filename}.");

		foreach($opt->Events as $event)
		$this->AddEvent($event);

		$this->Format = $opt->Format;

		return;
	}

	public function __destruct() {
		$this->CloseFile();
		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method protected void AddEvent
	@arg string Event

	create a ki event for this log object to capture.
	//*/

	protected function AddEvent($event) {
		Ki::Queue($event,array($this,'Write'),true);
		return;
	}

	/*//
	@method protected boolean OpenFile

	attempt to open the file chosen and return the success or fali.
	//*/

	protected function OpenFile($filename) {
		$this->File = fopen($filename,'a');

		if($this->File) {
			$this->Filename = $filename;
			return true;
		} else {
			$this->Filename = null;
			return false;
		}
	}

	/*//
	@method protected void CloseFile

	close the file currently open by this object.
	//*/

	protected function CloseFile() {
		if(!$this->File) return;

		fclose($this->File);
		$this->File = false;

		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method public void Write
	@arg string LogType
	@arg string Message

	prepare and write a log message to the file.
	//*/

	public function Write($ki,$msg) {
		if(!$this->File) return;

		switch($this->Format) {
			case self::TEXT: { $string = $this->PrepareText($ki,$msg); break; }
			case self::JSON: { $string = $this->PrepareJSON($ki,$msg); break; }
		}

		if(is_string($string) && $string) {
			flock($this->File,LOCK_EX);
			fwrite($this->File,$string);
			flock($this->File,LOCK_UN);
		}

		return;
	}

	/*//
	@method protected string PrepareText
	@arg string LogType
	@arg string Message

	prepare a string for the log file in the string text format.
	//*/

	protected function PrepareText($ki,$msg) {
		return sprintf(
			'[%s] [%s] [%s] %s%s',
			date('Y-m-d H:i:s'),
			session_id(),
			$ki,
			trim($msg),
			PHP_EOL
		);
	}

	/*//
	@method protected string PrepareJSON
	@arg string LogType
	@arg string Message

	prepare a string for the log file in JSON format.
	//*/

	protected function PrepareJSON($ki,$msg) {
		return trim(json_encode([
			'Date'    => date('Y-m-d H:i:s'),
			'Session' => session_id(),
			'Log'     => $ki,
			'Message' => trim($msg)
		])).PHP_EOL;
	}

}
