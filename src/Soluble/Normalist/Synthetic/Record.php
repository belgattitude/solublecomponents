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
     *
     * @var array
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
     * @param array $data
     */
    public function __construct(array $data=array())
    {
        $this->setData($data);
    }

    /**
     * Set record data
     *
     * @param array $data
     * @throws Exception\LogicException when the record has been deleted
     * @return Record
     */
    public function setData(array $data)
    {
        if ($this->state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        if (!$data instanceof ArrayObject) {
//            $data = new ArrayObject($data);
        }
        $this->data = $data;
        $this->state = self::STATE_DIRTY;
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
        if ($this->state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        return (array) $this->data;
    }


    /**
     * Tell whether this record is dirty
     *
     * @throws Exception\LogicException when the record has been deleted
     * @return boolean
     */
    /*
    public function isDirty()
    {
        if ($this->state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
        return ($this->state == self::STATE_DIRTY);
    }
    */


    /**
     *
     * @param string $field
     * @return boolean
     */
    public function offsetExists($field)
    {
        if ($this->state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }
//        return $this->data->offsetExists($field);
        return array_key_exists($field, $this->data);
    }

    /**
     * Get field value
     * @param string $field
     * @return mixed
     * @throws Exception\FieldNotFoundException
     */
    public function offsetGet($field)
    {
        if ($this->state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }

//        if (!$this->data->offsetExists($field)) {
        if (!array_key_exists($field, $this->data)) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }
//        return $this->data->offsetGet($field);
        return  $this->data[$field];
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
        if ($this->state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }

//        $this->data->offsetSet($field, $value);
        $this->data[$field] = $value;
        if ($this->state != self::STATE_NEW) {
            $this->state = self::STATE_DIRTY;
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
        if ($this->state == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }

        unset($this->data[$field]);
//        $this->data->offsetUnset($field);
        if ($this->state != self::STATE_NEW) {
            $this->state = self::STATE_DIRTY;
        }

        return $this;

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
    /*
    public function __set($field, $value)
    {
        if ($this->_securedFieldForArrayAccess['state'] == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }

        if (!$this->_securedFieldForArrayAccess['data']->offsetExists($field)) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }

        $this->_securedFieldForArrayAccess['state'] = self::STATE_DIRTY;
        $this->_securedFieldForArrayAccess['data']->offsetSet($field, $value);
    }
*/
    /**
     * Magical getter
     *
     * @param string $field
     * @throws Exception\FieldNotFoundException
     * @throws Exception\LogicException when the record has been deleted
     * @return mixed
     */
    /*
    public function __get($field)
    {
        if ($this->_securedFieldForArrayAccess['state'] == self::STATE_DELETED) {
            throw new Exception\LogicException("Logic exception, cannot operate on record that was deleted");
        }

        if (!$this->_securedFieldForArrayAccess['data']->offsetExists($field)) {
            throw new Exception\FieldNotFoundException("Cannot get field value, field '$field' does not exists in record.");
        }
        return $this->_securedFieldForArrayAccess['data']->offsetGet($field);
    }
    */



    /**
     *
     * @param string $state
     * @return \Soluble\Normalist\Synthetic\Record
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    public function getState()
    {
        return $this->state;
    }


}
