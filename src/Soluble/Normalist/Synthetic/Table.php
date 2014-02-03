<?php

namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Synthetic\Exception;
use Soluble\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate;
use ArrayObject;

class Table
{

    /**
     * Table name
     * @var string
     */
    protected $table;

    /**
     * Prefixed table name or table name if no prefix
     * @var string
     */
    protected $prefixed_table;

    /**
     * Primary key of the table
     * @var string|integer
     */
    protected $primary_key;

    /**
     * Primary keys of the table in case there's a multiple column pk
     * @var string|integer
     */
    protected $primary_keys;

    /**
     * Table alias useful when using join
     * @var string
     */
    protected $table_alias;

    /**
     * @param TableManager
     */
    protected $tableManager;

    /**
     *
     * @var string
     */
    protected $tablePrefix;

    /**
     *
     * @var Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     *
     * @var array
     */
    protected $column_information;

    /**
     *
     * @param array|string $table table name
     * @param \Soluble\Normalist\Synthetic\TableManager $tableManager
     * 
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($table, TableManager $tableManager)
    {
        $this->tableManager = $tableManager;
        $this->sql = new Sql($tableManager->getDbAdapter());
        if (!is_string($table)) {
            throw new Exception\InvalidArgumentException("Table name must be a string");
        }

        $this->table = $table;
        $this->prefixed_table = $this->tableManager->getTablePrefix() . $table;
    }

    /**
     *
     * @return \Soluble\Normalist\Synthetic\TableSearch
     * @throws \Exception
     */
    public function search($table_alias = null)
    {
        return new TableSearch($this->select($table_alias), $this->tableManager);
    }

    /**
     * Return all records in the table
     *
     * @return array
     */
    public function all()
    {
        return $this->search()->toArray();
    }

    /**
     * Find a record
     *
     * @param integer|string|array $id
     *
     * @throws Exception\InvalidArgumentException when id is invalid
     * @throws Exception\PrimaryKeyNotFoundException     
     *
     * @return Record|false
     */
    public function find($id)
    {
        $record = $this->findOneBy($this->getPrimaryKeyPredicate($id));
        return $record;
    }

    /**
     * 
     * @param integer|string|array $id
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\PrimaryKeyNotFoundException
     * @return array predicate
     */
    protected function getPrimaryKeyPredicate($id)
    {
        try {
            $keys = $this->getPrimaryKeys();
        } catch (\Soluble\Db\Metadata\Exception\NoPrimaryKeyException $e) {
            throw new Exception\PrimaryKeyNotFoundException("Cannot find any primary key (single or multiple) on table '{$this->table}'.");
        }
        if (count($keys) == 1) {
            $pk = $keys[0];
            if (!is_scalar($id)) {
                $type = gettype($id);
                throw new Exception\InvalidArgumentException("Invalid table identifier value. Table '{$this->table}' has a single primary key '$pk'. Argument must be scalar, '$type' given");
            }
            $predicate = array($pk => $id);
        } else {
            if (!is_array($id)) {
                $pks = join(',', $keys);
                $type = gettype($id);
                throw new Exception\InvalidArgumentException("Invalid table identifier value. Table '{$this->table}' has multiple primary keys '$pks'. Argument must be an array, '$type' given");
            }

            $matched_keys = array_diff($id, $keys);
            if (count($matched_keys) == count($keys)) {
                $predicate = $matched_keys;
            } else {
                $pks = join(',', $keys);
                $vals = join(',', $matched_keys);
                $type = gettype($id);
                throw new Exception\InvalidArgumentException("Incomplete table identifier value. Table '{$this->table}' has multiple primary keys '$pks', values received '$vals'");
            }
        }
        return $predicate;
    }

