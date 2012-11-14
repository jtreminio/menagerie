<?php

namespace m {
	use \m as m;

	// depends on the platform library for max automation.
	m_require('-lplatform');

	class surface {

		public $Theme;
		public $Style;
		public $Print;

		private $Storage = array();
		private $Capturing = false;

		public function __construct($input=null) {
			$opt = new m\object($input,array(
				'Theme' => option::get('surface-theme'),
				'Style' => option::get('surface-style'),
				'Print' => true,
				'Capture' => false
			));

			$this->Theme = $opt->Theme;
			$this->Style = $opt->Style;
			$this->Print = $opt->Print;

			if($opt->Capture)
			$this->CaptureStart();

			return;
		}

		public function __destruct() {
			if($this->Capturing) {
				$this->render();
			}

			return;
		}

		public function CaptureStart() {
			if($this->Capturing) return false;

			ob_start();
			$this->Capturing = true;

			return true;
		}

		public function CaptureStop($append=true) {
			if(!$this->Capturing) return false;

			$output = ob_get_clean();
			$this->Capturing = false;

			if($append)
			$this->append('stdout',$output);

			return true;
		}

		public function Render() {
			$themepath = $this->getThemePath();
			if(!$themepath) throw new \Exception("theme {$this->Theme} not found");

			//. get stdout.
			if($this->Capturing)
			$this->CaptureStop(true);

			//. do some special case stuff now that it is render time.
			$this->doSpecial();

			//. run theme.
			if($this->Print) {
				m_require($themepath,array('surface'=>$this));
				return;
			} else {
				ob_start();
				m_require($themepath,array('surface'=>$this));
				return ob_get_clean();
			}

		}

		private function DoSpecial() {

			if(option::get('surface-brand-title')) {
				if($this->has('page-title'))
					$this->append('page-title',sprintf(
						' - %s',
						option::get('app-name')
					));
				else
					$this->set('page-title',trim(sprintf(
						'%s - %s',
						option::get('app-name'),
						option::get('app-description-short')
					),'- '));
			}

			if(!$this->has('page-description'))
				$this->set(
					'page-description',
					option::get('app-description-long')
				);

			return;
		}

		private function GetThemePath() {
			// figure out the file system path for the theme file that we are
			// going to use (and that it even exists and all that)

			$path = m_repath_fs(sprintf(
				'%s/%s/design.phtml',
				m\option::get('surface-theme-path'),
				$this->Theme
			));

			if(file_exists($path)) return $path;
			else return false;
		}

		private function getThemeURI() {
			return sprintf(
				'%s/%s',
				option::get('surface-theme-uri'),
				$this->Theme
			);
		}

		/*// Template Subview
		  // yeah, subviews. i call them area files.
		  //*/

		public function area($area) {
			$path = dirname($this->getThemePath()).'/area/'.$area.'.phtml';
			m_require($path,array('surface'=>$this));
			return;
		}

		/*// Template Storage Engine API
		  // come back and comment here again bob.
		  //*/

		public function append($key,$value) {
			if(!array_key_exists($key,$this->Storage)) $this->Storage[$key] = $value;
			else $this->Storage[$key] .= $value;
			return;
		}

		public function get($key) {
			if(is_array($key)) {
				$list = array();
				foreach($key as $what) $list[] = $this->get($what);
				return $list;
			} else {
				if(array_key_exists($key,$this->Storage)) return $this->Storage[$key];
				else return null;
			}
		}

		public function has($key) {
			if(array_key_exists($key,$this->Storage) && $this->Storage[$key])
				return true;
			else
				return false;
		}

		public function show($key,$newline=false) {
			if(array_key_exists($key,$this->Storage))
			echo $this->Storage[$key], (($newline)?(PHP_EOL):(''));

			return;
		}

		public function set($key,$value) {
			return $this->Storage[$key] = $value;
		}

		public function uri($path,$return=false) {
			$uri = sprintf(
				'%s/%s',
				$this->getThemeURI(),
				$path
			);

			if($return) return $uri;
			else echo $uri;
		}

	}
}

namespace {

	///////////////////////////////////////////////////////////////////////////
	// self-configuration /////////////////////////////////////////////////////

	m\ki::queue('m-config',function(){

		// define default settings.
		m\option::define(array(
			'surface-auto'        => true,
			'surface-theme'       => 'default',
			'surface-style'       => 'default',
			'surface-brand-title' => true,
			'surface-theme-path'  => sprintf('%s%sthemes',m\root,DIRECTORY_SEPARATOR),
		));

		// calculate the theme uri from the theme path. this is far from an
		// exact science but should more or less under most simple cases allow
		// the framework to operate from a domain root or a subfolder without
		// config.

		// if this for some reason failed on your configuration you will need
		// to set surface-theme-uri yourself in the config file.

		// site accessed via http://whatever.wut/ as the root
		// surface-theme-uri should be set to "/m/themes"

		// site accessed via http://whatever.wut/zomg/bbq/ as the root
		// surface-theme-uri should be set to "/zomg/bbq/m/themes"

		// (assming you left surface-theme-path default)

		list($trash,$rooturi) = explode(
			m_repath_uri($_SERVER['DOCUMENT_ROOT']),
			m_repath_uri(m\option::get('surface-theme-path'))
		);
		m\option::define('surface-theme-uri',$rooturi);
		unset($rooturi,$trash);

		return;
	});

	///////////////////////////////////////////////////////////////////////////
	// initialization of the managed surface instance /////////////////////////

	m\ki::queue('m-setup',function(){

		// do not automatically capture on output platforms that should by the
		// very definition of their nature be unsurfaced.
		switch(m\stash::get('platform')->type) {
			case 'api': { }
			case 'bin': { }
			case 'cli': { return; }
		}

		if(m\option::get('surface-auto')) {
			m\stash::set('surface',new m\surface)->CaptureStart();

			// when a browser is told to redirect we need to shut down in
			// a way that cancels the theme engine properly or you will get
			// the "headers already sent" roflcopter.
			m\ki::queue('m-request-redirect',function(){
				if($surface = m\stash::get('surface')) {
					$surface->CaptureStop(false);
					m\stash::destroy('surface');
				}
			});
		}

		return;
	});

}
