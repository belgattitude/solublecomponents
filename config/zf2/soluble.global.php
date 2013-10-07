<?php
return array(
    'Soluble\Db' => array(
		'Soluble\Db\Metadata\Cache' => array(
			'invokables' => array(
				'Soluble\Db\Metadata\Cache' => 'Soluble\Db\Metadata\Cache',
			),
			/*
			'factory' => array(
				'Zend\Cache\StorageFactory' => array(
					'adapter' => 'filesystem',
					'options' => array(
						'ttl' => 0,
						'cache_dir' => '/tmp/cache',
						'dir_level' => 1,
						'dir_permission' => 0777,
						'file_permission' => 0666
					),
					'plugins' => array(
						'exception_handler' => array('throw_exceptions' => true)
					)				
				)
			)
		    */		
		)		 
    ),
);