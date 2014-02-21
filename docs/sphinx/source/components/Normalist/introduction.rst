:tocdepth: 3
Normalist ORM
=============

.. note:: 
   Normalist is an opensource zero configuration ORM for PHP 5.3+.

Introduction
------------

Normalist has been designed to provide an alternative to standard ORM's by 
allowing models to be dynamically guessed from your database structure, which 
make them usable without previous definition. Its API is inspired by Doctrine, Laravel Eloquent and 
Zend Framework 2, offering simple and intuitive methods to work with your database.

Features
++++++++

+ Automatic models and synthetic tables
+ Intuitive and simple API
+ Secure, automatic protection against SQL injections
+ Comprehensive error reporting
+ Modernize your existing code
+ Easily integrable into every new or existing PHP project 
+ Well documented 
+ Stable 100% unit tested, PSR-2 compliant
+ PHP 5.3+ namespaced
+ MIT licensed

Requirements
++++++++++++

Normalist is written in PHP 5.3 and currently supports MySQL/MariaDb 5.1+ (PDO_Mysql or MySQLi extensions).

Installation
++++++++++++

The recommended way to install Normalist is through `Composer <https://getcomposer.org/>`_.
Just add soluble/normalist in your composer.json file as described below

.. code-block:: json

    {
        "require": {
            "soluble/normalist": "~0.1.0"
        }
    }

Run composer update or install.

.. code-block:: bash

    $ php composer.phar update


Usage
=====

Table Manager
-------------
When 


Synthetic Table
---------------

Finding a record
++++++++++++++++


.. code-block:: php
   :linenos:
    <?php
    use Soluble\Normalist\Synthetic\TableManager;
    use Soluble\Normalist\Synthetic\Exception as SyntheticException;

    // Inject a Zend\Db\Adapter\Adapter object
    $tm = new TableManager($adapter);

    // Get a SyntheticTable from table 'user'
    $userTable = $tm->table('user');

    // Test if a primary key exists
    if ($userTable->exists(1)) { echo "User exists"; } ;

    // Getting an user record
    $userRecord = $userTable->findOneBy(array('username' => 'loginname'));
    $userRecord = $userTable->find(1);
    if (!$userRecord) {
        echo "User does not exists";
    }

    // Getting an user record or throw an Exception
    try {
        $userRecord = $userTable->findOneByOrFail(array('username' => 'loginname'));
        $userRecord = $userTable->findOrFail(1);
    } catch (SyntheticException\RecordNotFoundException $e) {
        echo "Error getting user, it does not exists in database";
    } catch (SyntheticException\ExceptionInterface $e) {
        echo "Error getting user";
    }


Getting records
+++++++++++++++


.. code-block:: php

    <?php

    // Getting all users
    $users = $userTable->all();
    foreach ($users as $userRecord) {
        echo $userRecord->name;
    }
    
    // All users to Json and Array
    $json  = $userTable->all()->toJson();
    $array = $userTable->all()->toArray();

    // Searching users


Finding a record by primary key
+++++++++++++++++++++++++++++++


.. code-block:: php

    <?php
    use Normalist\Synthetic\TableManager;

    $tm = new TableManager($adapter);
    $posts = $tm->table('post');

    // Finding a record by post_id = 1
    $post = $posts->find(1); 
    if ($post) {
        echo "Found post: " . $post->title;
    } else {
        echo "Post not found";
    }

    
   
Retrieving a records by conditions 
+++++++++++++++++++++++++++++++++++

.. code-block:: php

    <?php
    use Normalist\Synthetic\TableManager;

    $tm = new TableManager($adapter);
    $posts = $tm->table('post');

    // Will return an existing post
    $post = $posts->find(1); 
    if ($post) {
        echo "Found post: " . $post->title;
    } else {
        echo "Post not found";
    }



Transactions

.. code-block:: php

	<?php
	use Normalist\Synthetic\TableManager;

	$tm = new TableManager($adapter);

	$tm->transaction()->start();
	try {
		$tm->table('post')->update(array('title' => 'cool'));
		$tm->table('comment')->delete(1);
		// will throw an Exception\RecordNotFoundException;
		$tm->table('comment')->findOrFail(1);
    } catch (\Exception $e) {
		// will rollback any changes made  to the database
		$tm->transaction()->rollback();
		throw $e;
	} 
	$tm->transaction()->commit();
	
Synthetic Record
----------------

	

Notes
=====

In a existing project
---------------------


Typical usage scenarios
-----------------------
Normalist has been primarily designed to modernize, secure and empower existing PHP applications.  
If your project use already a decent ORM such as Doctrine, we recommend you to continue using it.


Portability
-----------

Currently Normalist supports only MySQL or MariaDB databases. Postgres and Oracle could be supported
by implementing a specific reader in the project. 


Contributing
------------

Project contributions are welcome, check our github repository.

Roadmap
-------

Roadmap for the project will be documented soon