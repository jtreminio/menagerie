<?php

namespace m;
use \m as m;

////////////////////////////////////////////////////////////////////////////////
// dependencies ////////////////////////////////////////////////////////////////

m_require('-lplatform');
m_require('-lmessage');

////////////////////////////////////////////////////////////////////////////////
// self-configuration //////////////////////////////////////////////////////////

ki::queue('m-config',function(){

	option::define(array(

		/*//
		@option boolean surface-auto
		@default true

		controls if the library should create an instance to manage for you
		automatically. for "normal web sites" this makes your life easier.
		it is accessable via the menagerie stash.
		//*/

		'surface-auto' => true,

		/*//
		@option string surface-theme
		@default "default"

		the theme which the renderer will attempt to use at shutdown render
		time. note this does not have to be an HTML template. you could
		make templates which render DNS zone files or Email bodies.
		//*/

		'surface-theme' => 'default',

		/*//
		@option string surface-style
		@default "default"

		a substyle option for themes. generally only pages will use this
		for including an additional css file or something to that effect.
		//*/

		'surface-style' => 'default',

		/*//
		@option boolean surface-brand-title
		@default true

		will do its best to nicely rewrite the page-title field to include
		the site name. (generally thought of as a good SEO practice)
		//*/

		'surface-brand-title' => true,

		/*//
		@option string surface-theme-path
		@default <>

		this is the local file path to the directory that has the themes
		you want to use in it. by default this will be similar to...

		* <whatever>/m/themes, e.g.:
		* /var/www/whatever.com/m/themes

		depending on where you are running from obviously.
		//*/

		'surface-theme-path' => m_repath_fs(sprintf('%s/themes',m\root)),

		/*//
		@option string surface-theme-uri
		@default <>

		this is the uri path to the directory that has the themes you want
		to use in it. it attempts to build itself off a common
		understanding of how filepaths and basic web configurations work.

		* IF your site root is http://whatever.com/
		  AND surface-theme-path is /var/www/whatever.com/m/themes
		  THEN surface-theme-uri = /m/themes

		* IF your site root is http://whatever.com/zomg/bbq/
		  AND surface-theme-path is /var/www/whatever.com/m/themes
		  THEN surface-theme-uri = /zomg/bbq/m/themes
		//*/

		'surface-theme-uri' => str_replace(
			m_repath_uri($_SERVER['DOCUMENT_ROOT']),'',
			m_repath_uri(option::get('surface-theme-path'))
		)

	));

	return;
});

////////////////////////////////////////////////////////////////////////////////
// initialization of the managed surface instance //////////////////////////////

ki::queue('m-setup',function(){

	// do not automatically capture on output platforms that should by the
	// very definition of their nature be unsurfaced. determined by way of the
	// platform library.
	switch(stash::get('platform')->type) {
		case 'api': { }
		case 'bin': { }
		case 'cli': { return; }
	}

	if(option::get('surface-auto')) {
		stash::set('surface',new Surface)->CaptureStart();

		// when a browser is told to redirect we need to shut down in a way
		// that cancels the theme engine properly or you will get the "headers
		// already sent" roflcopter in your face.
		ki::queue('request-redirect',function() {
			if($surface = stash::get('surface')) {
				$surface->CaptureStop(false);
				stash::destroy('surface');
			}
		});
	}

	return;
});

/*//
@class Surface

manages rendering surfaces for formatting displays with template files. can be
used to render HTML web pages, Emails, or anything else that requires a common
format with variable fields.
//*/
class Surface {

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@property public string Theme

	holds the name of the current theme that this instance is set to use when
	rendering surfaces.
	//*/

	public $Theme;

	/*//
	@property public string Style

	holds the name of a substyle that themes may optionally use to further
	customize their displays. not used by the library directly for anything.
	//*/

	public $Style;

	/*//
	@property public boolean Print

	if true when the surface renders it is automatically printed to the output
	stream. if false it is returned by the Render method instead. each new
	instance defaults this to true.
	//*/

	public $Print;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@property private array Storage

