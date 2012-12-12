<?php

namespace m {
	use \m as m;

	class user extends m\Object {

		static $PropertyMap = array(
			'u_id'            => 'ID',
			'u_alias'         => 'Alias',
			'u_email'         => 'Email',
			'u_phash'         => 'PHash',
			'u_psand'         => 'PSand',
			'u_fname'         => 'FirstName',
			'u_lname'         => 'LastName',
			'u_ltime'         => 'LoginTime',
			'u_jtime'         => 'JoinTime',
			'u_admin'         => 'Admin'
		);

		public $Database;

		public function __construct($raw,$opt=null) {
			$opt = new m\object($opt,array(
				'KeepHashes' => false,
				'Database'   => null
			));

			parent::__construct($raw);

			// support separate and/or sharded databasees.
			$this->Database = $opt->Database;

			// as a preventive security measure to help prevent a developer
			// from var_dumping something stupid, hashes are stripped from
			// the object unless you ask for them to be kept. basically, only
			// the login system really should be doing that.
			if(!$opt->KeepHashes) {
				foreach(array('PHash','PSand') as $hashkey) {
					if(property_exists($this,$hashkey))
					unset($this->{$hashkey});
				}
				unset($hashkey);
			}

			return;
		}

		//////////////////////////////////////////////////////////////////////
		// session management ////////////////////////////////////////////////

		public function sessionDestroy() {
			setcookie('m_user','',-1,'/');
			return;
		}

		public function sessionUpdate() {
			if(!property_exists($this,'PHash') || !$this->PHash)
			throw new \Exception('Unable to update session without Hash data in this object. (KeepHashes?)');

			$cdat = sprintf(
				'%d:%s',
				$this->ID,
				hash('sha512',"{$this->PHash}{$this->PSand}")
			);

			//. update the login cookie.
			setcookie('m_user',$cdat,(time() + (86400*30)),'/');
			return;
		}

		static function getFromSession($opt=null) {
			$opt = new m\Object($opt,array(
				'Database' => option::get('m-user-database') or null
			));
			$opt->KeepHashes = true;

			// quit if no session data.
			$cookie = new m\Request\Input('cookie');
			if(!$cookie->m_user) return false;

			// quit if invalid session data format.
			if(strpos($cookie->m_user,':') === false) return false;
			list($uid,$hash) = explode(':',$cookie->m_user);

			// quit if invalid user.
			$user = self::get((int)$uid,$opt);
			if(!$user) return false;

			// quit if invalid data.
			if($hash != hash('sha512',"{$user->PHash}{$user->PSand}"))
			return false;

			// looks like a valid login.
			return $user;
		}

		//////////////////////////////////////////////////////////////////////
		// cache methods /////////////////////////////////////////////////////

		public function cacheUpdate() {
			appcache::set("user-id-{$this->ID}",$this);
			appcache::set("user-alias-{$this->Alias}",$this);
			appcache::set("user-email-{$this->Email}",$this);
		}

		public function cacheDestroy() {
			appcache::drop("user-id-{$this->ID}",$this);
			appcache::drop("user-alias-{$this->Alias}",$this);
			appcache::drop("user-email-{$this->Email}",$this);
		}

		//////////////////////////////////////////////////////////////////////
		// query methods /////////////////////////////////////////////////////

		static function get($what,$opt=null) {
			if(!$what) return false;

			$opt = new m\object($opt,array(
				'KeepHashes'       => false,
				'Database'         => option::get('m-user-database') or null,
				'ReadCache'        => true,
				'WriteCache'       => true,
				'ExtendedClass'    => option::get('user-class-extended'),
				'UseExtendedClass' => true
			));

			// check the various cache systems before attempting to peg the
			// database for a user.
			if($opt->ReadCache) {
				$user = false;

				// local appcache. has this been asked for before earlier
				// during the same process?
				if(strpos($what,'@')!==false) $user = Appcache::get("user-email-{$what}");
				else if(is_string($what)) $user = Appcache::get("user-alias-{$what}");
				else if(is_int($what)) $user = Appcache::get("user-id-{$what}");

				if($user) {
					$user->FromCache = true;
					return $user;
				}
			}

			$where = false;
			if(strpos($what,'@')!==false) {
				// search by email address.
				$where = 'u_email LIKE "%s"';
			} else if(is_string($what)) {
				// search by username.
				$where = 'u_alias LIKE "%s"';
			} else if(is_int($what)) {
				// search by unique id.
				$where = 'u_id=%d';
			}

			//. if no valid data type then quit.
			if(!$where) return false;

			//. find the record.
			//. note that i built the query here expecting you to pass probably
			//. dirty data, so dumping a username from straight post data will
			//. still end up being cleaned up by the database library to not
			//. can has injection.
			$db = new m\Database($opt->Database);
			$who = $db->queryf(
				"SELECT * FROM m_users WHERE {$where} LIMIT 1;",
				$what
			)->next();

			// if no user was found then stop right here and return a
			// negative result.
			if(!$who) return false;

			// else build up a valid user object passing on the options that
			// were given to this function.

			if($opt->UseExtendedClass && $opt->ExtendedClass)
			$class = $opt->ExtendedClass;
			else
			$class = 'm\user';

			$user = new $class($who,array(
				'Database' => $opt->Database,
				'KeepHashes' => $opt->KeepHashes
			));
			$user->FromCache = false;

			// allow the ki to flow that will allow third party libraries to
			// modify this object.
			ki::flow('m-user-get',array($user));

			// allow the cache to be primed with the object in its current
			// state, after any possible extentions.
			if($opt->WriteCache && !$opt->KeepHashes)
				$user->cacheUpdate();

			return $user;
		}

