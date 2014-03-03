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
Zend Framework 2, offers simple and intuitive methods to play with your database.

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

.. code-block:: bash

    $ php require soluble/normalist


Or alternatively, add soluble/normalist in your composer.json file as described below

.. code-block:: json

    {
        "require": {
            "soluble/normalist": "0.*"
        }
    }


.. code-block:: bash

    $ php composer.phar update



.. note::     
   + Replace dev-master by the latest stable release, see soluble `GitHub account <https://github.com/belgattitude/solublecomponents>`_.
   + All dependencies will be automatically downloaded and installed in your vendor project directory. 


Usage reference
---------------

Synthetic\\TableManager
+++++++++++++++++++++++

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
   + The list of options supported by the adapter are explaind in the `Zend\\Db\\Adapter\\Adapter <http://framework.zend.com/manual/2.2/en/modules/zend.db.adapter.html>`_ reference guide.
   + Depending of your needs, you may adopt different strategies to ensure a unique instance across you project (singleton, service locator...). 
     See also our chapter about third party integration.

Synthetic\\Table
++++++++++++++++

Synthetic\\Table makes interacting with database tables extremely simple. 

Getting a Synthetic\\Table
~~~~~~~~~~~~~~~~~~~~~~~~~~

Synthetic tables are available through the TableManager object. Just call the Synthetic\\TableManager::table($table_name) method. 

.. code-block:: php
   :emphasize-lines: 2

    <?php
    $tm = new TableManager($adapter);
    $userTable = $tm->table('user');


Finding a record
~~~~~~~~~~~~~~~~

To get a specific record just pass the primary key value to the Synthetic\\Table::find($pk) method. 
Synthetic\\Table will automatically figure out which is the primary key of the table
and fetch your record accordingly to the requested id.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $userTable = $tm->table('user');
   $userRecord = $userTable->find(1);
   if (!$userRecord) {
       echo "Record does not exists";
   }
   echo get_class($userRecord); // -> Normalist\Synthetic\Synthetic\Record


Alternatively you can use the Synthetic\\Table::findOneBy($predicate) method to specify
the column(s) used to retrieve your record.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $userTable = $tm->table('user');
   $userRecord = $userTable->findOneBy(array('email' => 'test@example.com'));
   if (!$userRecord) {
       echo "Record does not exists";
   }
   echo get_class($userRecord); // -> Normalist\Synthetic\Synthetic\Record

.. note::
   + An exception will be thrown if Synthetic\\Table::findOneBy($predicate) condition matches more than one record.
   + Synthetic\\Table::findOneBy() method accepts any predicates or conditions
     offered by Synthetic\\TableSearch::where() method, see :ref:`normalist-predicate-where-method-label`.

   
Although it may be considered as a bad database design, Synthetic\\Table is also able to work with composite primary key 
(when a primary key spans over multiple columns). Just specify the columns and their values as an associative array.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $orderlines = $tm->table('order_line');
   $orderline = $userTable->find(array('order_id' => 1, 'order_line' => 10));

Depending on your preferences you can also use the Synthetic\\Table::findOrFail() or Synthetic\\Table::findOneByOrFail()
versions. Instead of returning a false value when a record have not been found, 
a Normalist\\Synthetic\\Exception\\RecordNotFoundException will be thrown.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   use Normalist\Synthetic\Exception as SE;

   $userTable = $tm->table('user');
   try {
       $userRecord = $userTable->findOrFail(1);
       $userRecord = $userTable->findOneByOrFail(array('email' => 'test@example.com'));
   } catch (SE\RecordNotFoundException $e) {
       echo "Record not found: " . $e->getMessage(); 
   }

Test a record exists
~~~~~~~~~~~~~~~~~~~~

The Synthetic\\Table::exists() method checks whether a record exists. 

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $userTable = $tm->table('user');
   if ($userTable->exists(1)) {
       echo "Record exists";
   }

.. note::
   If you care about performance, keep in mind that using the
   Synthetic\\Table::find() method could be used to check a record exists 
   but will bring some overhead due to record creation. Synthetic\\Table::exists()
   attempt to minimize impact on your database server.

