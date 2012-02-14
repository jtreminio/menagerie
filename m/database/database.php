<?php

namespace m {

	class database {
	
		static $dbx = array();
	
		public function __construct($which=null) {
		
			if(is_array($which) or is_object($which)) {
				$which = (object)$which;
								
			}
		
			$config = option::get('m-database');
			if(!$config or !is_array($config))
			throw new Exception('database configuration is nowhere near valid');

			

			return;
		}
		
	}


}

?>