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

    public function __construct(ArrayObject $columns)
    {
        $this->columns = $columns;
    }
    
    
    public function setInputFilter($column, InputFilterInterface $inputFilter)
    {
        $this->columns[$column]['inputFilter'] = $inputFilter;
    }
    
    public function setValidator($column, ValidatorInterface $inputFilter)
    {
        $this->columns[$column]['validator'] = $inputFilter;
    }
    
    public function setMetadataReader()
    {
        
    }
    
    
    
    

    /**
     *
     * @param string $column
     * @return AbstractColumnDefinition
     */
    public function getColumnDefinition($column)
    {
        return $this->columns[$column];
    }

    /**
     * Return column names
     *
     * @return ArrayObject
     */
    public function getColumns()
    {
        return $this->columns;
    }




}
