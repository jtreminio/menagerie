<?php

namespace m {

	class message {
		protected $ID;
		protected $Log;

		public function __construct($id) {
			$this->ID = $id;

			// get the data from the session if it exists.
			if(array_key_exists("m-message-{$this->ID}",$_SESSION))
			$this->Log = json_decode($_SESSION["m-message-{$this->ID}"]);

			if(is_object($this->Log)) $this->Log = (array)$this->Log;
			if(!is_array($this->Log)) $this->Log = array();
			return;
		}

		public function __destruct() {
			if(session_id())
			$this->store();
		}

		protected function store() {
			// pack up the left overs for next time.
			$_SESSION["m-message-{$this->ID}"] = json_encode($this->Log);
			return;
		}

		//////////////////////////////////////////////////////////////////////
		// queue manage //////////////////////////////////////////////////////

		public function add($text,$type='notice') {

			$opt = new object(array(),array(
				'Type' => $type,
				'Text' => $text,
				'ID'   => md5(microtime().rand(0,9001))
			));

			$this->Log[$opt->ID] = $opt;
			return $opt->ID;
		}

		public function remove($id) {
			if(array_key_exists($id,$this->Log))
			unset($this->Log[$id]);
		}

		//////////////////////////////////////////////////////////////////////
		// queue use /////////////////////////////////////////////////////////

		public function each($callback) {
			if(!is_callable($callback))
			throw new \Exception('This is not callable dude.');

			foreach($this->Log as $key => $log) {
				call_user_func($callback,$log);
				unset($this->Log[$key]);
			}

			$this->store();
			return;
		}

	}

}

namespace {
	m\ki::queue('m-setup',function(){
		m\stash::set('message',new m\message('m'));

		// when a browser is redirected from the page we need to make sure
		// we shut down in a way that the remaining messages get stored to
		// the session.
		m\ki::queue('m-request-redirect',function(){
			m\stash::destroy('message');
		});

		return;
	});
}

?>