    /**
     * Find a record by primary key, throw a NotFoundException if record does not exists
     *
     * @param integer|string|array $id
     *
     * @throws Exception\NotFoundException
     * @throws Exception\InvalidArgumentException when the id is not valid
     * @throws Exception\PrimaryKeyNotFoundException          
     *
     * @return Record
     */
    public function findOrFail($id)
    {
        $record = $this->find($id);
        if ($record === false) {
            throw new Exception\NotFoundException("Cannot find record '$id' in table '$this->table'");
        }
        return $record;
    }

    /**
     * Find a record by unique key
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @throws Exception\ColumnNotFoundException when a column in the predicate does not exists
     * @throws Exception\UnexpectedValueException when more than one record match the predicate
     * @throws Exception\InvalidArgumentException when the predicate is not correct / invalid column
     * 
     * @return Record|false
     */
    public function findOneBy($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        $select = $this->select($this->table);
        $select->where($predicate, $combination);
        try {
            $results = $select->execute()->toArray();
        } catch (\Exception $e) {

            $messages = array();
            $ex = $e;
            do {
                $messages[] = $ex->getMessage();
            } while ($ex = $ex->getPrevious());
            $message = join(', ', array_unique($messages));

            $lmsg = '[' . get_class($e) . '] ' . strtolower($message) . '(code:' . $e->getCode() . ')';

            if (strpos($lmsg, 'column not found') !== false ||
                    strpos($lmsg, 'unknown column') !== false) {
                //"SQLSTATE[42S22]: Column not found: 1054 Unknown column 'media_id' in 'where clause
                $rex = new Exception\ColumnNotFoundException($message);
                throw $rex;
            } else {

                $sql_string = $select->getSqlString($this->sql->getAdapter()->getPlatform());
                $iqex = new Exception\InvalidArgumentException("$message - $sql_string");
                throw $iqex;
            }
        }

        if (count($results) == 0)
            return false;
        if (count($results) > 1)
            throw new Exception\UnexpectedValueException("Table::findOneBy return more than one record");
        
        return $this->newRecord($results[0], false);
        
        
    }

    /**
     * Find a record by unique key and trhow an exception id record cannot be found
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @throws Exception\NotFoundException when the record is not found
     * @throws Exception\ColumnNotFoundException when a column in the predicate does not exists
     * @throws Exception\UnexpectedValueException when more than one record match the predicate
     * @throws Exception\InvalidArgumentException when the predicate is not correct / invalid column
     *
     * @return Record
     */
    public function findOneByOrFail($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        $record = $this->findOneBy($predicate, $combination);
        if ($record === false) {
            throw new Exception\NotFoundException("Cannot findOneBy record in table '$this->table'");
        }
        return $record;
    }

    /**
     * Count the number of record in table
     * 
     * @return int number of record in table
     */
    public function count()
    {
        return $this->countBy(true);
    }