Alternatively you can check on multiple conditions.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $userTable = $tm->table('user');
   if ($userTable->existsBy(array('email' => 'test@example.com')) {
       echo "Record exists";
   }

.. note::
   Synthetic\\Table::existsBy() method accepts any predicates or conditions
   offered by Synthetic\\TableSearch::where() method, see :ref:`normalist-predicate-where-method-label`.

Counting records
~~~~~~~~~~~~~~~~
Synthetic\\Table offers a way to count records based on conditions 

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $userTable = $tm->table('user');
   $count = $userTable->count());
       
   // Alternatively you can count with conditions
   $count = $userTable->countBy(array('country' => 'US'));

.. note::
   Synthetic\\Table::countBy() method accepts any predicates or conditions
   offered by Synthetic\\TableSearch::where() method, see 
   :ref:`normalist-predicate-where-method-label`.

Getting all records
~~~~~~~~~~~~~~~~~~~

To get all the records in a table just use the Synthetic\\Table::all() method.

.. code-block:: php
   :emphasize-lines: 3

   <?php
   $userTable = $tm->table('user');
   $userResultSet = $tm->all();
   
   echo get_class($userResultSet);
   // -> Normalist\Synthetic\ResultSet\ResultSet

   // Alternative 1 : iterating the resultset
   foreach($userResultSet as $record) {
        echo $record->email;
   }

   // Alternative 2 : getting an array version
   $users = $userResultSet->toArray();

.. note::
   Having a ResultSet object brings you a lot of options, you can browse and operate 
   on records, get an array version of the result or automatically get a Json version of it.
   To have a complete overview of the Normalist\\Synthetic\\ResultSet\\ResultSet, have a look to 

Inserting in a table
~~~~~~~~~~~~~~~~~~~~

Synthetic\\Table::insert() method return the newly inserted record on success, or throw
an exception otherwise.

.. code-block:: php
   :emphasize-lines: 12

   <?php
   use Soluble\Normalist\Synthetic\Exception as SE;

   $userTable = $tm->table('user');
   $data = array(
        'username'  => 'Bill',
        'email'     => 'test@example.com',
        'type_id'   => 10
   );

   try {
     $userRecord = $userTable->insert($data); 
   } catch (SE\NotNullException $e) {
        echo "Inserting record failed, one or more columns cannot be null";
   } catch (SE\DuplicateEntryException $e) {
        echo "Inserting record failed due to a duplicate entry";
   } catch (SE\ForeignKeyException $e) {
        echo "Inserting record failed due to a invalid foreign key";
   } catch (SE\ColumnNotFoundException $e) {
        echo "Inserting record failed, one or more columns does not exists in table";
   } catch (SE\RuntimeException $e) {
        echo "Inserting record failed, one or more column can be written";
   }

   // Alternatively you can catch the synthetic ExceptionInterface
   try {
     $userRecord = $userTable->insert($data); 
   } catch (SE\ExceptionInterface $e) {
        echo "Error inserting record: " . get_class($e) . ':' . $e->getMessage();
   }

   echo get_class($userRecord);
   // -> Normalist\Synthetic\Record

   echo $userRecord->user_id;
   // -> will return the auto-incremented id of the newly inserted record


Updating a table
~~~~~~~~~~~~~~~~

Synthetic\\Table::update() update one or more record(s) in a table

.. code-block:: php
   :emphasize-lines: 11

   <?php
   use Soluble\Normalist\Synthetic\Exception as SE;

   $userTable = $tm->table('user');
   $data = array(
        'email'     => 'test@example.com',
   );

   // will update email address of user 1 (primary key) 
   try {
    $affected = $userTable->update($data, 1);
   } catch (SE\ExceptionInterface $e) {
        echo "Update failed with error : " . $e->getMessage();
   }

Alternatively you can update multiple records by specifying a predicate.

