<?php

namespace Soluble\Flexstore\Writer;
use Soluble\FlexStore\Source\SourceInterface;
use Soluble\FlexStore\Writer\SendHeaders;
use Soluble\FlexStore\Exception;
use Traversable;

abstract class AbstractWriter {
	
	/**
	 *
	 * @var \Soluble\FlexStore\Source\AbstractSource
	 */
	protected $source; 
	
	/**
	 *
	 * @var array
	 */
	protected $options = array(
		'debug' => false,
		'charset' => 'UTF-8'
	);
	
	/**
	 * 
	 * @param array|Traversable $options
	 */
	function __construct(SourceInterface $source=null, $options=null) {
		if ($source !== null) {
			$this->setSource($source);
		}
		if ($options !== null) {
			$this->setOptions($options);
		}
	}
	
	
	/**
	 * 
	 * @param \Soluble\FlexStore\Source\SourceInterface $source
	 * @return \Soluble\FlexStore\Writer\Json
	 */
	function setSource(SourceInterface $source) {
		$this->source = $source;
		return $this;
	}
	
	
	/**
	 * @return string
	 */
	abstract function getData();
	

	/**
	 * @param SendHeaders $headers
	 * @return void
	 */
	abstract function send(SendHeaders $headers=null);
	
	
	/**
	 * 
	 * @param string $filename
	 * @param string $charset
	 * 
	 */
	public function save($filename, $charset=null)
	{
		$data = $this->getData();
		if ($charset === null) { 
			$charset = $this->options['charset'];
		}
		// UTF-8 : file_put_contents("file.txt", "\xEF\xBB\xBF" . $data);	
		$ret = file_put_contents($filename, $data);
		if (!$ret) {
			throw new \Exception("Filename $filename cannot be written");
		}
		
				
	}
	
	/**
	 * 
	 * @param boolean $debug
	 * @return \Soluble\Flexstore\Writer\AbstractWriter
	 */
	function setDebug($debug=true) {
		$this->options['debug'] = $debug;
		return $this;
	}
	
	
    /**
     * @param  array|Traversable $options
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        foreach ($options as $key => $value) {
            $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $setter)) {
				
                $this->{$setter}($value);
            } elseif (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                throw new Exception\InvalidArgumentException(sprintf(
                    'The option "%s" does not have a matching %s setter method or options[%s] array key',
                    $key, $setter, $key
                ));
            }
        }
        return $this;
    }

    /**
     * Retrieve options representing object state
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
	
}