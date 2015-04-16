<?php

namespace Soluble\Japha\Interfaces;

interface JavaObject
{
    
    /**
     * Returns the runtime class of this Object. 
     * The returned Class object is the object that is locked by static synchronized methods of the represented class. 
     * @return JavaObject Java(java.lang.Object)
     */
    public function getClass();
    
}

