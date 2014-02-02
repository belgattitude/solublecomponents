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
     */
    public function __construct($table, TableManager $tableManager)
    {
        $this->tableManager = $tableManager;
        $this->sql = new Sql($tableManager->getDbAdapter());
        $this->setTableName($table);
        $this->primary_key = $this->tableManager->getMetadata()->getPrimaryKey($this->prefixed_table);

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
     * Set internal table name
     * @param array|string $table
     */
    protected function setTableName($table)
    {
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
    public function search($table_alias=null)
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
     * @param integer|string $id
     *
     * @throws Exception\InvalidArgumentException when the id is not scalar
     *
     * @return Record|false
     */
    public function find($id)
    {
        if (!is_scalar($id)) {
            $type = gettype($id);
            throw new Exception\InvalidArgumentException("Table::find(id) only accept a scale id (numeric, string,...), type '$type' given");
        }
        $record =  $this->findOneBy(array($this->primary_key => $id));
        return $record;
    }

    /**
     * Find a record by primary key, throw a NotFoundException if record does not exists
     *
     * @param integer|string $id
     *
     * @throws Exception\NotFoundException
     * @throws Exception\InvalidArgumentException when the id is not scalar (string, int, numeric)
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
     * @throws Exception\UnexpectedValueException
     *
     * @return Record|false
     */
    public function findOneBy($predicate, $combination=Predicate\PredicateSet::OP_AND)
    {
        $select = $this->select($this->table);
        $select->where($predicate, $combination);
        $results = $select->execute()->toArray();
        if (count($results) == 0) return false;
        if (count($results) > 1) throw new Exception\UnexpectedValueException("Table::findOneBy return more than one record");
        return $this->makeRecord($this->table, $results[0]);
    }

    /**
     * Find a record by unique key and trhow an exception id record cannot be found
     *
     * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @throws Exception\NotFoundException
     * @throws Exception\UnexpectedValueException
     *
     * @return Record
     */
    public function findOneByOrFail($predicate, $combination=Predicate\PredicateSet::OP_AND)
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
     * @throws Exception\UnexpectedValueException when the returned count is not numeric (should not happen)
     *
     * @return int
     */
    public function count()
    {
        $result = $this->select()
                      ->columns(array('count' => new Expression('count(*)')))
                      ->execute()->toArray();
        $count = $result[0]['count'];
        if (!is_numeric($count)) {
            throw new Exception\UnexpectedValueException("Table::count() return a non numeric value");
        }
        return (int) $result[0]['count'];
    }

    /**
     * Test if a record exists
     *
     * @param integer|string $id
     *
     * @throws Exception\InvalidArgumentException when the id is not scalar
     *
     * @return boolean
     */
    public function exists($id)
    {
        if (!is_scalar($id)) {
            $type = gettype($id);
            throw new Exception\InvalidArgumentException("Table::exists(id) only accept a scale id (numeric, string,...), type '$type' given");
        }
        $result = $this->select()->where(array($this->primary_key => $id))
                      ->columns(array('count' => new Expression('count(*)')))
                      ->execute()->toArray();

        return ($result[0]['count'] == 1);
    }


    /**
     * Get a Soluble\Db\Select object
     *
     * @param string $table_alias useful when you want to join columns
     * @return \Soluble\Db\Sql\Select
     */
    public function select($table_alias=null)
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
     * Delete a record
     *
     * @param integer|strig $id primary key value
     *
     * @throws Exception\InvalidArgumentException if $id is not valid / not a scalar value
     *
     * @return int the number of affected rows (maybe be greater than 1 with trigger or cascade)
     */
    public function delete($id)
    {
        if (!is_scalar($id)) {
            $type = gettype($id);
            throw new Exception\InvalidArgumentException("Table::delete(id) only accept a scale id (numeric, string,...), type '$type' given");
        }

        $delete = $this->sql->delete($this->prefixed_table)
                  ->where(array($this->primary_key => $id));

        $statement = $this->sql->prepareStatementForSqlObject($delete);
        $result    = $statement->execute();
        $affected  = $result->getAffectedRows();
        /**
        Removed in case of trigger, a deletion may provoque
        multiple deletion or a foreign key cascade.
        Test before implementing;
        if ($affected > 1) {
            throw new Exception\UnexpectedValueException("Table delete returned more than one affected row");
        }
         *
         */
        return $affected;
    }

    /**
     * Delete a record or throw an Exception
     *
     * @param integer|strig $id primary key value
     *
     * @throws Exception\InvalidArgumentException if $id is not valid / not a scalar value
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
     * @throws Exception\UnexistentColumnException when $data contains columns that does not exists in table
     * @throws Exception\ForeignKeyException when insertion failed because of an invalid foreign key
     * @throws Exception\DuplicateEntryException when insertion failed because of an invalid foreign key
     * @throws Exception\RuntimeException when insertion failed for another reason
     *
     * @return \Soluble\Normalist\Synthetic\Record
     */
    public function insert($data)
    {
        if ($data instanceOf ArrayObject) {
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
            throw new Exception\UnexistentColumnException("Table::insert(data), cannot insert columns '$msg' does not exists in table {$this->table}.");
        }

        $insert = $this->sql->insert($this->prefixed_table);
        $insert->values($d);

        try {
            $statement = $this->sql->prepareStatementForSqlObject($insert);
            $result    = $statement->execute();
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

            if (strpos($lmsg, 'duplicate entry') !== false ) {
                $rex = new Exception\DuplicateEntryException($message, $e->getCode(), $e);
                throw $rex;
            
            } elseif (strpos($lmsg, 'constraint violation') !== false ||
                strpos($lmsg, 'foreign key') !== false ) {
                $rex = new Exception\ForeignKeyException($message, $e->getCode(), $e);
                throw $rex;
            } else {
                $sql_string = $insert->getSqlString($this->sql->getAdapter()->getPlatform());
                $iqex = new Exception\RuntimeException($message . "[$sql_string]", $e->getCode(), $e);
                throw $iqex;
            }

        }

        if (array_key_exists($this->primary_key, $d)) {
            // not using autogenerated value
            $id = $d['primary'];
        } else {
            $id = $this->tableManager->getDbAdapter()->getDriver()->getLastGeneratedValue();
        }
        $record = $this->find($id);
        if (!$record) {
            throw new Exception\ErrorException("Insert may have failed, cannot retrieve inserted record with id='$id' on table '$table'");
        }
        return $record;
    }

    /**
     *
     * @param array|ArrayObject $data
     * @param array|null $duplicate_exclude
     * @return Record
     * @throws \Exception
     */
    public function insertOnDuplicateKey($table, $data, $duplicate_exclude=array())
    {
        $platform = $this->adapter->platform;
        $prefixed_table = $this->prefixTable($table);
        $primary = $this->getMetadata()->getPrimaryKey($prefixed_table);

        if ($data instanceOf ArrayObject) {
            $d = (array) $data;
        } else {
            $d = $data;
        }


        $insert = $this->sql->insert($prefixed_table);
        $insert->values($d);

        $sql_string = $this->sql->getSqlStringForSqlObject($insert);
        $extras = array();
        $excluded_columns = array_merge($duplicate_exclude, array($primary));
        foreach($d as $column => $value) {
            if (!in_array($column, $excluded_columns)) {
                if ($value === null) {
                    $v = 'NULL';
                } else {
                    $v = $platform->quoteValue($value);
                }
                $extras[] = $platform->quoteIdentifier($column) . ' = ' . $v;
            }
        }
        $sql_string .= ' on duplicate key update ' . join (',', $extras);

        try {
            //$result = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);

            $stmt = $this->adapter->query($sql_string, Adapter::QUERY_MODE_PREPARE);
            $result = $stmt->execute();
            unset($stmt);

        } catch (\Exception $e) {
            $message ="Cannot execute sql [ $sql_string ]";
            throw new Exception\ErrorException($message, $code=1, $e);
        }

        if (array_key_exists($primary, $d)) {
            // not using autogenerated value
            $pk_value = $d[$primary];

        } else {

            $id = $this->adapter->getDriver()->getLastGeneratedValue();

            // This test is not made with id !== null, understand why before changing
            if ($id > 0) {
                $pk_value = $id;
            } else {
                // if the id was not generated, we have to guess on which key
                // the duplicate has been fired
                $unique_keys = $this->getMetadata()->getUniqueKeys($prefixed_table);
                $data_columns = array_keys($d);
                $found = false;
                foreach($unique_keys as $index_name => $unique_columns) {
                    //echo "On duplicate key\n\n $index_name \n";
                    $intersect = array_intersect($data_columns, $unique_columns);
                    if (count($intersect) == count($unique_columns)) {
                        // Try to see if we can find a record with the key
                        $conditions = array();
                        foreach($intersect as $key) {
                            $conditions[$key] = $d[$key];
                        }

                        $record = $this->findOneBy($table, $conditions);
                        if ($record) {
                            $found = true;
                            $pk_value = $record[$primary];
                            break;
                        }
                    }
                }

                if (!$found) {
                    throw new \Exception("After probing all unique keys in table '$table', cannot dertermine which one was fired when using on duplicate key.");
                }
            }
        }

        $record = $this->find($table, $pk_value);
        if (!$record) {
            throw new \Exception("insertOnDuplicateKey cannot retrieve record with $primary=$pk_value");
        } elseif ($record[$primary] != $pk_value) {
            throw new \Exception("System error, returned primary key value is different, check \"$sql_string\"");
        }
        return $record;
    }





    /**
     * @param string $table
     * @param array $data
     * @return Record
     */
    protected function makeRecord($table, $data)
    {
        $record = new Record($this->tableManager, $table, $data);
        return $record;
    }



    /**
     * Return table relations
     * @param string $table
     * @return array
     */
    public function getRelations($table)
    {
        $prefixed_table = $this->prefixTable($table);
        $rel = $this->getMetadata()->getRelations($prefixed_table);
        return $rel;
    }




    /**
     * Return cleaned data suitable for inserting/updating a table
     * If $throwException is true, if any non existing column is found
     * an error will be thrown
     *
     * @param string $table table name
     * @param array|ArrayObject $data associative array containing data to insert
     * @param boolean $throwException if true will throw an exception if a column does not exists
     * @return \ArrayObject
     * @throws Exception\InvalidColumnException
     */
    public function getRecordCleanedData($table, $data, $throwException=false)
    {
        $d = new \ArrayObject();
        $ci = $this->getColumnsInformation($table);
        $columns = array_keys($ci);
        foreach($data as $column => $value) {
            if (in_array($column, $columns)) {
                $d->offsetSet($column, $value);
            } elseif ($throwException) {
                throw new Exception\InvalidColumnException("Column '$column' does not exists in table '$table'");
            }
        }
        return $d;
    }

    /**
     * Return table primary keys
     * @param string $table
     * @return array
     */
    public function getPrimaryKeys($table)
    {
        $prefixed_table = $this->prefixTable($table);
        return $this->getMetadata()->getPrimaryKeys($prefixed_table);
    }

    /**
     * Return primary key, if multiple primary keys found will
     * throw an exception
     * @throws Exception
     * @return int|string
     */
    public function getPrimaryKey($table)
    {
        $prefixed_table = $this->prefixTable($table);
        return $this->getMetadata()->getPrimaryKey($prefixed_table);
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