		//////////////////////////////////////////////////////////////////////
		// user creation /////////////////////////////////////////////////////

		static function create($input) {
			$input = new object($input,array(
				'Username' => false,
				'Password' => false,
				'PConfirm' => false,
				'Email'    => false
			));

			// validate the data and throw exceptions to notify of any issues
			// that arise from the dataset.

			/* Exception Codes **********************************************
			 * 1 - missing data
			 * 2 - invalid chars in username
			 * 3 - password too short
			 * 4 - invalid data types for fields
			 * 5 - password confirm match failed
			 * 6 - username already in use
			 * 7 - email already in use
			 * 8 - unknown error
			 * 9 - username has no letters in it
			 * 10 - username not long enough
			 */

			// check we have data and it is not empty.
			foreach(array('Username','Password','Email') as $prop) {
				if(!$input->{$prop}) throw new \Exception("No {$prop} specified",1);
			}

			// check that things that should be strings are strings.
			foreach(array('Username','Password','Email') as $prop) {
				if(!is_string($input->{$prop}))
				throw new \Exception("Invalid datatype for {$prop}",4);
			}

			// check that the username only had valid characters.
			if($input->Username !== request::pathable($input->Username))
			throw new \Exception('Invalid characters in Username. (A-Z, 0-9, and Dash)',2);

			// check that the username contains some letters. e.g. disallow
			// pure numeric names so self::get can has automode.
			if(!preg_match('/[a-z]/i',$input->Username))
			throw new \Exception('Username must at least contain some letters.',9);

			// check that the username is long enough.
			$ulength = option::get('user-username-length');
			if(strlen($input->Username) < $ulength)
			throw new \Exception("Username must be at least {$ulength} characters long.",10);

			// check that the password was long enough.
			$plength = option::get('user-password-length');
			if(strlen($input->Password) < $plength)
			throw new \Exception("Password must be at least {$plength} characters long.",3);

			// check that the requested password confirmation match matched.
			// this is optional to allow for admin api to create users or
			// whatever.
			if($input->PConfirm !== false) {
				if($input->Password !== $input->PConfirm)
				throw new \Exception("Passwords did not match.",5);
			}

			// check that the username and email address submitted is not
			// already in use by another account.
			$db = new m\Database;
			$olduser = $db->queryf(
				'SELECT u_alias,u_email FROM m_users WHERE u_alias LIKE "%s" OR u_email LIKE "%s" LIMIT 1;',
				$input->Username,
				$input->Email
			)->next();

			if($olduser) {
				if(strtolower($input->Username) == $olduser->u_alias)
					throw new \Exception("Username already in use. Choose another.",6);

				if(strtolower($input->Email) == $olduser->u_email)
					throw new \Exception("Email already associated with an account.",7);
			}

			/*
			 * create
			 */

			$phash = hash('sha512',$input->Password);
			$psand = hash('sha512',sprintf('%s %d',microtime(),rand(1,9001)));

			$u_id = $db->queryf(
				'INSERT INTO m_users '.
				'(u_alias,u_email,u_phash,u_psand,u_jtime,u_ltime) '.
				'VALUES ("%s","%s","%s","%s",%d,0);',
				$input->Username,
				$input->Email,
				$phash,
				$psand,
				time()
			)->id();

			m\ki::flow('m-user-created',array($u_id));

			if($u_id) {
				return self::get((int)$u_id,array(
					'KeepHashes'       => true,
					'UseExtendedClass' => false
				));
			} else {
				throw new \Exception("Unknown error creating account.",8);
			}
		}

		//////////////////////////////////////////////////////////////////////
		// authenticate //////////////////////////////////////////////////////