.. code-block:: php
   :emphasize-lines: 9-11

   <?php
   use Soluble\Normalist\Synthetic\Exception as SE;
   use Zend\Db\Sql\Where;

   $userTable = $tm->table('user');
   $data = array( 'has_access' => 0 );

   try {
     $affected = $userTable->update($data, function(Where $where) {
        $where->like('email', '%@hotmail.com');
     });
   } catch (SE\ExceptionInterface $e) {
        echo "Update failed with error : " . $e->getMessage();
   }

   echo $affected; 
   // will print the affected number of records (int)

.. note::
   Synthetic\\Table::update() method accepts any predicates or conditions
   offered by Synthetic\\TableSearch::where() method, see :ref:`normalist-predicate-where-method-label`.

Insert OnDuplicateKey update
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Synthetic\\Table::insertOnDuplicateKey() method can be used to replace data when a duplicate
entry is found. 

.. code-block:: php
   :emphasize-lines: 12

   <?php
   use Soluble\Normalist\Synthetic\Exception as SE;

   $userTable = $tm->table('user');
   $data = array(
        'first_name'  => 'Bill',
        'last_name'   => 'Joy',
        'email'       => 'test@example.com' // unique !!!
   );

   try {
     $userRecord = $userTable->insertOnDuplicateKeyUpdate($data, $exclude=array('email')); 
   } catch (SE\ExceptionInterface $e) {
        echo "Error : " . get_class($e) . ':' . $e->getMessage();
   }

   echo get_class($userRecord);
   // -> Normalist\Synthetic\Record

   echo $userRecord->username;
   // -> will print 'Bill'

The corresponding sql will be :

.. code-block:: mysql

   INSERT INTO `user` (`first_name`, `last_name`, `email`) 
   VALUES ('Bill', 'Joy', 'test@example.com') 
   ON DUPLICATE KEY UPDATE 
      `first_name` = 'Bill',
      `last_name` = 'Joy'

.. note::
   Synthetic\\Table::insertOnDuplicateKey($data, $exclude) $exclude parameter is optional. By default
   the primary key will be removed in the update part of the query. 
   If you have other unique keys in the table, it may make sense to specify them as well.



Deleting records
~~~~~~~~~~~~~~~~

Synthetic\\Table::delete() delete a record based on primary key value.
The Synthetic\\Table::deleteOrFail() version throws a Soluble\\Normalist\\Synthetic\\Exception\\RecordNotFoundException
in case the record does not exists.

.. code-block:: php
   :emphasize-lines: 4,12

   <?php
   use Soluble\Normalist\Synthetic\Exception as SE;

   $affected = $tm->table('user')->delete(10);
   
   echo $affected;
   // will print the number of affected rows (int)
   // due to possible cascading behaviour, this result may
   // be greater than 1

   try {
      $affected = $tm->table('user')->deleteOrFail(10);
   } catch (SE\RecordNotFoundException $e) {
      echo "Error, cannot delete record 10 it does not exists";
   }
    

Alternatively you can delete multiple records by specifying a predicate.

.. code-block:: php
   :emphasize-lines: 5-7

   <?php
   use Zend\Db\Sql\Where;

   $userTable = $tm->table('user');
   $userTable->deleteBy(function (Where $where) {
        $where->like('email', '%@hotmail.com');
   });

.. note::
   Synthetic\\Table::deleteBy() method accepts any predicates or conditions
   offered by Synthetic\\TableSearch::where() method, see :ref:`normalist-predicate-where-method-label`.


Synthetic\\Record
+++++++++++++++++

Synthetic\\Record focus on record operations and 

Getting a new record
~~~~~~~~~~~~~~~~~~~~
To have a fresh new record simply call the Synthetic\\Table::record() method.

.. code-block:: php
   :emphasize-lines: 5-7

   <?php

   $userTable = $tm->table('user');
   $newRecord = $userTable->record();
   $newRecord->first_name = 'Bill';
   
   // or alternatively, you can fill the record with array values

   $initial_data = array('email' => 'test@example.com', 'first_name' => 'Bill');
   $newRecord = $userTable->record($initial_data);
   echo $newRecord->first_name;
   // Will print 'Bill'

Accessing values
~~~~~~~~~~~~~~~~

