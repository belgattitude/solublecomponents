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

    public function __construct(ArrayObject $config=null)
    {
        if ($config === null) {
            $this->config = new ArrayObject(array('columns' => new ArrayObject()));
        } else {
            $this->config = $config;
        }
    }
    
    
    /**
     * 
     * @param string $column
     * @param array|ArrayObject $definition
     * @return ColumnModel
     */
    public function addColumn($column, $definition) {
        $this->config['columns'][$column] = $definition;
        return $this;
    }

    /**
     * Set column that must be excluded in getData()
     * 
     * @param array $columns column nams to exclude
     * @throws Exception\InvalidArgumentException     
     * @return ColumnModel
     */
    public function setExcluded(array $excluded_columns, $excluded=true) {
        
        foreach($excluded_columns as $column) {
            $column = trim($column);
            $this->addColumnParam($column, 'excluded', $excluded);
        }
        return $this;
    }
    
    /**
     * Return column that have been excluded 
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
    
    public function setIncludeOnly(array $include_only_columns) 
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
     * @param type $column
     * @param type $key
     * @return type
     * @throws Exception\InvalidArgumentException
     */
    protected function getColumnParam($column, $key) {
        if (!array_key_exists($column, $this->getColumnsConfig())) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Column '$column' does not exists in columnModel");
        }
        
        return $this->config['columns']['params'][$key];
        
    }
    
    
    /**
     * 
     * @return array
     */
    public function getColumns()
    {
        $columns = array();

        foreach($this->getColumnsConfig() as $column => $config) {
            if ($config['params']['excluded'] !== true) {
               $columns[] = $column;     
            } 
        }
        
        return $columns;

    }
    
    
    

    /**
     * Return column definition
     * 
     * @param string $column
     * @return AbstractColumnDefinition
     */
    public function getColumnDefinition($column)
    {
        
        return $this->config['columns'][$column]['definition'];
    }

    
    /**
     * Return column names
     *
     * @return ArrayObject
     */
    public function getColumnsConfig()
    {
        return $this->config['columns'];
    }




}