		static function auth($input) {
			$input = new m\object($input,array(
				'Account'  => false,
				'Password' => false
			));

			/* Exception Codes
			 * 1 - missing data
			 * 2 - invalid datatype
			 * 3 - user not found
			 * 4 - invalid password
			 */

			// check that we have our data.
			foreach($input as $prop => $val) {
				if(!$val) throw new \Exception("No {$prop} specified",1);
				if(!is_string($val)) throw new \Exception("Invalid type for {$prop}",2);
			}

			// see if the account exists.
			$user = self::get($input->Account,array('KeepHashes'=>true));
			if(!$user) throw new \Exception('User not found',3);

			// see if the passwords match.
			if($user->PHash !== hash('sha512',$input->Password))
			throw new \Exception('Invalid password',4);

			return $user;
		}

		//////////////////////////////////////////////////////////////////////
		// automatic post handlers ///////////////////////////////////////////

		static function handlerSignup($post) {
		// handle creating new accounts in the system.

			try {
				$user = false;

				if(m\option::get('user-recaptcha-signup')) {
					$cap = new m\Recaptcha;
					if(!$cap->isValid()) {
						throw new \Exception('Invalid security code');
					}
				}

				$class = m\option::get('user-class-extended');
				if(!$class) $class = 'm\user';

				$user = $class::create(array(
					'Username' => $post->username,
					'Password' => $post->password1,
					'PConfirm' => $post->password2,
					'Email'    => $post->email
				));
			}

			catch(\Exception $e) {
				self::handlerException($e);
			}

			// start a session with our new user.
			if($user) {
				$message = m\stash::get('message');
				$message->add('Your account has been created and you have been signed in.','success');

				$user->sessionUpdate();
				$bye = new m\Request\Redirect(($post->redirect)?($post->redirect):('m://refresh'));
				$bye->go();
			}
		}

		static function handlerLogin($post) {
		// handle authenticating and starting a user session.

			try {
				$user = false;
				$user = self::auth(array(
					'Account' => $post->account,
					'Password' => $post->password
				));
			}

			catch(\Exception $e) {
				self::handlerException($e);
			}

			// start a session with the authenticated user.
			if($user) {
				$message = m\stash::get('message');

				$user->sessionUpdate();
				$message->add('You have successfully signed in.','success');

				// and refresh or go somewhere.
				$bye = new m\Request\Redirect(($post->redirect)?($post->redirect):('m://refresh'));
				$bye->go();
			}
		}

		static function handlerLogout($post) {
		// handle terminating the user session. once destroyed send them back
		// home.

			$user = m\stash::get('user');

			if($user) {
				$message = m\stash::get('message');
				$message->add('You have been logged out.');
				$user->sessionDestroy();
			}

			// go home.
			$bye = new m\Request\Redirect('m://home');
			$bye->go();
		}

		static function handlerException($e) {
		// when any of the POST handlers catch an exception it can be passed
		// to this, which will decide what type of output platform to render
		// the error out with.

			$text = sprintf('Error: %s (%d)',$e->getMessage(),$e->getCode());

			switch(m\platform) {
				case 'api': {
					$api = new m\api;
					$api->shutdown($e->getMessage(),$e->getCode());
					break;
				}
				default: {
					$message = m\stash::get('message');
					if($message) $message->add($text,'error');
					else die($text);
				}
			}

			return;
		}


	}

}

namespace m {
	m_require('-lDatabase');
	m_require('-lMessage');

	///////////////////////////////////////////////////////////////////////////
	// library config /////////////////////////////////////////////////////////
	ki::queue('m-config',function(){
		option::define(array(
			'user-username-length'   => 3,
			'user-password-length'   => 6,
			'user-enable-post-hooks' => true
		));
	});

	///////////////////////////////////////////////////////////////////////////
	// library setup //////////////////////////////////////////////////////////
	ki::queue('m-setup',function(){
		stash::set('user',($user = user::getFromSession()));

		//////////////////////////////////////////////////////////////////////

		// allow integration with the surface library to provide automatic
		// scope into the theme engine, but only if the theme engine is
		// already loaded. we do not want this test to autoload it. it expects
		// a reference to the scope array that we need to add elements to.

		if(class_exists('\m\surface',false))
		ki::queue('surface-build-render-scope',function(&$scope){
			if(!array_key_exists('user',$scope))
			$scope['user'] = stash::get('user');

			return;
		},true);

		//////////////////////////////////////////////////////////////////////

		if(option::get('user-enable-post-hooks')) {
			$post = new Request\Input('post');
			if(!$post->action) return;

			// if the user is not signed in register the handlers for sign up
			// and logging in.
			if(!$user) ki::queue('m-ready',function(){
				$post = new Request\Input('post');
				if(!$post->action) return;

				switch($post->action) {
					case 'm-signup': { user::handlerSignup($post); break; }
					case 'm-login': { user::handlerLogin($post); break; }
				}

				return;
			});

			// if the user is logged in then register the handler for logging
			// out.
			else ki::queue('m-ready',function(){
				$post = new Request\Input('post');
				if(!$post->action) return;

				switch($post->action) {
					case 'm-logout': { user::handlerLogout($post); break; }
				}

				return;
			});

		}

		return;
	});

}

?>