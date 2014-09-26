<?php

namespace Soluble\FlexStore\Writer\Http;

class SimpleHeaders
{
    const DIPOSITION_INLINE = 'inline';
    const DIPOSITION_ATTACHEMENT = 'attachement';
    
    /**
     * Supported disposition types
     * @var array
     */
    protected $disposition_types = array('inline', 'attachement');
    
    
    /**
     *
     * @var array
     */
    protected $default_params = array(
        'content-type'                  => null,
        'content-type-charset'          => null,
        'content-disposition-filename'  => null,
        'content-disposition-type'      => null, 
        'content-length'                => null
    );
    
    /**
     *
     * @var array
     */
    protected $params;
    
    function __construct()
    {
        $this->params = $this->default_params;
    }
    
    /**
     * 
     * @param string $content_type
     * @param string $charset
     * @return SimpleHeaders
     */
    function setContentType($content_type, $charset=null)
    {
        $this->params['content-type'] = $content_type;
        if ($charset !== null) {
            $this->setCharset($charset);
        }
        return $this;
    }
    
    function getContentType()
    {
        return $this->params['content-type'];
    }
    
    
    /**
     * Set the content disposition filename and type
     * 
     * @param string $filename filename when downloading
     * @param string $content_disposition_type by default attachment
     * @return SimpleHeaders
     */
    function setFilename($filename, $content_disposition_type='attachement')
    {
        $this->params['content-disposition-filename'] = $filename;
        $this->setContentDispositionType($content_disposition_type);
        return $this;
    }
    
    /**
     * Return the content disposition filename
     * 
     * @return string|null
     */
    function getFilename()
    {
        return $this->params['content-disposition-filename'];
    }
    
    /**
     * Set the content type charset
     * 
     * @throws Exception\RuntimeException
     * @param string $charset
     * @return SimpleHeaders
     */
    function setCharset($charset)
    {
        if ($this->getContentType() == '') {
            throw new Exception\RuntimeException(__METHOD__ . " Content-type must be specified prior to setting charset");
        }
        $this->params['content-type-charset'] = $charset;
        return $this;
        
    }
    
    /**
     * 
     * @return string
     */
    function getCharset()
    {
        return $this->params['content-type-charset'];
    }
    
    
    /**
     * Set the preferred content disposition type 'attachement' or 'inline'
     * 
     * @throws Exception\InvalidArgumentException
     * @param string $content_disposition_type
     * @return SimpleHeaders
     */
    function setContentDispositionType($content_disposition_type)
    {
        if (!in_array($content_disposition_type, $this->disposition_types)) {
            $supported = join(',', $this->disposition_types);
            throw new Exception\InvalidArgumentException(__METHOD__ . " Content disposition type '$content_disposition_type' not in supported types: $supported");
        }
        
        $this->params['content-disposition-type'] = $content_disposition_type;
        return $this;
    }
    
    
    /**
     * Return the content disposition type
     * @return string
     */
    function getContentDispositionType()
    {
        return $this->params['content-disposition-type'];
    }
    
    /**
     * 
     * @param int $length
     * @return SimpleHeaders
     */
    function setContentLength($length)
    {
        $this->params['content-length'] = $length;
        return $this;
    }
    
    /**
     * 
     * @return int
     */
    function getContentLength()
    {
        return $this->params['content-length'];
    }
        
    
    function getHeaderLines()
    {
        
        $lines = array();
        if ($this->params['content-type'] !== null) {
            $ct = "Content-Type: " . $this->params['content-type'];
            if ($this->params['content-type-charset'] !== null) {
                $ct .= "; charset=" . $this->params['content-type-charset'];
            }
            $lines[] = $ct;
        }
        
        if ($this->params['content-disposition-type'] !== null) {
            $cd = "Content-Disposition: " . $this->params['content-disposition-type'];
            if ($this->params['content-disposition-filename'] !== null) {
                $cd .= '; filename="' . $this->params['content-disposition-filename'] . '"';
            }
            $lines[] = $cd;
        }
        
        if ($this->params['content-length'] !== null) {
            $lines[] = "Content-Length: " . $this->params['content-length'];
        }
        
        return $lines;
    }
    
    /**
     * Output the headers (php)
     * 
     * @param boolean $replace tells to replace eventual headers
     * @return void
     */
    function outputHeaders($replace=true)
    {
        $lines = $this->getHeaderLines();
        foreach($lines as $line) {
            header($line, $replace);
        }
    }

    
}
