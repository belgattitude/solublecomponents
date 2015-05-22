---
title: Searching tables
taxonomy:
    category: docs
---

### Synthetic\TableSearch

Synthetic\TableSearch is one of the most powerful feature of Normalist and makes your searches a breeze.

#### Getting a Synthetic\TableSearch

TableSearch is available through a Synthetic\Table object. Just call the Synthetic\Table::search() method.

```php
<?php
$tm = My\Namespace\CustomClass::getTableManager();
$userTable = $tm->table('user');
$search = $userTable->search();
echo get_class($search);
// -> Normalist\Synthetic\Table\TableSearch
```

#### Searching records

As a basic example, conditions or predicates can be given as an array.

```php
<?php
$tm = My\Namespace\CustomClass::getTableManager();
$userTable = $tm->table('user');
$results = $userTable->search()
                     ->where([
                                'email' => 'test@example.com', 
                                'login' => 'Bill'
                             ]
                            )
                     ->orWhere(['login' => 'Steve'])
                     ->limit(10)
                     ->toArray();            

echo get_type($results);
// -> array
```

The query executed will be similar to :

```sql
SELECT `user`.* 
FROM `user` 
WHERE `email` = 'test@example.com' 
  AND `login` = 'Bill'
   OR `login` = 'Steve'
LIMIT 10
```

Alternatively you can use PHP 5.3 closures to get the job done.

```php
<?php
use Zend\Db\Sql\Where;

$tm = My\Namespace\CustomClass::getTableManager();
$search = $tm->table('user')->search();
$search->where(function (Where $where) {

    $where->like('email', '%@example.com');

    $where->in('country', ['FR', 'US'])
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
```

The corresponding SQL will be :

```sql
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
```

