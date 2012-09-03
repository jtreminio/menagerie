<?php

namespace m {
	ki::queue('m-init',function(){
		m_require('-lsurface');
	});

	ki::queue('m-config',function() {

		// application.
		option::set(array(
			'app-name'              => 'Blogster',
			'app-description-short' => 'Blogging Community',
			'app-description-long'  => 'Free online blogging community. Share and discuss. Post your news, opinions, cats, or all three at once!',
			'm-surface-theme-path'  => sprintf('%s/share/themes',dirname(dirname(__FILE__)))
		));

		// database options.
		option::set('m-database',array(
			'default' => array(
				'driver'   => 'mysql',
				'hostname' => 'localhost',
				'username' => 'username',
				'password' => 'password',
				'database' => 'database'
			)

		));

	});

}

?>