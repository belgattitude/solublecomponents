<?php

namespace Soluble\FlexStore\Renderer;

use ArrayObject;
use Closure;

class ClosureRenderer implements RowRendererInterface
{
    /**
     * @var Closure
     */
    protected $closure;
    
    public function __construct(Closure $closure) {
        $this->closure = $closure;
    }
    
    
    public function __call($method, $args)
    {
        if(is_callable(array($this, $method))) {
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
    
}
