<?php

namespace m {
	use \m as m;

	// depends on the platform library for max automation.
	m_require('-lplatform');

	class surface {

		public $theme;
		public $style;
		public $print = true;

		private $storage = array();
		private $capturing = false;

		public function __construct($input=null) {
			$opt = new m\object($input,array(
				'theme' => option::get('m-surface-theme'),
				'style' => option::get('m-surface-style')
			));

			$this->theme = $opt->theme;
			$this->style = $opt->style;

			return;
		}

		public function __destruct() {
			if($this->capturing) {
				$this->render();
			}

			return;
		}

		public function startCapture() {
			if($this->capturing) return false;

			ob_start();
			$this->capturing = true;

			return true;
		}

		public function stopCapture($append=true) {
			if(!$this->capturing) return false;

			$output = ob_get_clean();
			$this->capturing = false;

			if($append)
			$this->append('stdout',$output);
			
			return true;
		}

		public function render() {
			$themepath = $this->getThemePath();
			if(!$themepath) throw new \Exception("theme {$this->theme} not found");

			//. get stdout.
			if($this->capturing)
			$this->stopCapture(true);

			//. do some special case stuff now that it is render time.
			$this->doSpecial();

			//. run theme.
			if($this->print) {
				m_require($themepath,array('surface'=>$this));
				return;
			} else {
				ob_start();
				m_require($themepath,array('surface'=>$this));
				return ob_get_clean();
			}
			
		}

		private function doSpecial() {

			if(option::get('m-surface-brand-title')) {
				if($this->has('page-title'))
					$this->append('page-title',trim(sprintf(
						' - %s',
						option::get('app-name')
					),'- '));
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

		private function getThemePath() {
			$path = sprintf(
				'%s%sthemes%s%s%sdesign.phtml',
				m\root,
				DIRECTORY_SEPARATOR,
				DIRECTORY_SEPARATOR,
				$this->theme,
				DIRECTORY_SEPARATOR
			);

			if(file_exists($path)) return $path;
			else return false;
		}

		private function getThemeURI() {
			return sprintf(
				'%s/themes/%s',
				option::get('m-root-uri'),
				$this->theme
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
			if(!array_key_exists($key,$this->storage)) $this->storage[$key] = $value;
			else $this->storage[$key] .= $value;
			return;
		}

		public function get($key) {
			if(array_key_exists($key,$this->storage)) return $this->storage[$key];
			else return null;
		}

		public function has($key) {
			if(array_key_exists($key,$this->storage) && $this->storage[$key])
				return true;
			else
				return false;
		}

		public function show($key,$newline=false) {
			if(array_key_exists($key,$this->storage))
			echo $this->storage[$key], (($newline)?(PHP_EOL):(''));

			return;
		}

		public function set($key,$value) {
			return $this->storage[$key] = $value;
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

	//. define default configuration options.
	m\ki::queue('m-config',function(){
		m\option::define(array(
			'm-surface-auto'        => true,
			'm-surface-theme'       => 'default',
			'm-surface-style'       => 'default',
			'm-surface-brand-title' => true
		));

		return;
	});

	//. start up the auto instance if enabled.
	m\ki::queue('m-setup',function(){

		// do not automatically capture on output platforms that should by the
		// very definition of their nature be unsurfaced.
		switch(m\stash::get('platform')->type) {
			case 'api': { }
			case 'bin': { }
			case 'cli': { return; }
		}

		if(m\option::get('m-surface-auto')) {
			m\stash::set('surface',new m\surface)->startCapture();
		}

		return;
	});

}

?>