<?php

namespace m {
	use \m as m;

	if(!function_exists('recaptcha_check_answer'))
	m_require('-/lib/Recaptcha/share/recaptchalib.php');

	class Recaptcha {

		public function __construct() {
			$this->PublicKey = option::get('recaptcha-public-key');
			$this->PrivateKey = option::get('recaptcha-private-key');
			return;
		}

		public function GetHTML() {
			return recaptcha_get_html($this->PublicKey).PHP_EOL;
		}

		public function IsValid() {
			$r = recaptcha_check_answer(
				$this->PrivateKey,
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]
			);

			return $r->is_valid;
		}
	}

}

?>