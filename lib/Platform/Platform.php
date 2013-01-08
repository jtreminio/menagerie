<?php

namespace m {
	use \m as m;

	class platform {
	/**
	 * this class's job is to detect what the output platform the application
	 * is running on if it had not been explicitly set by the application
	 * prior to loading the framework.
	 *
	 * an instance of this should be managed by the framework for you to
	 * access in the stash. should almost never really have a reason to use
	 * this class yourself.
	**/

		public $api = false;
		public $bin = false;
		public $cli = false;

		public $type = null;

		public function __construct() {
			$this->detect();
			return;
		}

		protected function detect() {

			// attempt to define as cli if the app did not already directly
			// set the platform constant.
			if(defined('STDIN'))
			m_define('m\platform','cli');

			$this->type = 'generic';

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
			}

			else
			define('m\platform',$this->type);

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
