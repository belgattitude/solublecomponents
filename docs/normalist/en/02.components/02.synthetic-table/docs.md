---
title: Working with tables
taxonomy:
    category: docs
---

>>> The `Synthetic\Table`(s) provides offer a great level of interaction with your tables. 

### Getting a Synthetic\Table


>>>>> Synthetic tables are not meant to be directly instanciated, always use them through your configured `Synthetic\TableManager` instance.
>>>>> As a reminder, the TableManager implementation is not provided by Normalist, read the [setting up table manager](../../components/table-manager) section for information. 


To get a synthetic table, simply call the `TableManager::table($table)` method with 

| parameter  | type  | description  | 
|---|---|---|
| `$table`  | `string` | The database table name | 
    

```php
<?php
     
// Table manager retrieval example     
$tm = MyExampleSingletonConfig::getTableManager();

$userTable = $tm->table('user');
echo get_class($userTable);
```

### Synthetic\Table usage

#### General methods {lang=fr}


| method  | description  |
|---|---|
| `count()`  | Return the number of record present in the table. |
| `countBy($predicate)`  | Return the number of record matching the `$predicate`. |
| `exists($primary)`  | Return `true` if a the `$primary` value matches a record, `false` otherwise. |
| `existsBy($predicate)`  | Return `true` if the `$predicate` matches at least one record |

#### Record retrieval

| method  | description  |
|---|---|
| `find($primary)`  | Retrieve a specific record based on primary key value `$id`, return a `Synthetic\Record` or `false` if no match.  |
| `findOrFail($primary)`  | Alternative to `find($primary)` but throws a `Soluble\Normalist\Exception\NotFoundException` if no match. |
| `findOneBy($predicate)`  | Return one `Synthetic\Record` based on `$predicate`, `false` if no match. Throws a `Soluble\Normalist\Exception\MultipleMatchesException` when the predicate return multiple results.    |
| `findOneByOrFail($predicate)`  | Alternative to findOneBuy but throws a `Soluble\Normalist\Exception\NotFoundException` if no match.  |  

#### Table operations

| method  | description  |
|---|---|
| `insert($values)`  | Insert a new record based on `$values`, an associative `array` which maps columns and their values. Will throw a `Soluble\Normalist\Exception\ColumnNotFoundException` if a column does not exists in the table. Return a `Synthetic\Record` on success. |
| `update($values, $conditions)`  | Update a record with the given `$values`. `$conditions` can be either a `$primary` or a `$predicate` condition. The first one acting on maximum one record, the second one allowing multiple records updates. Return the number of affected rows. |
| `delete($conditions)`  | Delete one record based on the `$primary` condition. Return the number of affected rows. |

#### Vendor specific



#### Finding a record

To get a specific record just pass the primary key value to the Synthetic\Table::find($pk) method. Synthetic\Table will automatically figure out which is the primary key of the table and fetch your record accordingly to the requested id.

```php
<?php
$userTable = $tm->table('user');
$userRecord = $userTable->find(1);
if (!$userRecord) {
    echo "Record does not exists";
}
echo get_class($userRecord); // -> Normalist\Synthetic\Synthetic\Record
```

Alternatively you can use the Synthetic\Table::findOneBy($predicate) method to specify the column(s) used to retrieve your record.

```php
<?php
$userTable = $tm->table('user');
$userRecord = $userTable->findOneBy(['email' => 'test@example.com']);
if (!$userRecord) {
    echo "Record does not exists";
}
echo get_class($userRecord); // -> Normalist\Synthetic\Synthetic\Record
```

>>>>>> An exception will be thrown if Synthetic\Table::findOneBy($predicate) condition matches more than one record. 
>>>>>>> Synthetic\Table::findOneBy() method accepts any predicates or conditions offered by Synthetic\TableSearch::where() method, see normalist-predicate-where-method-label.

Although it may be considered as a bad database design, Synthetic\Table is also able to work with composite primary key (when a primary key spans over multiple columns). Just specify the columns and their values as an associative array.

```php
<?php
$orderlines = $tm->table('order_line');
$orderline = $userTable->find(['order_id' => 1, 'order_line' => 10]);
```

Depending on your preferences you can also use the Synthetic\Table::findOrFail() or Synthetic\Table::findOneByOrFail() versions. Instead of returning a false value when a record have not been found, a Normalist\Synthetic\Exception\RecordNotFoundException will be thrown.

```php
<?php
use Normalist\Synthetic\Exception as SE;

$userTable = $tm->table('user');
try {
    $userRecord = $userTable->findOrFail(1);
    $userRecord = $userTable->findOneByOrFail(['email' => 'test@example.com']);
} catch (SE\RecordNotFoundException $e) {
    echo "Record not found: " . $e->getMessage(); 
}
```

#### Test record existence


