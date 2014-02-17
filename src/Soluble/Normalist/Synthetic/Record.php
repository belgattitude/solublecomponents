<?php
namespace Soluble\Normalist\Synthetic;


use ArrayAccess;
use ArrayObject;

class Record implements ArrayAccess
{
    /**
     * Means record is not yet inserted in database
     */
    const STATE_NEW         = 'new';


    /**
     * Means record have been deleted
     */
    const STATE_DELETED     = 'deleted';

    /**
     * Means record comes from database
     * but one of its data have been modified
     */
    const STATE_DIRTY       = 'dirty';

    /**
     * Means record comes from database
     * but none of its data have been modified
     */
    const STATE_CLEAN       = 'clean';

    /**
     * @var \ArrayObject
     */
    protected $_securedFieldForArrayAccess;
    
    /**
     *
     * @var ArrayObject
     */
    protected $data;



    /**
     *
     * @var string
     */
    protected $state;



    /**
     *
     * @param array $data
     * @param Table $table
     */
    public function __construct($data, Table $table)
    {
        $this->_securedFieldForArrayAccess = new \ArrayObject();
        $this->_securedFieldForArrayAccess['table'] = $table;
        //$this->table = $table;
        $this->setData($data);
    }

    /**
     * Delte record
     *
     * @throws Exception\LogicException if Record has already been deleted
     * @return int affected rows can be > 1 if triggers ...
     */
    public function delete()
    {

        $state = $this->getState();
        if ($state == self::STATE_DELETED) {
            throw new Exception\LogicException("Record has already been deleted in database.");
        }
        if ($state == self::STATE_NEW) {
            throw new Exception\LogicException("Record has not already been saved in database.");
        }
        $affected_rows = $this->getTable()->deleteBy($this->getRecordPrimaryKeyPredicate());
        $this->setState(self::STATE_DELETED);
        return $affected_rows;
    }

    /**
     * Save a record in database
     *
     * @throws Exception\LogicException when record has already been deleted
     *
     * @param boolean $validate_datatype if true will ensure record data correspond to column datatype
     *
     * @return Record freshly modified record (from database)
     */
    public function save($validate_datatype=false)
    {

        $state = $this->getState();
        if ($state == self::STATE_DELETED) {
            throw new Exception\LogicException("Record has already been deleted in database.");
        }

        $data = $this->toArray();

        if ($state == self::STATE_NEW) {
            // Means insert
            $new_record = $this->getTable()->insert($data, $validate_datatype);

        } elseif ($state == self::STATE_CLEAN || $state == self::STATE_DIRTY) {
            // Means update
            $predicate = $this->getRecordPrimaryKeyPredicate();
            $this->getTable()->update($data, $predicate, $validate_datatype);
            $new_record = $this->getTable()->findOneBy($predicate);
        } else {
             //@codeCoverageIgnoreStart

            throw new Exception\LogicException(__CLASS__ . '::' . __METHOD . ": Record is not on manageable state.");
             //@codeCoverageIgnoreEnd
        }
        $this->setData($new_record->toArray());
        unset($new_record);
        $this->setState(Record::STATE_CLEAN);
        return $this;
    }


    /**
     * Set record data
     *
     * @param array|ArrayObject $data
     * @throws Exception\InvalidArgumentException when the para
     * @throws Exception\LogicException when the record has been deleted
     * @return Record
     */
    public function setData($data)
    {
        $state = $this->getState();
        if ($state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        if (is_array($data)) {
            $data = new ArrayObject($data);
        } elseif (! $data instanceof ArrayObject) {
            throw new Exception\InvalidArgumentException("Data must be an array of an ArrayObject");
        }
        $this->_securedFieldForArrayAccess['data']  = $data;
        $this->setState(self::STATE_DIRTY);
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
        
        if ($this->getState() == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        return (array) $this->_securedFieldForArrayAccess['data'];
    }
    
    /**
     * Return an json version of the record
     * 
     * @throws Exception\LogicException when the record has been deleted
     * @return string 
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
    



    /**
     *
     * @param string $field
     * @return boolean
     */
    public function offsetExists($field)
    {
        if ($this->getState() == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }

        return array_key_exists($field, $this->_securedFieldForArrayAccess['data']);
    }

    /**
     * Get field value
     * @param string $field
     * @return mixed
     * @throws Exception\FieldNotFoundException
     */
    public function offsetGet($field)
    {
        if ($this->getState() == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }

        if (!array_key_exists($field, $this->_securedFieldForArrayAccess['data'])) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }

        return  $this->_securedFieldForArrayAccess['data'][$field];
    }

    /**
     * Set a field
     * @param string $field
     * @param mixed $value
     * @throws Exception\LogicException when the record has been deleted
     * @return \Soluble\Normalist\Record
     */
    public function offsetSet($field, $value)
    {
        $state = $this->getState();
        if ($state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }


        $this->_securedFieldForArrayAccess['data'][$field] = $value;
        if ($state != self::STATE_NEW) {
            $this->setState(self::STATE_DIRTY);
        }
        return $this;
    }

    /**
     * Unset a field
     *
     * @param string $field
     * @throws Exception\LogicException when the record has been deleted
     * @return \Soluble\Normalist\Record
     */
    public function offsetUnset($field)
    {
        $state = $this->getState();
        if ($state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }

        unset($this->_securedFieldForArrayAccess['data'][$field]);

        if ($state != self::STATE_NEW) {
            $this->setState(self::STATE_DIRTY);
        }

        return $this;

    }


    /**
     * Magic setter
     *
     * @throws Exception\LogicException when the record has been deleted
     * @param string $field
     * @param mixed $value
     * @return void
     */
    
    public function __set($field, $value)
    {

        $state = $this->getState();
        if ($state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }


        $this->_securedFieldForArrayAccess['data'][$field] = $value;
        if ($state != self::STATE_NEW) {
            $this->setState(self::STATE_DIRTY);
        }

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
        if ($this->getState() == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }

        if (!array_key_exists($field, $this->_securedFieldForArrayAccess['data'])) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }

        return  $this->_securedFieldForArrayAccess['data'][$field];
    }



    /**
     *
     * @param string $state
     * @return \Soluble\Normalist\Synthetic\Record
     */
    public function setState($state)
    {
        $this->_securedFieldForArrayAccess['state'] = $state;
        return $this;
    }

    public function getState()
    {
        return $this->_securedFieldForArrayAccess['state'];
    }


    /**
     * Return primary key predicate on record
     *
     *
     * @throws Exception\PrimaryKeyNotFoundException
     * @throws Exception\UnexcpectedValueException
     * @return array predicate
     */
    protected function getRecordPrimaryKeyPredicate()
    {
        // Get table primary keys
        $primary_keys = $this->getTable()->getPrimaryKeys();
        $predicate = array();
        foreach($primary_keys as $column) {
            $pk_value = $this->offsetGet($column);
            if ($pk_value != '') {
                 $predicate[$column] = $pk_value;
            } else {
                throw new Exception\UnexpectedValueException("Cannot find record primary key values. Record has no primary key value set");
            }
        }
        return $predicate;
    }
    
    /**
     * Return originating table
     * @return Table
     */
    function getTable()
    {
        return $this->_securedFieldForArrayAccess['table'];
    }


}
