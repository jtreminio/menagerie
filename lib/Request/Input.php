<?php

namespace m\request {
	use \m as m;

	class input {

		protected $var = '';
		protected $which;
		protected $opt;

		public function __construct($which,$opt=null) {
			$this->opt = new m\object($opt,array(
				'trim'     => false,
				'pathable' => false,
				'htmlify'  => false
			));

			if(!is_array($which)) {
				$this->which = strtolower($which);
				switch($this->which) {
					case 'cookie':  { $this->var =& $_COOKIE;  break; }
					case 'files':   { $this->var =& $_FILES;   break; }
					case 'get':     { $this->var =& $_GET;     break; }
					case 'global':  { $this->var =& $_GLOBALS; break; }
					case 'post':    { $this->var =& $_POST;    break; }
					case 'request': { $this->var =& $_REQUEST; break; }
					case 'server':  { $this->var =& $_SERVER;  break; }
					case 'session': { $this->var =& $_SESSION; break; }
					default:        { $this->var =& $_REQUEST; break; }
				}
			} else {
				$this->which = 'custom';
				$this->var = $which;
			}

			return;
		}

		public function __toString() {
			return json_encode($this->var);
		}

		public function __set($key,$value) {
			$return = null;

			switch($this->which) {
				case 'custom':  { }
				case 'global':  { }
				case 'session': { $this->var[$key] = $return = $value; break; }
			}

			return $return;
		}

		public function __unset($key) {

			switch($this->which) {
				case 'custom':  { }
				case 'global':  { }
				case 'session': { if($this->exists($key)) unset($this->var[$key]); break; }
			}

			return;
		}

		public function __get($key) {
			if($this->exists($key)) return $this->filter($this->var[$key]);
			else return null;
		}

		public function exists($key) {
			if(is_array($this->var) && array_key_exists($key,$this->var)) return true;
			else	return false;
		}

		public function itemize() {
			$list = func_get_args();
			$output = array();

			foreach($list as $key)
				if(!is_array($key))
					$output[$key] = $this->__get($key);
				else foreach($key as $kkey)
					$output[$kkey] = $this->__get($kkey);

			return $output;
		}

		protected function filter($input) {
			if($this->opt->trim) $input = trim($input);
			if($this->opt->pathable) $input = m\request::pathable($input,true);
			if($this->opt->htmlify) $input = htmlentities($input);
			return $input;
		}

		//////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////

		public function GetAsList($key,$mainbreak="\n",$addbreak=false) {
		/*//
		This will assume the input for the specific field was to be a list of
		items that was input with each time on its own line. It will return
		an array broken up by the new lines, trimming whitespace off both ends
		of the input data and disregarding any blank lines.

		the mainbreak parameter is the main delimiter that it will use to
		break up the data. by default it is new line. but you can change it to
		comma or tab or whatever you want.

		the addbreak parameter will also allow what you specify to break a
		list up too, like if you want both tabs and commas or something. it
		should be a valid preg character group. example: "\\t," will allow
		tabs and commas in addition to the mainbreak.
		//*/

			// unless there was no data field.
			if(!$this->Exists($key)) return false;
			else $input = $this->{$key};

			// if additional delimits was specified turn them into newlines.
			if($addbreak)
			$input = preg_replace("/[{$addbreak}]+/",$mainbreak,$input);

			// break up the data into the list.
			$input = explode($mainbreak,$input);

			// shore up the data and disregard blanks.
			$output = array();
			foreach($input as $item) {
				$item = trim($item);

				if(strlen($item)) $output[] = $item;
				else continue;
			}

			return $output;
		}

	}

}

?>