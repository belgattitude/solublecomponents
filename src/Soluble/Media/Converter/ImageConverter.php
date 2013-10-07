<?php

namespace Soluble\Media\Converter;
use Soluble\Media\BoxDimension;

use Imagine\Imagick\Imagine as ImagickImagine;
use Imagine\Gd\Imagine as GdImagine;

use Imagine\Image\ImageInterface;
use Imagine\Image\Box;

use Zend\Cache\Storage\StorageInterface;


class ImageConverter implements ConverterInterface {
	
	protected $default_quality = 90;
	
	
	/**
	 *
	 * @var boolean
	 */
	protected $cacheEnabled = false;

	/**
	 *
	 * @var Zend\Cache\Storage\StorageInterface
	 */
	protected $cacheStorage;	
	
	/**
	 * 
	 */
	function __construct() {
		
		
	}

	/**
	 * 
	 * @param string $filename
	 * @param \Soluble\Media\BoxDimension $box
	 * @param string $format
	 * @param int $quality
	 * @throws \Soluble\Media\Converter\Exception
	 * @throws \Exception
	 */
	function getThumbnail($filename, BoxDimension $box, $format=null, $quality=null) {
		
		$width    = $box->getWidth();
		$height   = $box->getHeight();
		
		if ($quality === null) $quality = $this->default_quality;
		
		$cache_key = md5("$filename/$width/$height/$quality/$format");
		
		if ($this->cacheEnabled && $this->cacheStorage->hasItem($cache_key)) {
			$cacheMd = $this->cacheStorage->getMetadata($cache_key);
			if ($cacheMd['mtime'] < filemtime($filename)) {
				// invalid cache
				
				$binaryContent = $this->generateThumbnail($filename, $box, $format, $quality);
				$this->cacheStorage->setItem($cache_key, $binaryContent);
			} else {
				$binaryContent = $this->cacheStorage->getItem($cache_key);
			}
		} else {
			$binaryContent = $this->generateThumbnail($filename, $box, $format, $quality);
			
		}
		
		switch ($format) {
			case 'jpg' :
				$content_type = 'image/jpeg';
			case 'png':
				$content_type = 'image/png';
				break;
			case 'gif':
				$content_type = 'image/gif';
				break;
			default:
				throw new \Exception("Unsupported format '$format'");
		}

		header("Content-type: $content_type", true);
		header("Accept-Ranges: bytes", true);
		header("Cache-control: max-age=2592000, public", true);
		header("Content-Disposition: inline; filename=\"$filename\";", true);
		header('Last-Modified: '. gmdate('D, d M Y H:i:s', filemtime($filename)).' GMT', true);
		//header('Date: ' . );
		header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('+10 years')) . ' GMT');
		//header('Content-Disposition: attachment; filename="downloaded.pdf"');
		echo $binaryContent;
		die();
		
	}
	
	protected function generateThumbnail($filename, BoxDimension $box, $format=null, $quality=null) {
		
		$width    = $box->getWidth();
		$height   = $box->getHeight();

		try {
			$imagine = $this->getImagine('imagick');

			if ($imagine instanceof Imagine\Imagick\Imagine) {
				$filter = ImageInterface::FILTER_LANCZOS;
				/**
				 * BESSEL : 53k
				 * LANCZOS: 54.5k
				 * GAUSSIAN: 52k
				 * MITCHELL: 53k
				 jpegtran -optimize 14610.jpg > 14610_test.jpg
				 */
			} else {
				$filter = ImageInterface::FILTER_UNDEFINED;
			}

			$image = $imagine->open($filename);


				// Get dimension by keeping proportions
			$size = $image->getSize();
			$ratio_x = $size->getWidth() / $width;
			$ratio_y = $size->getHeight() / $height;
			$max_ratio = max($ratio_x, $ratio_y);
			$new_width 	= (int) ($size->getWidth() / $max_ratio);
			$new_height = (int) ($size->getHeight() / $max_ratio);

			$newSize = new Box($new_width, $new_height);

			//$image->strip();

			//$image->interlace(ImageInterface::INTERLACE_LINE);

			$image->resize($newSize, $filter);
			$options = array(
				'quality' => $quality,
				'flatten' => true,
				//'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH,
				//'resolution-y' => 72,
				//'resolution-x' => 72,
			);
			
			//var_dump(get_class($image));die();
			$content = $image->get($format, $options);

		} catch (\Exception $e) {
			// ERROR 403 ?
			//var_dump($e);
			//die();
			throw $e;
		}
		return $content;
		
		
	}
	
	
	/**
	 * 
	 * @param string $library
	 * @return 
	 */
	protected function getImagine($library) {
		switch(strtolower($library)) {
			case 'imagick' :
				$imagine = new ImagickImagine();
				break;
			case 'gd' :
				$imagine = new GdImagine();
				break;
			default:
				throw new \Exception("Library '$library' not supported");
			
		}
		return $imagine;
	}
	
	/**
	 * 
	 * @param \Zend\Cache\Storage\StorageInterface $storage
	 * @return \Soluble\Media\Converter\ImageConverter
	 */
	public function setCache(StorageInterface $storage) {
		$this->cacheStorage = $storage;
		$this->cacheEnabled = true;
		return $this;
	}	
	
	/**
	 * Unset cache (primarly for unit testing)
	 * @return \Soluble\Media\Converter\ImageConverter
	 */
	public function unsetCache() {
		$this->cacheEnabled = false;
		$this->cacheStorage = null;
		return $this;
	}
	
	

}