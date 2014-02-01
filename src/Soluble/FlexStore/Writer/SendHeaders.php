<?php
namespace Soluble\FlexStore\Writer;

class SendHeaders
{
    /**
     *
     * @var string
     */
    protected $filename;


    /**
     *
     * @var boolean
     */
    protected $force_download;


    /**
     *
     * @var string
     */
    protected $charset;


    /**
     *
     * @var string
     */
    protected $content_type;

    /**
     *
     * @param array $options
     */
    public function __construct(array $options=null)
    {
        if ($options !== null) {
            if (array_key_exists('filename', $options)) {
                $this->setFilename($options['filename']);
            }
            if (array_key_exists('content_type', $options)) {
                $this->setContentType($options['content_type']);
            }
            if (array_key_exists('charset', $options)) {
                $this->setCharset($options['charset']);
            }
        }

    }

    /**
     *
     * @param string $filename
     * @return \Soluble\FlexStore\Writer\SendHeaders
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     *
     * @param boolean $force_download
     * @return \Soluble\FlexStore\Writer\SendHeaders
     */
    public function setForceDownload($force_download=true)
    {
        $this->force_download = $force_download;
        return $this;
    }

    /**
     *
     * @param string $content_type
     * @return \Soluble\FlexStore\Writer\SendHeaders
     */
    public function setContentType($content_type)
    {
        $this->content_type = $content_type;
        return $this;
    }

    /**
     *
     * @param string $charset
     * @return \Soluble\FlexStore\Writer\SendHeaders
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }


    /**
     *
     */
    public function printHeaders()
    {
        if ($this->content_type === null) {
            $content_type = $this->guessContentType($this->filename);
        } else {
            $content_type = $this->content_type;
        }

        header("Content-Type: $content_type", $replace=true);

    }




    /**
     *
     * @param string $filename
     * @return string
     */
    protected function guessContentType($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $map = array(
            'json' => 'application/json'
        );

        if (!array_key_exists($extension, $map)) {
            throw new \Exception("Cannot find mimetype for filename $extension");
        }

        return $map[$extension];


    }


}
