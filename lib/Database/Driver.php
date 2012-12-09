<?php

namespace m\database;

abstract class Driver {

	protected $Name;

	public $QueryCount = 0;
	public $QueryTime = 0;
	public $ConnectTime = 0;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function __construct($name) {
		$this->Name = $name;
		return;
	}

	public function __destruct() {
		$this->Disconnect();
		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	protected function ThrowError($message) {
		throw new \Exception("{$message} [{$this->Name}]");
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function GetConnectTime() {
		return sprintf('%.3fsec',$this->ConnectTime);
	}

	public function GetQueryTime() {
		return sprintf('%.3fsec',$this->QueryTime);
	}

	public function GetQueryCount() {
		return $this->QueryCount;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	abstract public function Connect($config);
	abstract public function Disconnect();
	abstract public function Escape($input);
	abstract public function Query($sql);

	// highly recommended additional methods:
	// public function id(void); // return the last inserted id.

}


?>