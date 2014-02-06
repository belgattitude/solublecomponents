<?php
namespace Soluble\Normalist\Synthetic;

use Soluble\Normalist\Exception;


use ArrayAccess;

class Record implements ArrayAccess
{
    /**
     *
     * @var \ArrayObject
     */
    protected $data;

    /**
     *
     * @var string
     */
    protected $tableName;

    /**
     *
     * @var \Soluble\Normalist\Synthetic\Table
     */
    protected $table;


    /**
     *
     * @var string
     */
    protected $primary_keys;

    /**
     *
     * @var boolean
     */
    protected $clean;

    /**
     *
     * @var array
     */
    protected $_securedFieldForArrayAccess;
    
    /**
     *
     * @param \Soluble\Normalist\Synthetic\Table $table
     * @param array $data
     */
    public function __construct(Table $table, array $data, $check_columns=true)
    {

        $this->_securedFieldForArrayAccess = array();
        $this->_securedFieldForArrayAccess['table'] = $table;
        $this->_securedFieldForArrayAccess['dirty'] = false;
        $this->_securedFieldForArrayAccess['deleted'] = false;
        $this->_securedFieldForArrayAccess['primary_keys'] = $table->getPrimaryKeys();

        $this->primary_keys = $table->getPrimaryKeys();
        
        $this->setData($data);
    }

    /**
     *
     * @param array $data
     * @param boolean $check_columns
     * @throws Exception\InvalidColumnException     
     * @throws Exception\LogicException when the record has been deleted
     * @return Record
     */
    public function setData($data, $check_columns=true)
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        $d = new \ArrayObject();
        $ci = $this->getTable()->getColumnsInformation();
        $columns = array_keys($ci);
        foreach ($data as $column => $value) {
            if (in_array($column, $columns)) {
                $d->offsetSet($column, $value);
            } elseif ($check_columns) {
                throw new Exception\InvalidColumnException("Column '$column' does not exists in table '$table'");
            }
        }
        $this->_securedFieldForArrayAccess['data'] = $d;
        return $this;
    }

    /**
     * Return an array version of the record
     * 
     * @throws Exception\LogicException when the record has been deleted
     * @return array
     */
    public function toArray()
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        return (array) $this->_securedFieldForArrayAccess['data'];
    }

    
    /**
     * Tell whether this record is dirty
     * 
     * @throws Exception\LogicException when the record has been deleted 
     * @return boolean
     */
    public function isDirty()
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        return $this->_securedFieldForArrayAccess['dirty'];
    }

    /**
     * Save the record in database
     * 
     * @throws Exception\LogicException when the record has been deleted 
     * @return \Soluble\Normalist\SyntheticRecord
     */
    public function save()
    {

        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        
        $table   = $this->getTable();
        $primary = $table->getPrimaryKey();
        
        $pkvalue = $this->offsetGet($primary);
        
        if ($pkvalue === null) {

            // INSERT
            $record = $table->insert($this->toArray());

        } else {
            
            // TODO implement dirty records
            $predicate = array($primary => $pkvalue);
            $data = $this->toArray();
            unset($data[$primary]);
            $table->update($data, $predicate);
            $record = $table->findOneBy($predicate);
            
        }

        $this->_securedFieldForArrayAccess['data'] = new \ArrayObject($record->toArray());
        $this->_securedFieldForArrayAccess['dirty'] = false;
        return $this;
    }

    /**
     * Delete a record
     * 
     * @throws Exception\LogicException
     * @return void
     */
    public function delete()
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        
        $table   = $this->getTable();
        $primary = $table->getPrimaryKey();
        $pk_value = $this->offsetGet($primary);
        if ($this->isDirty()) {
            $tableName = $table->getTableName();
            throw new Exception\LogicException("Cannot delete record '$pk_value' on table '$tableName', it is not in clean state (not in saved state in database)");
        }
        $table->delete($pk_value);

        // Destroy references
        unset($this->_securedFieldForArrayAccess['table']);
        unset($this->_securedFieldForArrayAccess['data']);
        unset($this->_securedFieldForArrayAccess['primary_keys']);
        $this->_securedFieldForArrayAccess['deleted'] = true;        
        
    }


    /**
     *
     * @param string $field
     * @return boolean
     */
    public function offsetExists($field)
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        
        return $this->_securedFieldForArrayAccess['data']->offsetExists($field);
    }

    /**
     * Get field value
     * @param string $field
     * @return mixed
     * @throws Exception\FieldNotFoundException
     */
    public function offsetGet($field)
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        
        if (!$this->_securedFieldForArrayAccess['data']->offsetExists($field)) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }
        return $this->_securedFieldForArrayAccess['data']->offsetGet($field);
    }

    /**
     *
     * @param string $field
     * @param mixed $value
     * @throws Exception\LogicException when the record has been deleted 
     * @return \Soluble\Normalist\Record
     */
    public function offsetSet($field, $value)
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        
        $this->_securedFieldForArrayAccess['dirty'] = true;
        $this->_securedFieldForArrayAccess['data']->offsetSet($field, $value);
        return $this;
    }

    /**
     * Always throws a LogicException
     * 
     * @throws Exception\LogicException
     * @param string $field
     * @return void
     */
    public function offsetUnset($field)
    {
        throw new Exception\LogicException("Cannot unset record fields");
    }

    /**
     * Return parent record
     * 
     * @throws Exception\LogicException when the record has been deleted 
     * @throws Exception\RelationNotFoundException 
     * @param string $parent_table
     * @return Record
     */
    public function getParent($parent_table)
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        
        $table = $this->getTable();
        $tableName = $table->getTableName();
        $relations = $table->getTableManager()->getMetadata()->getRelations($tableName);
        //$rels = array();
        foreach($relations as $column => $parent) {
            if ($parent['table_name'] == $parent_table) {
                // @todo, check the case when
                // table has many relations to the same parent
                // we'll have to throw an exception
                $record = $table->getTableManager()->table($parent_table)->findOneBy(array(
                    $parent['column_name'] => $this->offsetGet($column)
                ));
                return $record;
            }
        }
        throw new Exception\RelationNotFoundException("Cannot find parent relation between table '$tableName' and '$parent_table'");
    }


    /**
     * Magic setter
     * 
     * @throws Exception\FieldNotFoundException
     * @throws Exception\LogicException when the record has been deleted 
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function __set($field, $value)
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        
        if (!$this->_securedFieldForArrayAccess['data']->offsetExists($field)) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }
        $this->_securedFieldForArrayAccess['dirty'] = true;
        $this->_securedFieldForArrayAccess['data']->offsetSet($field, $value);
    }

    /**
     * Magical getter
     * 
     * @param string $field
     * @throws Exception\FieldNotFoundException
     * @throws Exception\LogicException when the record has been deleted 
     * @return mixed
     */
    public function __get($field)
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        
        if (!$this->_securedFieldForArrayAccess['data']->offsetExists($field)) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }
        return $this->_securedFieldForArrayAccess['data']->offsetGet($field);
    }
    
    
    /**
     * Return underlying table instance
     * 
     * @throws Exception\LogicException when the record has been deleted 
     * @return Table
     */
    public function getTable()
    {
        if ($this->_securedFieldForArrayAccess['deleted']) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        
        return $this->_securedFieldForArrayAccess['table'];
    }


    /*
    public function __call($method, $arguments)
    {
        if (preg_match('/related/', $method)) {


        }
    }
    */
    
}
