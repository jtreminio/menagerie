<?php

/*//
@package CLI
@project Menagerie
@version 1.0.0
@author Bob Majdak Jr <bob@theorangehat.net>
//*/

/*//
@namespace m
@extern
//*/

namespace m;

////////////////////////////////////////////////////////////////////////////////
// dependencies ////////////////////////////////////////////////////////////////

m_require('-lPlatform');

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/*//
@class CLI

provides an OOP interface to the command line options that were provided if
this script was run by the command line, also provides some basic interface
for doing common cliney things like ending the script and CLI detection.

the class provides query access to command line options.

if launched with:
* php script.php --option=yes

then an cli object will provide query access:
* if($cli->option === 'yes') ...

//*/

class CLI {

	/*//
	@property public argv
	stores all the arguments from the command line that were presented in the
	format of an option, like "--option=value"
	//*/

	public $argv = array();

	/*//
	@property public args
	stores a list of all the left over arguments that did not fit the valid
	option format.
	//*/

	public $args = array();

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function __construct() {
		$this->parseArguments();
		return;
	}

	public function __get($key) {
		if(array_key_exists($key,$this->argv)) return $this->argv[$key];
		else return null;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method public boolean Exists
	@arg string Key

	Returns if a value existed in the command line flags.
	//*/

	public function Exists($key) {
		return array_key_exists($key,$this->argv);
	}

	/*//
	@method public Shutdown

	shuts down the cli application.

	if given as arguments:
	* int errno
	  shuts down with that err code.

	* string errmsg
	  shuts down printing that message to the terminal.

	* int errno, string errmsg
	  shuts down with that err code and printing that message to the screen.
	//*/

	public function Shutdown() {
		$argv = func_get_args();
		$errno = 0;
		$errmsg = '';

		// this version of the function is an experiment in
		// pseudopolymorphism. e.g., will anybody care that it does not
		// really care about the order of its arguments.
		// - cli->shutdown(void)
		// - cli->shutdown(int errno)
		// - cli->shutdown(string errmsg)
		// - cli->shutdown(int errno, string errmsg)
		// - cli->shutdown(string errmsg, int errno)

		switch(count($argv)) {
			case 1: { }
			case 2: {

				foreach($argv as $arg)
					if(is_int($arg)) $errno = $arg;
					else if(is_string($arg)) $errmsg = $arg;

				break;
			}
			default: {
				// derp.
				break;
			}
		}

		//. output message.
		if($errmsg) fwrite(
			($errno)?(STDERR):(STDOUT),
			$errmsg.PHP_EOL
		);

		//. goodbye.
		exit($errno);
	}

	/*//
	@method protected void ParseArguments

	reads the arguments from the command line and generates the data sets
	that allow for query access to options.
	//*/

	protected function ParseArguments() {
		if(!array_key_exists('argv',$_SERVER)) return;
		if(!is_array($_SERVER['argv'])) return;

		$input = $_SERVER['argv'];
		unset($input[0]);

		foreach($input as $argv) {
			if(preg_match('/--([a-z0-9-]+)(?:(=)(.*))?/i',$argv,$match)) {
				if(array_key_exists(2,$match) && $match[2] == '=')
					$this->argv[$match[1]] = $match[3];
				else
					$this->argv[$match[1]] = true;
			} else {
				$this->args[] = $argv;
			}
		}

		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method static boolean Is

	returns if the script was launched from the command line interface as
	detected by the platform library.
	//*/

	static function Is() {
		return Stash::Get('platform')->CLI;
	}

	/*//
	@method static void Only

	will shut down a script if it was not launched from the CLI. useful if you
	do something weird like have cli scripts in your public directory...
	//*/

	static function Only() {
		if(!self::Is()) exit(0);
	}

}

?>