Based on your preferences you can access the record properties (values) as an array 
(it implements ArrayAccess interface) or simply with through magic getter/setter.

To have a json or array version of the record, simply call the Synthetic\\Record::toJson()
and Synthetic\\Record::toArray() methods.

.. code-block:: php
   :emphasize-lines: 5-7

   <?php

   $userTable = $tm->table('user');
   $user = $userTable->find(1);

   // ArrayAccess
   $email = $user["email"];
   $user["email"] = 'test@example.com';

   // Magic getter/setter
   $email = $user->email;
   $user->email = 'test@example.com';

   // in JSON
   $json = $user->toJson();

   // as Array
   $array = $user->toArray();


Saving a record
~~~~~~~~~~~~~~~

Synthetic\\Record::save() will detect insert or update operation and ensure
record is saved in database

.. code-block:: php
   :emphasize-lines: 5-7

   <?php

   $userTable = $tm->table('user');
   $user = $userTable->find(1);
   $user->email = 'test@example.com';
   $user->save();

Deleting a record
~~~~~~~~~~~~~~~~~
   
.. code-block:: php
   :emphasize-lines: 5-7

   <?php

   $userTable = $tm->table('user');
   $user = $userTable->find(1);
   $user->delete();



Synthetic\\TableSearch
++++++++++++++++++++++

Synthetic\\TableSearch is one of the most powerful feature of Normalist and makes your searches a breeze.


Getting a Synthetic\\TableSearch
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

TableSearch is available through a Synthetic\\Table object. Just call the Synthetic\\Table::search() method. 

.. code-block:: php
   :emphasize-lines: 4

    <?php
    $tm = new TableManager($adapter);
    $userTable = $tm->table('user');
    $search = $userTable->search();
    echo get_class($search);
    // -> Normalist\Synthetic\Table\TableSearch

.. _normalist-predicate-where-method-label:

Searching records
~~~~~~~~~~~~~~~~~

As a basic example, conditions or predicates can be given as an array.

.. code-block:: php
   :emphasize-lines: 4-11

    <?php
    $tm = new TableManager($adapter);
    $userTable = $tm->table('user');
    $results = $userTable->search()
                         ->where(array(
                                    'email' => 'test@example.com', 
                                    'login' => 'Bill'
                                  )
                                )
                         ->orWhere(array('login' => 'Steve'))
                         ->limit(10)
                         ->toArray();            
 
    echo get_type($results);
    // -> array

The query executed will be similar to :

.. code-block:: mysql

   SELECT `user`.* 
   FROM `user` 
   WHERE `email` = 'test@example.com' 
     AND `login` = 'Bill'
      OR `login` = 'Steve'
   LIMIT 10

Alternatively you can use PHP 5.3 closures to get the job done.

.. code-block:: php
   :emphasize-lines: 6-26

    <?php
    use Zend\Db\Sql\Where;

    $tm = new TableManager($adapter);
    $search = $tm->table('user')->search();
    $search->where(function (Where $where) {
        
        $where->like('email', '%@example.com');
        
        $where->in('country', array('FR', 'US'))
              ->between('birth_date', 1970, 2001);

        $where->lessThan('birth_date', 1980)
              ->and
              ->greaterThan('birth_date', 2010);

        $where->isNotNull('zipcode');

        $where->or
                 ->nest
                   ->equalsTo('name', 'Bill')
                   ->or->like('last_name', '%Gates%')
                 ->unnest

        $where->like('first_name', "John%");
    })->limit(10);

    $results = $search->execute();
    echo get_class($results);
    // -> Normalist\Synthetic\ResultSet\ResultSet


The corresponding SQL will be :

.. code-block:: mysql

   SELECT `user`.*
   FROM `user`
   WHERE `email` LIKE '%@example.com' 
     AND `country` IN ('FR', 'US') 
     AND `birth_date` BETWEEN '1970' AND '2001' 
     AND `birth_date` < '1980' AND `birth_date` > '2010' 
     AND `zipcode` IS NOT NULL 
      OR (`name` = 'Bill' OR `last_name` LIKE '%Gates%')
     AND `first_name` LIKE 'John%'
   LIMIT 10