```php 
<?php
$userTable = $tm->table('user');
if ($userTable->exists(1)) {
    echo "Record exists";
}
```

>>>>>> See also Synthetic\Table::findOrFail() whenever you want to retrieve the record if it exists.  

Alternatively you can check on multiple conditions.

```php
<?php
$userTable = $tm->table('user');
if ($userTable->existsBy(['email' => 'test@example.com']) {
    echo "Record exists";
}
```

> **note
>
> Synthetic\Table::existsBy() method accepts any predicates or conditions offered by Synthetic\TableSearch::where() method, see normalist-predicate-where-method-label.

#### Counting records

Synthetic\Table offers a way to count records based on conditions

```php
<?php
$userTable = $tm->table('user');
$count = $userTable->count());

// Alternatively you can count with conditions
$count = $userTable->countBy(['country' => 'US']);
```

> ##note**
>
> Synthetic\Table::countBy() method accepts any predicates or conditions offered by Synthetic\TableSearch::where() method, see normalist-predicate-where-method-label.

#### Getting all records

To get all the records in a table just use the Synthetic\Table::all() method.

```php
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
```

> **note
>
> Having a ResultSet object brings you a lot of options, you can browse and operate on records, get an array version of the result or automatically get a Json version of it. To have a complete overview of the Normalist\Synthetic\ResultSet\ResultSet, have a look to

#### Inserting in a table

Synthetic\Table::insert() method return the newly inserted record on success, or throw an exception otherwise.

```php
<?php
use Soluble\Normalist\Synthetic\Exception as SE;

$userTable = $tm->table('user');
$data = [
     'username'  => 'Bill',
     'email'     => 'test@example.com',
     'type_id'   => 10
];

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
```

#### Updating a table

Synthetic\Table::update() update one or more record(s) in a table

```php
<?php
use Soluble\Normalist\Synthetic\Exception as SE;

$userTable = $tm->table('user');
$data = [
     'email'     => 'test@example.com',
];

// will update email address of user 1 (primary key) 
try {
 $affected = $userTable->update($data, 1);
} catch (SE\ExceptionInterface $e) {
     echo "Update failed with error : " . $e->getMessage();
}
```

Alternatively you can update multiple records by specifying a predicate.

```php
<?php
use Soluble\Normalist\Synthetic\Exception as SE;
use Zend\Db\Sql\Where;

$userTable = $tm->table('user');
$data = [ 'has_access' => 0 ];

try {
  $affected = $userTable->update($data, function(Where $where) {
     $where->like('email', '%@hotmail.com');
  });
} catch (SE\ExceptionInterface $e) {
     echo "Update failed with error : " . $e->getMessage();
}

echo $affected; 
// will print the affected number of records (int)
```

> ##note**
>
> Synthetic\Table::update() method accepts any predicates or conditions offered by Synthetic\TableSearch::where() method, see normalist-predicate-where-method-label.

#### Insert OnDuplicateKey update

Synthetic\Table::insertOnDuplicateKey() method can be used to replace data when a duplicate entry is found.

```php
<?php
use Soluble\Normalist\Synthetic\Exception as SE;

$userTable = $tm->table('user');
$data = [
     'first_name'  => 'Bill',
     'last_name'   => 'Joy',
     'email'       => 'test@example.com' // unique !!!
];

try {
  $userRecord = $userTable->insertOnDuplicateKeyUpdate($data, $exclude=['email']); 
} catch (SE\ExceptionInterface $e) {
     echo "Error : " . get_class($e) . ':' . $e->getMessage();
}

echo get_class($userRecord);
// -> Normalist\Synthetic\Record

echo $userRecord->username;
// -> will print 'Bill'
```

The corresponding sql will be :

```sql
INSERT INTO `user` (`first_name`, `last_name`, `email`) 
VALUES ('Bill', 'Joy', 'test@example.com') 
ON DUPLICATE KEY UPDATE 
   `first_name` = 'Bill',
   `last_name` = 'Joy'
```

> **note
>
> Synthetic\Table::insertOnDuplicateKey($data, $exclude) $exclude parameter is optional. By default the primary key will be removed in the update part of the query. If you have other unique keys in the table, it may make sense to specify them as well.

#### Deleting records

Synthetic\Table::delete() delete a record based on primary key value. The Synthetic\Table::deleteOrFail() version throws a Soluble\Normalist\Synthetic\Exception\RecordNotFoundException in case the record does not exists.

```php
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
```

Alternatively you can delete multiple records by specifying a predicate.

```php
<?php
use Zend\Db\Sql\Where;

$userTable = $tm->table('user');
$userTable->deleteBy(function (Where $where) {
     $where->like('email', '%@hotmail.com');
});
```

> ##note**
>
> Synthetic\Table::deleteBy() method accepts any predicates or conditions offered by Synthetic\TableSearch::where() method, see normalist-predicate-where-method-label.

