<?php

namespace m;

/*//
@class m\Object

this class is designed as a utility to easily create nicely organized objects
from arrays or objects which may be cumbersome to use. it has the ability to
remap properties to new names and make sure that any default data that needs
to be set in properties are set. take for example this object that was fetched
from a database.

	{ "u_id":4, "u_name":"bob", "u_email":"bob@localhost" }

we can define a class called User that extends Object with the following
PropertyMap

	class User extends Object {
		static $PropertyMap = [
			'u_id'    => 'ID',
			'u_name'  => 'Name',
			'u_email' => 'Email',
			'u_admin' => 'Admin'
		];

		// user methods...

	}

we can easily convert that database row into a nice object.

	$user = new User($row);
	var_dump($user);

we can also make sure that any missing data is automatically filled in. in this
example our database row did not have a field called u_admin but we did define
one in the property map. because it did not exist the property Admin never would
have been created. to make sure it exists we can specify an object or array that
contains default values that need to be there.

	$user = new User(
		$row,
		['Admin'=>false]
	);
	var_dump($user);

in the very least we can now trust that the property Admin will exist if it did
not get populated by the database and will default to false.
//*/

class Object {

	/*//
	@property static array PropertyMap
	holds a mapping index that is used to convert objects or arrays into nice
	looking objects via late-static binding.
	//*/

	static $PropertyMap = array();

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	public function __construct($input=null,$defaults=null) {

		//. initialize the object with the input data, running the input
		//. by the property map first if need be.
		if(is_array($input)) $input = (object)$input;
		if(is_object($input)) {
			if(count(static::$PropertyMap))
			$this->InputApplyMap($input);
		}

		//. set any default properties that may have been missing from
		//. the original input data.
		if(is_array($defaults)) $defaults = (object)$defaults;
		if(is_object($defaults)) {
			$this->InputProperties($defaults,false);
		}

		//. an experimental idea, allow this object to self-ready itself if
		//. a ready psuedomagical method was defined.
		if(method_exists($this,'__ready')) $this->__ready();

		return;
	}

	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////

	/*//
	@method protected void InputApplyMap
	uses the PropertyMap to remap the input data to the specified properties
	in this object.
	//*/

	protected function InputApplyMap($input) {

		foreach(static::$PropertyMap as $old => $new)
			if(property_exists($input,$old))
				$this->{$new} = $input->{$old};

		return;
	}

	/*//
	@method protected void InputProperties
	insures that if a set of default property values were given that those
	properties are set in this object.
	//*/

	protected function InputProperties($source,$overwrite=false) {

		foreach($source as $property => $value)
			if(!property_exists($this,$property) || $overwrite)
				$this->{$property} = $value;

		return;
	}

}
