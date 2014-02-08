<?php
namespace Soluble\Media;

class BoxDimension
{
    /**
     *
     * @var integer
     */
    protected $width;
    /**
     *
     * @var integer
     */
    protected $height;

    /**
     *
     * @param int $width
     * @param int $heigth
     */
    public function __construct($width=null, $height=null)
    {
        $this->setWidth($width);
        $this->setHeight($height);

    }


    /**
     *
     * @param int $width
     * @return \Soluble\Media\BoxDimension
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     *
     * @param int $height
     * @return \Soluble\Media\BoxDimension
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

}
