<?php

namespace m {

	class log {

		public $Print = 'cli';
		public $Filename = null;

		public function __construct() {
			$this->Filename = sprintf('%s/menagerie.log',dirname(__FILE__));

			if($this->Filename) {
				if(file_exists($this->Filename) && is_writable($this->Filename))
				$this->openLogFile();
			}

			return;
		}

		public function add($entry) {
			$entry = new object($entry,array(
				'Time'      => gmdate('U'),
				'Timestamp' => gmdate('Y-m-d H:i:s'),
				'Level'     => 'message',
				'From'      => 'application',
				'Message'   => ''
			));

			if(($this->Print == 'cli' && m_defined_as('m\platform','cli')) || m_defined_true('m\platform'))
			m_printfln(
				'[%s] [%s] [%s] %s',
				$entry->Timestamp,
				$entry->Level,
				$entry->From,
				$entry->Message
			);

			return;
		}

		protected function openLogFile() {

		}

	}

}

namespace {
	m\ki::queue('m-setup',function(){
		m\stash::set('log',new m\log);
		return;
	});
}

?>