<?php

namespace m {

	class database {
	
		static $config;
	
		public function __construct($config=null) {
			if(!$config) { }
	
			return;
		}
		
	}
	
	ki::queue('zen-setup',function(){
		database::$config = option::get('m-database');
		if(!is_array(database::$config)) database::$config = array();
		return;
	});

}

?>