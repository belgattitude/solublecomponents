<?php

namespace Soluble\FlexStore\Column;
use Soluble\Db\Metadata\Column\Definition\AbstractColumnDefinition;
use Soluble\FlexStore\Renderer\RendererInterface;
use ArrayObject;

use Zend\Validator\ValidatorInterface;
use Zend\InputFilter\InputFilterInterface;



class ColumnModel
{
    /**
     *
     * @var ArrayObject
     */
    protected $config;
    
    /**
     *
     * @var ArrayObject
     */
    protected $columns;

    /*
     * A columns is
     *  - [price_sale]['translation']['header']         = 
     *  - [price_sale]['translation']['label']          = 
     *  - [price_sale]['translation']['description']    =
     * 
     *  - [price_sale]['display']['renderer']      = Soluble\Renderer\RendererInterface
     *  - [price_sale]['display']['align']         = [left|right|center]
     *  - [price_sale]['display']['width']         = xxx
     * 
     *  - [price_sale]['datatype'] = Soluble\Renderer\RendererInterface
     *  - [price_sale]['datatype']['metadata']   = 'ALL metadata attributes'
     * 
     *  - [price_sale]['validator'] = ['email', 'nospace', 'notblank', 'regex', 'minlength'] 
     */

    public function __construct()
    {
        $this->config = new ArrayObject(array('columns' => new ArrayObject()));
        $this->columns = new ArrayObject();
    }
    
    
    /**
     * 
     * @param string $column
     * @param array|ArrayObject $definition
     * @throws Exception\InvalidArgumentException when column nam is not correct
     * @return ColumnModel
     */
    public function addColumn($column, $definition) {
        if (!is_string($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column name must be a valid string");
        }
        
        $column = trim($column);
        if ($column == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column name cannot be empty");
        }
        $this->config['columns'][$column] = $definition;
        $this->columns->offsetSet($column, $column);
        return $this;
    }

    /**
     * Set column that must be excluded in getData() and getColumns()
     * 
     * @param array $columns column nams to exclude
     * @throws Exception\InvalidArgumentException     
     * @return ColumnModel
     */
    public function setExcluded(array $excluded_columns, $excluded=true) {
        
        foreach($excluded_columns as $column) {
            $column = trim($column);
            $this->addColumnParam($column, 'excluded', $excluded);
            if ($excluded) {
                $this->columns->offsetUnset($column);
            } else {
                $this->columns->offsetSet($column, $column);
            }
        }
        return $this;
    }
    
    /**
     * Return column that have been excluded in getData() and getColumns()
     * 
     * @return array
     */
    public function getExcluded()
    {
        $excluded = array();
        foreach($this->getColumnsConfig() as $column => $config) {
            if ($config['params']['excluded']) {
               $excluded[] = $column;     
            }
        }
        return $excluded;
    }
    

    /**
     * Sort columns in the order specified, columns that exists
     * in the dataset but not in the sorted_columns will be
     * appended to the end
     * 
     * @param array $sorted_columns
     * @return ColumnModel
     */
    public function sortColumns(array $sorted_columns)
    {
        $new_columns = array();
        foreach($sorted_columns as $column) {
            if (!$this->exists($column)) {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists.");
            }
            $new_columns[$column] = $column;
        }

        // Appending eventual non sorted columns at the end
        $new_columns = array_merge($new_columns, (array) $this->columns);
        $this->columns->exchangeArray($new_columns);
        
        return $this;
    }
    
    /**
     * Tells whether a column exists
     * 
     * @param string $column
     * @return boolean
     */
    public function exists($column)
    {
        return array_key_exists($column, $this->config['columns']);
        
    }
    
    /**
     * Exclude all other columns that the one specified
     * Column sort is preserved in getData()
     * 
     * @param array $include_only_columns
     * @param bool $sort automatically apply sortColumns
     * @return ColumnModel
     */
    public function setIncludeOnly(array $include_only_columns, $sorted=true) 
    {
        $columns = $this->getColumnsConfig();
        // trim column
        $include_only_columns = array_map('trim', $include_only_columns);
        
        foreach($columns as $column => $config) {
            if (in_array($column, $include_only_columns)) {
                $this->setExcluded(array($column), false);
            } else {
                $this->setExcluded(array($column), true);
            }
        }
        if ($sorted) {
            $this->sortColumns($include_only_columns);
        } 
        return $this;        
        
    }
    
    
    
    /**
     * Add column paramter
     * @param string $column
     * @param string $key
     * @param mixed $value
     * @throws Exception\InvalidArgumentException
     */
    protected function addColumnParam($column, $key, $value) {
        if (!array_key_exists($column, $this->getColumnsConfig())) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists in columnModel");
        }
        $this->config['columns'][$column]['params'][$key] = $value;
    }
    
    /**
     * 
     * @throws Exception\InvalidArgumentException
     * @param string $column
     * @param string $key
     * @return mixed
     */
    protected function getColumnParam($column, $key) {
        if (!$this->exists($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists in dataset.");
        }
        
        
        return $this->config['columns']['params'][$key];
        
    }
    
    
    /**
     * Return columns
     * 
     * @return array
     */
    public function getColumns()
    {
        
        return array_values((array) $this->columns);
        /*
        $columns = array();

        foreach($this->getColumnsConfig() as $column => $config) {
            if ($config['params']['excluded'] !== true) {
               $columns[] = $column;     
            } 
        }
        
        return $columns;
         * 
         */

    }
    
    
    

    /**
     * Return column definition
     * 
     * @throws Exception\InvalidArgumentException
     * @param string $column
     * @return AbstractColumnDefinition
     */
    public function getColumnDefinition($column)
    {
        if (!$this->exists($column)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists in dataset.");
        }
        return $this->config['columns'][$column]['definition'];
    }

    
    /**
     * Return columns underlying configuration
     *
     * @return ArrayObject
     */
    public function getColumnsConfig()
    {
        return $this->config['columns'];
    }

}
