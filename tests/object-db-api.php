<?php

require(sprintf('%s/m/application.php',dirname(dirname(__FILE__))));

/*// first order of business is to demonstrate extending the
  // frameworks smart object. with it we are going to convert
  // a database dump into a neat object.
  //*/

class testuser extends m\object {
	static $PropertyMap = array(
		'u_id'    => 'ID',
		'u_name'  => 'Name',
		'u_email' => 'Email'
	);
	
	public function __ready() {
		$this->Hash = md5($this->Email);
		return;
	}
}


/*// now using our database connectivity suite to do something
  // like fetch a simple list of users.
  //*/

$db = new m\database;
$query = $db->queryf('SELECT u_id,u_name,u_email FROM users ORDER BY u_email ASC;');

$userlist = array();
while($dump = $query->next()) {
	$userlist[] = new testuser($dump);
	
	// testuser Object
	// (
	//    [ID] => 3
	//    [Name] => bob dumpr
	//    [Email] => bob@dumpr.info
	//    [Hash] => 1e910051baa0d3f3e7fe710cb9db8c9f
	// )
}


/*// and now i have decided this is now a simple json api to dump the
  // userlist out so lets do that.
  //*/
  
$api = new m\api;
$api->shutdown(array(
	'userlist' => $userlist
));

/*
Menagerie bob$ php test.php
{"errno":0,"errmsg":"","userlist":[{"ID":"3","Name":"bob dumpr","Email":"bob@d...
*/

?>