<?php

namespace Soluble\FlexStore\Writer;

use Soluble\FlexStore\Exception;
use Traversable;
use Soluble\FlexStore\Options;
use Soluble\FlexStore\StoreInterface;

abstract class AbstractWriter
{
    /**
     *
     * @var StoreInterface
     */
    protected $store;

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
      * @param StoreInterface|null $store
      * @param array|Traversable|null $options
      */
    public function __construct(StoreInterface $store = null, $options = null)
    {
        if ($store !== null) {
            $this->setStore($store);
        }
        if ($options !== null) {
            $this->setOptions($options);
        }
    }


    /**
     *
     * @param StoreInterface $store
     * @return AbstractWriter
     */
    public function setStore(StoreInterface $store)
    {
        $this->store = $store;
        return $this;
    }


    /**
     * Return data
     * @param Options $options
     * @return string
     */
    abstract public function getData(Options $options = null);





    /**
     * Save content to a file
     *
     * @param string $filename
     * @param string $charset
     * @return void
     *
     */
    public function save($filename, $charset = null)
    {
        $data = $this->getData();
        if ($charset === null) {
            $charset = $this->options['charset'];
        }
        // UTF-8 : file_put_contents("file.txt", "\xEF\xBB\xBF" . $data);

        /*

            $data = file_get_contents($npath);
            $data = mb_convert_encoding($data, 'UTF-8', 'OLD-ENCODING');
            file_put_contents('tempfolder/'.$a, $data);

            Or alternatively, with PHP's stream filters:

            $fd = fopen($file, 'r');
            stream_filter_append($fd, 'convert.iconv.UTF-8/OLD-ENCODING');
            stream_copy_to_stream($fd, fopen($output, 'w'));
         *
         * mb_convert_encoding($data, 'UTF-8', 'auto');
         * mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data));
         */

        $ret = file_put_contents($filename, $data);
        if (!$ret) {
            throw new \Exception("Filename $filename cannot be written");
        }
    }

    /**
     *
     * @param boolean $debug
     * @return AbstractWriter
     */
    public function setDebug($debug = true)
    {
        $this->options['debug'] = $debug;
        return $this;
    }


    /**
     * Set options
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param  array|Traversable $options
     * @return AbstractWriter
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
     * Retrieve options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