.. note::
   TableSearch internally relies on the wonderful Zend\\Db\\Sql\\Select component. 
   This manual does not cover all possible options offered by the Select object. 
   For further information, have a look at the `official documentation <http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html#zend-db-sql-select>`_

Another possibility is to use raw conditions, but be cautious of possible 
sql injections. Always quote your values and identifiers !!!

.. code-block:: php
   :emphasize-lines: 6-25

    <?php
    $tm = new TableManager($adapter);
    $platform = $tm->getDbAdapter()->getPlatform();
    echo get_class($platform);
    // -> Zend\Db\Adapter\Platform\PlatformInterface

    $search = $tm->table('user')->search();
    $last_name = $platform->quoteValue($_GET['last_name']);
    $id        = $platform->quoteValue($_GET['id']);
    $search->where("(last_name =  or id = $id) and flag_active = 1");

.. warning::
   Normalist ensures that values are automatically quoted and prevents sql injections.
   Using raw conditions should be used with caution as no automatic quoting is done.


Using limit and offsets
~~~~~~~~~~~~~~~~~~~~~~~

Synthetic\\TableSearch::limit() and Synthetic\\TableSearch::offset() can be used to limit the results.

.. code-block:: php
   :emphasize-lines: 9

    <?php
    use Zend\Db\Sql\Where;

    $tm = new TableManager($adapter);
    $search = $tm->table('user')->search();
    $search->where(function(Where $where) {
        $where->like("email", "%@hotmail.com");
    });
    $search->limit(10)->offset(10);
    $results = $search->execute();
    


Specify columns
~~~~~~~~~~~~~~~
Synthetic\\TableSearch::columns() allows to specify columns to retrieve

.. code-block:: php
   :emphasize-lines: 5-8

    <?php

    $tm = new TableManager($adapter);
    $search = $tm->table('user')->search();
    $search->columns(array(
                    'user_id', 
                    'aliased_column' => 'email'
                    )
    );
    $result = $search->execute();
    var_dump(result->toArray());
    // array(
    //   0 => array('user_id' => 1, 'aliased_column' => 'test@example.com'),
    //   ...
    // )

    // The following iterable behaviour will fail due
    // to incomplete column definition of Record.
    // A Soluble\Normalist\Synthetic\Exception\LogicException will be thrown
    foreach($result as $record) {
        // Never reached
    }


Will execute the following sql :

.. code-block:: mysql

   SELECT 
         `user_id` AS `user_id`, 
         `email`   AS `aliased_column` 
   FROM `user` 
   

.. warning::
   
   If you modify the columns in the Synthetic\\TableSearch, it may happen
   that Record creation through the Iterator won't be possible due to incomplete
   column definition. Iterating through the ResultSet to get Records will throw 
   a Synthetic\\Exception\\LogicException to prevent undefined behaviour. 

    
Join multiple tables
~~~~~~~~~~~~~~~~~~~~

Synthetic\\TableSearch supports INNER JOIN, LEFT OUTER JOIN and RIGHT OUTER join methods through the
method ::join(), ::joinLeft() and ::joinRight();

.. code-block:: php
   :emphasize-lines: 6,9-13

   <?php
   $tm = new TableManager($adapter);
   $search = $tm->table('user')->search();        
   
   $results = $search
        ->join('country', 'user.country_id = country.country_id')
        ->where(function (Where $where) {
              $where->like('email', '%@example.com');
              $where->nest
                        ->like('country.name', 'United%')
                      ->or
                        ->isNull('country.name')
                   ->unnest;

         })->execute();

Will produce the following SQL:

.. code-block:: mysql

   SELECT `user`.* 
   FROM `user` 
   INNER JOIN `country` ON `user`.`country_id` = `country`.`country_id` 
   WHERE 
        `email` LIKE '%@example.com' 
     AND 
       (`country`.`name` LIKE 'United%' OR `country`.`name` IS NULL)
   
