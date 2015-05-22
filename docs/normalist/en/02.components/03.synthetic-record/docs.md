---
title: Working with records
taxonomy:
    category: docs
---

### Synthetic\Record

Synthetic\Record focus on record operations and

#### Getting a new record

To have a fresh new record simply call the Synthetic\Table::record() method.

```php
<?php

$userTable = $tm->table('user');
$newRecord = $userTable->record();
$newRecord->first_name = 'Bill';

// or alternatively, you can fill the record with array values

$initial_data = ['email' => 'test@example.com', 'first_name' => 'Bill'];
$newRecord = $userTable->record($initial_data);
echo $newRecord->first_name;
// Will print 'Bill'
```

#### Accessing values

Based on your preferences you can access the record properties (values) as an array (it implements ArrayAccess interface) or simply with through magic getter/setter.

To have a json or array version of the record, simply call the Synthetic\Record::toJson() and Synthetic\Record::toArray() methods.

```php
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
```

#### Saving a record

Synthetic\Record::save() will detect insert or update operation and ensure record is saved in database

```php
<?php

$userTable = $tm->table('user');
$user = $userTable->find(1);
$user->email = 'test@example.com';
$user->save();
```

#### Deleting a record

```php
<?php

$userTable = $tm->table('user');
$user = $userTable->find(1);
$user->delete();
```

