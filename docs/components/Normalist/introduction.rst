Quick introduction
==================

Normalist is a minimalistic ORM without the need to define model. Inspired by Laravel Eloquent and Zend Framework 2 database components.

Requirements
------------

PHP 5.3 and a MySQL database 5.1+

Installation
------------

The recommended way to install Normalist is through `Composer`_.
Composer is a dependency management library for PHP.

Here is an example of composer project configuration that requires normalist
version 0.1.

.. code-block:: json

    {
        "require": {
            "soluble/normalist": "~0.1.0"
        }
    }

Install the dependencies using composer.phar and use Imagine :

.. code-block:: none

    php composer.phar install
    


Basic usage
-----------

Using Synthetic tables
++++++++++++++++++++++

To 

.. code-block:: php

   <?php
   use Normalist\Synthetic\TableManager;

   $tm = new TableManager($adapter);
   $posts = $tm->table('post');

   // Will return an existing post
   $post = $posts->find(1); 
   if ($post) {
     echo "Found post: " . $post->title;
   }
   
   // Will return false
   $post = $posts->find(5454654156151);

   // Test if a record exists
   $test = $posts->exists(1);

   $results = $posts->search()
                ->where(array('category_id' => 1))
                ->order(array('updated_at DESC', 'title ASC'))
                ->toArray();


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
	
	


. TIP::
   Read more about SyntheticTable_

The ``SyntheticTable::`` method may throw one of the following exceptions:

* ``Normalist\Synthetic\Exception\InvalidArgumentException``

.. TIP::
   Read more about Normalist/exceptions_

