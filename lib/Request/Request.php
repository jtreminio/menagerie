<?php

namespace m {

	class request {

		static function pathable($input,$strict=true) {
			if($strict) return preg_replace('/[^a-zA-Z0-9\-]/','',$input);
			else return preg_replace('/[^a-zA-Z0-9\-\.\\]/','',$input);
		}

	}

}

namespace {
	m\ki::queue('m-setup',function(){

		return;
	});
}

?>