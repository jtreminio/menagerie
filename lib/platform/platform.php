<?php

namespace m {
	use \m as m;

	class platform {

		public $api = false;
		public $bin = false;
		public $cli = false;

		public function __construct() {
			$this->detect();
			return;
		}

		protected function detect() {

			// attempt to define as cli if the app did not already directly
			// set the platform constant.
			if(defined('STDIN'))
			m_define('m\platform','cli');

			// supported application types. libraries may check these to
			// determine what they should try to do by default depending on
			// the current platform.
			if(defined('m\platform'))
			switch(m\platform) {
				case 'api': { }
				case 'bin': { }
				case 'cli': {
					$this->{m\platform} = true;
					$this->type = m\platform;
					break;
				}
				default: {
					$this->type = 'generic';
					break;
				}
			}

			return;
		}

	}

}

namespace {
	m\ki::queue('m-setup',function(){
		m\stash::set('platform',new m\platform);
		return;
	});
}
