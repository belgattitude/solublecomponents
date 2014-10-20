<?php

namespace Soluble\FlexStore\Renderer;

use ArrayObject;
use Closure;

/**
 *
 * @method void closure(ArrayObject $row)
 */
class ClosureRenderer implements RowRendererInterface
{
    /**
     * @var Closure
     */
    protected $closure;
    
    
    /**
     *
     * @var array
     */
    protected $required_columns;
    
    /**
     *
     * @param Closure $closure
     */
    public function __construct(Closure $closure)
    {
        $this->required_columns = array();
        $this->closure = $closure;
    }
    
    
    /**
     * Magic callable
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        if (is_callable(array($this, $method))) {
            return call_user_func_array($this->$method, $args);
        }
    }
    
    /**
     * @param ArrayObject
     * @return string
     */
    function apply(\ArrayObject $row)
    {
        $this->closure($row);
    }
    
    /**
     * Return the list of columns required in order to use this renderer
     * @return array
     */
    function getRequiredColumns()
    {
        return $this->required_columns;
    }
    
    /**
     * 
     * @param array $required_columns
     */
    function setRequiredColumns(array $required_columns) 
    {
        $this->required_columns = $required_columns;
    }
}