> **note
>
> TableSearch internally relies on the wonderful Zend\Db\Sql\Select component. This manual does not cover all possible options offered by the Select object. For further information, have a look at the [official documentation](http://framework.zend.com/manual/2.2/en/modules/zend.db.sql.html#zend-db-sql-select)

Another possibility is to use raw conditions, but be cautious of possible sql injections. Always quote your values and identifiers !!!

```php
<?php
$tm = My\Namespace\CustomClass::getTableManager();
$platform = $tm->getDbAdapter()->getPlatform();
echo get_class($platform);
// -> Zend\Db\Adapter\Platform\PlatformInterface

$search = $tm->table('user')->search();
$last_name = $platform->quoteValue($_GET['last_name']);
$id        = $platform->quoteValue($_GET['id']);
$search->where("(last_name =  or id = $id) and flag_active = 1");
```

>>>> Normalist ensures that values are automatically quoted and prevents sql injections. Using raw conditions should be used with caution as no automatic quoting is done.

#### Using limit and offsets

Synthetic\TableSearch::limit() and Synthetic\TableSearch::offset() can be used to limit the results.

```php
<?php
use Zend\Db\Sql\Where;

$tm = My\Namespace\CustomClass::getTableManager();
$search = $tm->table('user')->search();
$search->where(function(Where $where) {
    $where->like("email", "%@hotmail.com");
});
$search->limit(10)->offset(10);
$results = $search->execute();
```

#### Specify columns

Synthetic\TableSearch::columns() allows to specify columns to retrieve

```php
<?php

$tm = My\Namespace\CustomClass::getTableManager();
$search = $tm->table('user')->search();
$search->columns([
                'user_id', 
                'aliased_column' => 'email'
                 ])
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
```

Will execute the following sql :

```sql
SELECT 
      `user_id` AS `user_id`, 
      `email`   AS `aliased_column` 
FROM `user` 
```

> **warning
>
> If you modify the columns in the Synthetic\TableSearch, it may happen that Record creation through the Iterator won't be possible due to incomplete column definition. Iterating through the ResultSet to get Records will throw a Synthetic\Exception\LogicException to prevent undefined behaviour.

#### Join multiple tables

Synthetic\TableSearch supports INNER JOIN, LEFT OUTER JOIN and RIGHT OUTER join methods through the method ::join(), ::joinLeft() and ::joinRight();

```php
<?php
$tm = My\Namespace\CustomClass::getTableManager();
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
```

Will produce the following SQL:

```sql
SELECT `user`.* 
FROM `user` 
INNER JOIN `country` ON `user`.`country_id` = `country`.`country_id` 
WHERE 
     `email` LIKE '%@example.com' 
  AND 
    (`country`.`name` LIKE 'United%' OR `country`.`name` IS NULL)
```

Alternatively a good practice is to alias your tables.

```php
<?php
use Zend\Db\Sql\Expression;

$tm = My\Namespace\CustomClass::getTableManager();

$categTable = $tm->table('product_category');

// During the search the 'pc' table alias will be used
// to refer to the 'product_category' table

$search = $categTable->search('pc');

// The 'pc18' table alias will be used to reference 
// the product_category_translation table

$search->joinLeft(['pc18' => "product_category_translation"], "pc18.category_id = pc.category_id")
       ->where(function (Where $where) {
             $where->nest->equalTo('pc18.lang', 'fr')->or->isNull('pc18.lang')->unnest;
         })

// An advanced example of how we can retrieve columns with table alias
$search->prefixedColumns([
                 'pc.category_id',
                 'pc.title', 
                 'translated_title' => 'pc18.title', 
                 'auto_title' => new Expression('COALESCE(pc18.title, pc.title)')
             ])->limit(10);                    



$results = $search->execute()->toArray();
var_dump($results);
// -> could dump
// ['category_id' => 1, 'title' => 'GSM', 'translated_title' => null, 'auto_title' => 'GSM']
// ['category_id' => 1, 'title' => 'PC', 'translated_title' => 'Ordinateur', 'auto_title' => 'Ordinateur'] 
```

Will produce the following SQL :

```sql
SELECT `pc`.`category_id` AS `category_id`, 
       `pc`.`title` AS `title`, 
       `pc18`.`title` AS `translated_title`, 
       COALESCE(pc18.title, pc.title) AS `auto_title` 
FROM `product_category` AS `pc` 
LEFT JOIN `product_category_translation` AS `pc18` 
     ON `pc18`.`category_id` = `pc`.`category_id` 
WHERE (`pc18`.`lang` = 'it' OR `pc18`.`lang` IS NULL) 
LIMIT '10'
```

#### Grouping

Synthetic\TableSearch offers group() and having() methods. The following code is taken from the default Wordpress database to illustrate an example of grouping.

```php
<?php
use Zend\Db\Sql\Having;
use Zend\Db\Sql\Expression;

$tm = $this->tableManager;
$search = $tm->table("wp_posts")->search('p');        

$search->joinLeft(['c' => "wp_comments"], "c.comment_post_ID = p.ID")
       ->where(function (Where $where) {
            $where->equalTo('post_status', 'publish');
         })                
       ->group(['post_id', 'post_title'])
       ->having(function(Having $having) {
            $having->greaterThanOrEqualTo('count_comment', 1);
         })
       ->order([
            'count_comment DESC',
            'p.post_date DESC']
         )
       ->prefixedColumns([
                'post_id'       => 'p.ID',
                'post_title'    => 'p.post_title',
                'count_comment' => new Expression('COUNT(c.comment_ID)') 
            ]);

$json = $search->toJson();
```

This search will produce the following SQL:

```sql
SELECT `p`.`ID` AS `post_id`, 
       `p`.`post_title` AS `post_title`, 
       COUNT(c.comment_ID) AS `count_comment` 
FROM `wp_posts` AS `p` 
LEFT JOIN `wp_comments` AS `c` ON `c`.`comment_post_ID` = `p`.`ID` 
WHERE `post_status` = 'publish' 
GROUP BY `post_id`, `post_title` 
HAVING `count_comment` >= '1' 
ORDER BY `count_comment` DESC, `p`.`post_date` DESC 
```

### Synthetic\ResultSet

#### Getting data

### Synthetic\Transactions

Transactions are provided by the Synthetic\TableManager object.

#### Transaction example

```php
<?php
use Normalist\Synthetic\TableManager;

$tm = My\Namespace\CustomClass::getTableManager();

$tm->transaction()->start();
try {
    $tm->table('post')->update(['title' => 'cool']);
    $tm->table('comment')->delete(1);
    // will throw an Exception\RecordNotFoundException;
    $tm->table('comment')->findOrFail(1);
} catch (\Exception $e) {
    // will rollback any changes made  to the database
    $tm->transaction()->rollback();
    throw $e;
} 
$tm->transaction()->commit();
```

Notes
=====

In a existing project
---------------------

Typical usage scenarios
-----------------------

Normalist has been primarily designed to modernize, secure and empower existing PHP applications. If your project use already a decent ORM such as Doctrine, we recommend you to continue using it.

Portability
-----------

Currently Normalist supports only MySQL or MariaDB databases. Postgres and Oracle could be supported by implementing a specific reader in the project.

Contributing
------------

Project contributions are welcome, check our github repository.

Roadmap
-------

Roadmap for the project will be documented soon