Alternatively a good practice is to alias your tables.

.. code-block:: php
   :emphasize-lines: 11,16
    
   <?php
   use Zend\Db\Sql\Expression;

   $tm = new TableManager($adapter);
   
   $categTable = $tm->table('product_category');
   
   // During the search the 'pc' table alias will be used
   // to refer to the 'product_category' table

   $search = $categTable->search('pc');

   // The 'pc18' table alias will be used to reference 
   // the product_category_translation table

   $search->joinLeft(array('pc18' => "product_category_translation"), "pc18.category_id = pc.category_id")
          ->where(function (Where $where) {
                $where->nest->equalTo('pc18.lang', 'fr')->or->isNull('pc18.lang')->unnest;
            })

   // An advanced example of how we can retrieve columns with table alias
   $search->prefixedColumns(array(
                    'pc.category_id',
                    'pc.title', 
                    'translated_title' => 'pc18.title', 
                    'auto_title' => new Expression('COALESCE(pc18.title, pc.title)')
                ))->limit(10);                    
                
        
        
   $results = $search->execute()->toArray();
   var_dump($results);
   // -> could dump
   // ['category_id' => 1, 'title' => 'GSM', 'translated_title' => null, 'auto_title' => 'GSM']
   // ['category_id' => 1, 'title' => 'PC', 'translated_title' => 'Ordinateur', 'auto_title' => 'Ordinateur'] 

                
Will produce the following SQL :

.. code-block:: mysql

   SELECT `pc`.`category_id` AS `category_id`, 
          `pc`.`title` AS `title`, 
          `pc18`.`title` AS `translated_title`, 
          COALESCE(pc18.title, pc.title) AS `auto_title` 
   FROM `product_category` AS `pc` 
   LEFT JOIN `product_category_translation` AS `pc18` 
        ON `pc18`.`category_id` = `pc`.`category_id` 
   WHERE (`pc18`.`lang` = 'it' OR `pc18`.`lang` IS NULL) 
   LIMIT '10'


Grouping 
~~~~~~~~

Synthetic\\TableSearch offers group() and having() methods. 
The following code is taken from the default Wordpress database to illustrate
an example of grouping.

.. code-block:: php
   :emphasize-lines: 9-12,16

    <?php
    use Zend\Db\Sql\Having;
    use Zend\Db\Sql\Expression;

    $tm = $this->tableManager;
    $search = $tm->table("wp_posts")->search('p');        

    $search->joinLeft(array('c' => "wp_comments"), "c.comment_post_ID = p.ID")
           ->where(function (Where $where) {
                $where->equalTo('post_status', 'publish');
             })                
           ->group(array('post_id', 'post_title'))
           ->having(function(Having $having) {
                $having->greaterThanOrEqualTo('count_comment', 1);
             })
           ->order(array(
                'count_comment DESC',
                'p.post_date DESC')
             ) 
           ->prefixedColumns(array(
                    'post_id'       => 'p.ID',
                    'post_title'    => 'p.post_title',
                    'count_comment' => new Expression('COUNT(c.comment_ID)') 
                ));

    $json = $search->toJson();


This search will produce the following SQL:

.. code-block:: mysql

   SELECT `p`.`ID` AS `post_id`, 
          `p`.`post_title` AS `post_title`, 
          COUNT(c.comment_ID) AS `count_comment` 
   FROM `wp_posts` AS `p` 
   LEFT JOIN `wp_comments` AS `c` ON `c`.`comment_post_ID` = `p`.`ID` 
   WHERE `post_status` = 'publish' 
   GROUP BY `post_id`, `post_title` 
   HAVING `count_comment` >= '1' 
   ORDER BY `count_comment` DESC, `p`.`post_date` DESC 

Synthetic\\ResultSet
++++++++++++++++++++

Getting data
~~~~~~~~~~~~



Synthetic\\Transactions
+++++++++++++++++++++++

Transactions are provided by the Synthetic\\TableManager object.

Transaction example
~~~~~~~~~~~~~~~~~~~

.. code-block:: php
   :emphasize-lines: 6,14,17

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