---
title: Transactions
taxonomy:
    category: docs
---

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