    /**
     * Find a record by unique key
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * 
     * @return int number of record matching predicates
     */
    public function countBy($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {

        $result = $this->select()
                        ->columns(array('count' => new Expression('count(*)')))
                        ->where($predicate, $combination)
                        ->execute()->toArray();
        $count = $result[0]['count'];
        return (int) $result[0]['count'];
    }

    /**
     * Test if a record exists
     *
     * @param integer|string|array $id
     *
     * @throws Exception\InvalidArgumentException when the id is invalid
     * @throws Exception\PrimaryKeyNotFoundException          
     *
     * @return boolean
     */
    public function exists($id)
    {
        $result = $this->select()->where($this->getPrimaryKeyPredicate($id))
                        ->columns(array('count' => new Expression('count(*)')))
                        ->execute()->toArray();

        return ($result[0]['count'] > 0);
    }

    /**
     * Test if a record exists by a predicate
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @throws Exception\InvalidArgumentException when the predicate is not correct
     *
     * @return boolean
     */
    public function existsBy($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {

        try {
            $select = $this->select()->where($predicate, $combination)
                    ->columns(array('count' => new Expression('count(*)')));
            $result = $select->execute()->toArray();
        } catch (\Exception $e) {
            throw new Exception\InvalidArgumentException("Table::existsBy(), invaid usage ({$e->getMessage()})");
        }

        return ($result[0]['count'] > 0);
    }

    /**
     * Get a Soluble\Db\Select object
     *
     * @param string $table_alias useful when you want to join columns
     * @return \Soluble\Db\Sql\Select
     */
    public function select($table_alias = null)
    {
        $prefixed_table = $this->getTableName($this->table);
        $select = new Select();
        $select->setDbAdapter($this->tableManager->getDbAdapter());
        if ($table_alias === null) {
            $table_spec = $this->table;
        } else {
            $table_spec = array($table_alias => $prefixed_table);
        }
        $select->from($table_spec);
        return $select;
    }

    /**
     * Delete a record by primary key value
     *
     * @param integer|string|array $id primary key value
     *
     * @throws Exception\InvalidArgumentException if $id is not valid
     *
     * @return int the number of affected rows (maybe be greater than 1 with trigger or cascade)
     */
    public function delete($id)
    {
        return $this->deleteBy($this->getPrimaryKeyPredicate($id));
    }

    /**
     * Delete a record by predicate
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @return boolean
     */
    public function deleteBy($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        $delete = $this->sql->delete($this->prefixed_table)
                ->where($predicate);

        $statement = $this->sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();
        $affected = $result->getAffectedRows();
        return $affected;
    }

    /**
     * Delete a record or throw an Exception
     *
     * @param integer|string|array $id primary key value
     *
     * @throws Exception\InvalidArgumentException if $id is not valid 
     * @throws Exception\NotFoundException if record does not exists
     *
     * @return Table
     */
    public function deleteOrFail($id)
    {
        $deleted = $this->delete($id);
        if ($deleted == 0) {
            throw new Exception\NotFoundException("Cannot delete record '$id' in table '$this->table'");
        }
        return $this;
    }

    /**
     * Insert data into table
     *
     * @param array|ArrayObject $data
     *
     * @throws Exception\InvalidArgumentException when data is not an array or an ArrayObject
     * @throws Exception\ColumnNotFoundException when $data contains columns that does not exists in table
     * @throws Exception\ForeignKeyException when insertion failed because of an invalid foreign key
     * @throws Exception\DuplicateEntryException when insertion failed because of an invalid foreign key
     * @throws Exception\NotNullException when insertion failed because a column cannot be null
     * @throws Exception\RuntimeException when insertion failed for another reason
     * 
     * @return \Soluble\Normalist\Synthetic\Record
     */
    public function insert($data)
    {
        if ($data instanceof \ArrayObject) {
            $d = (array) $data;
        } elseif (is_array($data)) {
            $d = $data;
        } else {
            $type = gettype($data);
            throw new Exception\InvalidArgumentException("Table::insert(data) expects data to be array or ArrayObject. Type receive '$type'");
        }

        $diff = array_diff_key($d, $this->getColumnsInformation());
        if (count($diff) > 0) {
            $msg = join(',', array_keys($diff));
            throw new Exception\ColumnNotFoundException("Table::insert(data), cannot insert columns '$msg' does not exists in table {$this->table}.");
        }


        $insert = $this->sql->insert($this->prefixed_table);
        $insert->values($d);

        try {
            $statement = $this->sql->prepareStatementForSqlObject($insert);
            $result = $statement->execute();
        } catch (\Exception $e) {

            // In ZF2, PDO_Mysql and MySQLi return different exception,
            // attempt to normalize by catching one exception instead
            // of RuntimeException and InvalidQueryException

            $messages = array();
            $ex = $e;
            do {
                $messages[] = $ex->getMessage();
            } while ($ex = $ex->getPrevious());
            $message = join(', ', array_unique($messages));

            $lmsg = '[' . get_class($e) . '] ' . strtolower($message) . '(code:' . $e->getCode() . ')';


            if (strpos($lmsg, 'cannot be null') !== false) {
                // Integrity constraint violation: 1048 Column 'non_null_column' cannot be null
                $rex = new Exception\NotNullException($message, $e->getCode(), $e);
                throw $rex;
            } elseif (strpos($lmsg, 'duplicate entry') !== false) {
                $rex = new Exception\DuplicateEntryException($message, $e->getCode(), $e);
                throw $rex;
            } elseif (strpos($lmsg, 'constraint violation') !== false ||
                    strpos($lmsg, 'foreign key') !== false) {
                $rex = new Exception\ForeignKeyException($message, $e->getCode(), $e);
                throw $rex;
            } else {
                $sql_string = $insert->getSqlString($this->sql->getAdapter()->getPlatform());
                $iqex = new Exception\RuntimeException($message . "[$sql_string]", $e->getCode(), $e);
                throw $iqex;
            }
        }

        $pks = $this->getPrimaryKeys();
        $nb_pks = count($pks);
        if ($nb_pks > 1) {
            // In multiple keys there should not be autoincrement value
            $id = array();
            foreach ($pks as $pk) {
                $id[$pk] = $d[$pk];
            }
        } elseif (array_key_exists($pks[0], $d)) {
            // not using autogenerated value
            //$id = $d[$this->getPrimaryKey()];
            $id = $d[$pks[0]];
        } else {
            $id = $this->tableManager->getDbAdapter()->getDriver()->getLastGeneratedValue();
        }
        $record = $this->findOrFail($id);
        return $record;
    }

    /**
     * Update data in a table
     * 
     * @param array|ArrayObject $data
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet     
     * 
     * @throws Exception\InvalidArgumentException when one of the argument is invalid
     * 
     * @return int number of affected rows
     */
    public function update($data, $predicate = null, $combination = Predicate\PredicateSet::OP_AND)
    {
        try {
            $affected_rows = $this->tableManager->update($this->table, $data, $predicate, $combination);
        } catch (Exception\InvalidArgumentException $e) {
            throw new Exception\InvalidArgumentException("Table::update(data) requires data to be array or an ArrayObject");
        }
        return $affected_rows;
    }

    /**
     *
     * @param array|ArrayObject $data
     * @param array|null $duplicate_exclude
     * 
     * @throws Exception\ColumnNotFoundException
     * 
     * @return Record
     */
    public function insertOnDuplicateKey($data, $duplicate_exclude = array())
    {
        $platform = $this->tableManager->getDbAdapter()->platform;

        $primary = $this->getPrimaryKey();

        if ($data instanceOf ArrayObject) {
            $d = (array) $data;
        } else {
            $d = $data;
        }


        $diff = array_diff_key($d, $this->getColumnsInformation());
        if (count($diff) > 0) {
            $msg = join(',', array_keys($diff));
            throw new Exception\ColumnNotFoundException("Table::insertOnDuplicateKey(data), cannot insert columns '$msg' does not exists in table {$this->table}.");
        }


        $insert = $this->sql->insert($this->prefixed_table);
        $insert->values($d);

        $sql_string = $this->sql->getSqlStringForSqlObject($insert);
        $extras = array();
        $excluded_columns = array_merge($duplicate_exclude, array($primary));

        foreach ($d as $column => $value) {

            if (!in_array($column, $excluded_columns)) {
                if ($value === null) {
                    $v = 'NULL';
                } else {
                    $v = $platform->quoteValue($value);
                }
                $extras[] = $platform->quoteIdentifier($column) . ' = ' . $v;
            }
        }
        $sql_string .= ' on duplicate key update ' . join(',', $extras);

        try {
            $stmt = $this->tableManager->getDbAdapter()->query($sql_string, Adapter::QUERY_MODE_PREPARE);
            $result = $stmt->execute();
            unset($stmt);
        } catch (\Exception $e) {

            $messages = array();
            $ex = $e;
            do {
                $messages[] = $ex->getMessage();
            } while ($ex = $ex->getPrevious());
            $msg = join(', ', array_unique($messages));
            $message = "Table::insertOnDuplicateKey failed, $msg [ $sql_string ]";
            throw new Exception\RuntimeException($message);
        }

        if (array_key_exists($primary, $d)) {
            // not using autogenerated value
            $pk_value = $d[$primary];
            $record = $this->find($pk_value);
        } else {

            $id = $this->tableManager->getDbAdapter()->getDriver()->getLastGeneratedValue();

            // This test is not made with id !== null, understand why before changing
            if ($id > 0) {
                $pk_value = $id;
                $record = $this->find($pk_value);
            } else {
                // if the id was not generated, we have to guess on which key
                // the duplicate has been fired
                $unique_keys = $this->tableManager->getMetadata()->getUniqueKeys($this->prefixed_table);
                $data_columns = array_keys($d);
                $found = false;
                // Uniques keys could be
                // array(
                //      'unique_idx' => array('categ', 'legacy_mapping'),
                //      'unique_idx_2' => array('test', 'test2')
                //      )
                //
                
                
                foreach ($unique_keys as $index_name => $unique_columns) {
                    //echo "On duplicate key\n\n $index_name \n";
                    $intersect = array_intersect($data_columns, $unique_columns);
                    if (count($intersect) == count($unique_columns)) {
                        // Try to see if we can find a record with the key
                        $conditions = array();
                        foreach ($intersect as $key) {
                            $conditions[$key] = $d[$key];
                        }
                        $record = $this->findOneBy($conditions);
                        if ($record) {
                            $found = true;
                            $pk_value = $record[$primary];
                            break;
                        }
                    }
                }

                // I cannot write a test case for that
                // It should never happen but in case :
                //@codeCoverageIgnoreStart
                if (!$found) {
                    throw new \Exception("After probing all unique keys in table '{$this->table}', cannot dertermine which one was fired when using on duplicate key.");
                }
                //@codeCoverageIgnoreEnd
            }
        }


        return $record;
    }


    /**
     * Return table relations
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->tableManager->getMetadata()->getRelations($this->prefixed_table);
    }

    /**
     * Return a new record, eventually
     * If $throwException is true, if any non existing column is found
     * an error will be thrown
     *
     * @param string $table table name
     * @param array|ArrayObject $data associative array containing data to insert
     * @param boolean $throwException if true will throw an exception if a column does not exists
     * @return \ArrayObject
     * @throws Exception\InvalidColumnException
     */
    public function newRecord($data = array(), $check_columns = false)
    {
        //return new \ArrayObject($data);
        
        $record = new Record($this, $data, $check_columns);
        return $record;
    }

    /**
     * Return table primary keys
     * @return array
     */
    public function getPrimaryKeys()
    {
        if (!$this->primary_keys) {
            $this->primary_keys = $this->tableManager->getMetadata()->getPrimaryKeys($this->prefixed_table);
        }
        return $this->primary_keys;
    }

    /**
     * Return primary key, if multiple primary keys found will
     * throw an exception
     * @throws Exception
     * @return int|string
     */
    public function getPrimaryKey()
    {
        if (!$this->primary_key) {
            $this->primary_key = $this->tableManager->getMetadata()->getPrimaryKey($this->prefixed_table);
        }
        return $this->primary_key;
    }

    /**
     * Return list of table columns
     * @return array
     */
    public function getColumnsInformation()
    {
        if ($this->column_information === null) {
            $this->column_information = $this->tableManager
                    ->getMetadata()
                    ->getColumnsInformation($this->prefixed_table);
        }
        return $this->column_information;
    }

    /**
     * Return the original table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * Return the prefixed table
     *
     * @return string
     */
    public function getPrefixedTableName()
    {
        return $this->prefixed_table;
    }

}
