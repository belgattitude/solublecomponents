:tocdepth: 4
Normalist ORM
=============

.. note:: 
   Normalist is an opensource zero configuration ORM for PHP 5.3+.

Introduction
------------

Normalist has been designed to provide an alternative to standard ORM's by 
allowing models to be dynamically guessed from your database structure, which 
make them usable without previous definition. Its beautiful API is inspired by Doctrine, Laravel Eloquent and 
Zend Framework 2, offering simple and intuitive methods to play with your database.

Features
++++++++

+ Automatic models and synthetic tables
+ Elegant and intuitive API
+ Secure, automatic protection against SQL injections
+ Comprehensive error reporting
+ Modernize your existing code
+ Easily integrable into every new or existing PHP project 
+ Support custom table prefix
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

.. note::     
   All dependencies will be automatically downloaded and installed in your vendor project directory.


Usage reference
---------------

SyntheticTableManager
+++++++++++++++++++++

The TableManager provides a simple and central way to work with your table and models.


Database connection
~~~~~~~~~~~~~~~~~~~

TableManager requires a Zend\\Db\\Adapter\\Adapter database connection. 

.. code-block:: php

    <?php
    use Soluble\Normalist\Synthetic\TableManager;
    use Zend\Db\Adapter\Adapter;
    
    $config = array(
        'driver'    => 'MySQLi',  // or PDO_Mysql
        'hostname'  => 'localhost',
        'username'  => 'db_user',
        'password'  => 'db_password',
        'database'  => 'my_db'
    );

    $adapter = new Adapter($config);
       
    $tm = new TableManager($adapter);

.. note::     
   The list of options supported by the adapter are explaind in the `Zend\\Db\\Adapter\\Adapter <http://framework.zend.com/manual/2.2/en/modules/zend.db.adapter.html>`_ reference guide.

.. note::
   Depending of your needs, you may adopt different strategies to ensure a unique instance across you project (singleton, service locator...). 
   See also our chapter about third party integration.

SyntheticTable
++++++++++++++

SyntheticTable makes interacting with database tables extremely simple. 

Getting a SyntheticTable
~~~~~~~~~~~~~~~~~~~~~~~~

Synthetic tables are available through the TableManager object. Just call the SyntheticTableManager::table($table_name) method. 

.. code-block:: php
   :emphasize-lines: 2

    <?php
    $tm = new TableManager($adapter);
    $userTable = $tm->table('user');


Finding a record
~~~~~~~~~~~~~~~~

To get a specific record just pass the primary key value to the SyntheticTable::find($pk) method. 
SyntheticTable will automatically figure out which is the primary key of the table
and fetch your record accordingly to the requested id.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $userTable = $tm->table('user');
   $userRecord = $userTable->find(1);
   if (!$userRecord) {
       echo "Record does not exists";
   }
   echo get_class($userRecord); // -> SyntheticRecord


Alternatively you can use the SyntheticTable::findOneBy($predicate) method to specify
the column(s) used to retrieve your record.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $userTable = $tm->table('user');
   $userRecord = $userTable->findOneBy(array('email' => 'test@example.com'));
   if (!$userRecord) {
       echo "Record does not exists";
   }
   echo get_class($userRecord); // -> SyntheticRecord

.. note::
   An exception will be thrown if SyntheticTable::findOneBy($predicate) condition matches more than one record.
   
Although it may be considered as a bad database design, SyntheticTable is also able to work with composite primary key 
(when a primary key spans over multiple columns). Just specify the columns and their values as an associative array.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $orderlines = $tm->table('order_line');
   $orderline = $userTable->find(array('order_id' => 1, 'order_line' => 10));

Depending on your preferences you can also use the SyntheticTable::findOrFail() or SyntheticTable::findOneByOrFail()
versions. Instead of returning a false value when a record have not been found, 
a Normalist\\Synthetic\\Exception\\RecordNotFoundException will be thrown.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   use Normalist\Synthetic\Exception as SyntheticException;

   $userTable = $tm->table('user');
   try {
       $userRecord = $userTable->findOrFail(1);
       $userRecord = $userTable->findOneByOrFail(array('email' => 'test@example.com'));
   } catch (SyntheticException\RecordNotFoundException $e) {
       echo "Record not found: " . $e->getMessage(); 
   }

sdf

.. code-block:: php

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