	a private storage for all the variable data to be used in the surface
	system at render time.
	//*/

	private $Storage = array();

	/*//
	@property private boolean Capturing

	a private switch noting if this instance had started an overbuffer to
	automatically capture stdout.
	//*/

	private $Capturing = false;

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

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
		if($this->Capturing)
		$this->Render();

		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method public boolean CaptureStart
	@flags internal

	starts an offscreen buffer for capturing STDOUT.
	//*/

	public function CaptureStart() {
		if($this->Capturing) return false;

		ob_start();
		$this->Capturing = true;

		return true;
	}

	/*//
	@method public boolean CaptureStop
	@arg boolean Append default true
	@flags internal

	stops an offscreen buffer. if the Append argument is true then the contents
	from the buffer that was stopped is appended to the internal storage for
	this surface instance. if the Append argument is false then the output is
	disregarded.
	//*/

	public function CaptureStop($append=true) {
		if(!$this->Capturing) return false;

		$output = ob_get_clean();
		$this->Capturing = false;

		if($append)
		$this->append('stdout',$output);

		return true;
	}

	/*//
	@method public void Render
	@flags internal

	renders a complete template using the design.phtml as the main container.
	calling this will automatically fetch any STDOUT that may have been
	captured to be rendered by the 'stdout' key in the page template. this
	surface instance is also passed to the template files and is accessable via
	the $surface interface.
	//*/

	public function Render() {
		$themepath = $this->getThemePath();
		if(!$themepath) throw new \Exception("theme {$this->Theme} not found");

		// get stdout.
		if($this->Capturing)
		$this->CaptureStop(true);

		// do some special case stuff now that it is render time.
		$this->RenderDoSpecial();

		// run theme.
		if($this->Print) {
			m_require($themepath,array('surface'=>$this));
			return;
		} else {
			ob_start();
			m_require($themepath,array('surface'=>$this));
			return ob_get_clean();
		}

	}

	/*//
	@method private void RenderDoSpecial
	@flags internal

	perform some special renderings based on options that might have been set.
	these are tasks designed to make templating pages a little nicer.
	//*/

	private function RenderDoSpecial() {


		// brand the page-title
		if(option::get('surface-brand-title')) {

			// if data has been stored in page-title we will automatically
			// append the app-name to the end of it. this is generally a good
			// thing to do for SEO and whatever.
			if($this->has('page-title'))
			$this->append('page-title',sprintf(
				' - %s',
				option::get('app-name')
			));

			// if no page-title has been set by the application we will
			// generate one that is in the format of:
			// app-name - app-description-short
			else
			$this->set('page-title',trim(sprintf(
					'%s - %s',
					option::get('app-name'),
					option::get('app-description-short')
			),'- '));

		}

		// generate the page-description
		// if no page-description has been defined we will use the configured
		// app-description-long as a default.
		if(!$this->has('page-description'))
		$this->set(
			'page-description',
			option::get('app-description-long')
		);

		return;
	}

	/*//
	@method private string GetThemePath
	@flags internal

	check that the theme that is requested exists. if it does it returns the
	full filepath to that file. else it returns false.
	//*/

	private function GetThemePath() {

		$path = m_repath_fs(sprintf(
			'%s/%s/design.phtml',
			m\option::get('surface-theme-path'),
			$this->Theme
		));

		if(file_exists($path)) return $path;
		else return false;
	}

	/*//
	@method private string GetThemeURI
	@flags internal

	returns the uri to the theme directory for use in URI/URLs. if this is
	generating bad prefixes then you may need to tweak surface-theme-path and
	or surface-theme-uri in your config file.
	//*/

	private function GetThemeURI() {
		return sprintf(
			'%s/%s',
			option::get('surface-theme-uri'),
			$this->Theme
		);
	}

	/*//
	@method public void Area
	@arg string Filename

	render a template subview from the area folder in a theme. allows you to
	break themes into sections and pull them in as you want them like a box
	of legos.
	//*/

	public function Area($area) {
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
