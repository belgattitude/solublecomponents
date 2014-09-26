<?php

namespace Soluble\Flexstore\Writer\Http;


class Response
{
    
    /**
     *
     * @var string
     */
    protected $response;
    
    
    /**
     *
     * @var SimpleHeaders
     */
    protected $headers;
    
    /**
     * 
     * @param string $content
     */
    function __construct($content=null, SimpleHeaders $headers=null)
    {
        if ($content !== null) {
            $this->setContent($content);
        }
        if ($headers !== null) {
            $this->setSimpleHeaders($headers);
        } else {
            $this->headers = new SimpleHeaders();
        }
    }
    
    /**
     * 
     * @param string $content
     * @return Response
     */
    function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Set reponse headers
     * 
     * @param SimpleHeaders $headers
     * @return Response
     */
    function setSimpleHeaders(SimpleHeaders $headers)
    {
        $this->headers = $headers;
        return $this;
    }
    
    /**
     * 
     * @return SimpleHeaders
     */
    function getSimpleHeaders()
    {
        if ($this->headers === null) {
            $this->setSimpleHeaders(new SimpleHeaders());
        }
        return $this->headers;
    }
    
    /**
     * Send the http response including headers
     * @param boolean $die_after exit after reponse output
     */
    function send($die_after=true)
    {
        try {
            
            
        } catch (Exception $ex) {

        }
        
        if ($die_after) {
            die();
        }
    }
}
