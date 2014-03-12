<?php
namespace Soluble\Normalist\Driver;

use Soluble\Normalist\Driver\Exception;
use Soluble\Db\Metadata\Source;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Config;
use Zend\Config\Writer;

class ZeroConfDriver implements DriverInterface
{

    /**
     * @var Source\AbstractSource
     */
    protected $metadata;
    
    
    /**
     *
     * @var array
     */
    protected $options;

    
    /**
     *
     * @var array
     */
    protected $default_options = array(
       'alias'          => 'default',
       'path'           => null,
       'version'        => 'latest',
       'permissions'    => 0666 
        
    );
    
    /**
     *
     * @var array
     */
    static protected $metadataCache = array();
    
    
    
    
    /**
     * Construct a new Zero configuration driver
     * 
     * $options allows you to specify the 
     *   path    : where to store the model definition (default to sys_get_temp_dir())
     *   alias   : the alias to use when using multiple schemas, default: 'default'
     *   version : the version to use, default to 'latest'
     *   schema  : the database schema name, default to current adapter connection
     *   
     * 
     * @param array|Traversable $options [alias,path,version]
     * @throws Exception\ModelPathNotFoundException
     * @throws Exception\ModelPathNotWritableException
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options=array())
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' $options parameter expects an array or Traversable object');
        }        
        
        $this->options = array_merge($this->default_options, (array) $options);        
        
        if (!is_string($this->options['alias']) || trim($this->options['alias']) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' $options["alias"] parameter expects valid string');            
        }
        
        if (!is_scalar($this->options['version']) || trim($this->options['version']) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' $options["version"] parameter expects valid scalar value');            
        }
        
        if ($this->options['path'] == '') {
            $this->options['path'] = sys_get_temp_dir();
        } elseif (!is_string($this->options['path']) || trim($this->options['path']) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' $options["path"] parameter expects valid string value');            
        }
        
        if (!is_dir($this->options['path'])) {
            $path = (string) $this->options['path'];
            throw new Exception\ModelPathNotFoundException(__METHOD__ . " Model directory not found '" . $path . "'");
        }
        if (!is_writable($this->options['path'])) {
            $path = (string) $this->options['path'];
            throw new Exception\ModelPathNotWritableException(__METHOD__ . " Model directory not writable '" . $path . "'");
        }
        
        if ($this->options['permissions'] != '') {
            
            if (!is_scalar($this->options['permissions'])) {
                throw new Exception\InvalidArgumentException(__METHOD__ . ' $options["permission"] parameter expects string|interger|octal value');            
            }
            
        }
        
        
    }
    

    /**
     * Return models configuration file
     * @return string
     */
    public function getModelsConfigFile()
    {
        $o = $this->options;
        $file =  $o['path'] . DIRECTORY_SEPARATOR . 'normalist_' . $o['alias'] . '-' . $o['version'] . '.php';
        return $file;
    }
    
    /**
     * Get models definition according to options
     * 
     * @throws Exception\ModelFileNotFoundException
     * @throws Exception\ModelFileCorruptedException
     * @return array
     */
    public function getModelsDefinition()
    {
        $file = $this->getModelsConfigFile();
        if (!file_exists($file)) {
            throw new Exception\ModelFileNotFoundException(__METHOD__ . " Model configuration file '$file' does not exists");
        }
        
        $definition = include $file;
        if (!$definition || !is_array($definition)) {
            throw new Exception\ModelFileCorruptedException(__METHOD__ . " Model configuration file '$file' cannot be read");
        }
        return $definition;
    }
    

    /**
     * Save model definition
     * 
     * @throws Exception\ModelFileNotWritableException
     * @param array $models_definition
     * @return DriverInterface
     */
    public function saveModelsDefinition(array $models_definition)
    {
        $file = $this->getModelsConfigFile();
        if (file_exists($file) && !is_writable($file)) {
            throw new Exception\ModelFileNotWritableException(__METHOD__ . "Model configuration file '$file' cannot be overwritten, not writable.");
        }
        
        //$config = new Config($models_defintion, true);
        $writer = new Writer\PhpArray();
        $writer->toFile($file, $models_definition, $exclusiveLock=true);
        $perms = $this->options['permissions'];
        if ($perms != '') {
            if (decoct(octdec($perms)) == $perms) {
                $perms = octdec($perms);
            }
            chmod($file, $perms);
        }
        return $this;
    }        
           
    
    
    
    /**
     * Set underlying database adapter
     * 
     * @param Adapter $adapter
     * @return DriverInterface
     */
    public function setDbAdapter(Adapter $adapter) 
    {
        $this->adapter = $adapter;
        return $this;
    }
    
    /**
     * Get underlying database adapter
     * 
     * @return Adapter
     */
    public function getDbAdapter()
    {
        return $this->adapter;
    }
    
    /**
     * Get internal metadata reader
     * 
     * @return Source\AbstractSource
     */
    public function getMetadata()
    {
        $cache_key = md5(serialize($this->options));
        if (!array_key_exists($cache_key, self::$metadataCache)) {
            self::$metadataCache[$cache_key] = $this->getDefaultMetadata();
        }
        return self::$metadataCache[$cache_key];
    }
    
    /**
     * 
     * @return \Soluble\Normalist\Driver\Metadata\NormalistModels
     * @throws Exception\RuntimeException
     */
    protected function getDefaultMetadata()
    {
        try {
            $model_definition = $this->getModelsDefinition();
        } catch (Exception\ExceptionInterface $e) {
            
            // means model definition does not exists
            // lets load it from the current connection
            if ($this->adapter === null) {
                $msg = "Zero conf driver requires a Zend\Db\Adapter\Adapter connection in order to provide automatic model generation.";
                throw new Exception\RuntimeException(__METHOD__ . " " . $msg);
            }
            if ($this->options['schema'] == '') {
                $schema = null;
            } else {
                $schema = $this->options['schema'];
            }
            $md = new Source\Mysql\InformationSchema($this->adapter, $schema);
            $model_definition = $md->getSchemaConfig();
            
            // For later use we save the models definition
            $this->saveModelsDefinition($model_definition);
        }
        return new Metadata\NormalistModels($model_definition);
        
    }        
          
    
    /**
     * Set internal metadata reader
     *  
     * @param Source\AbstractSource $metadata
     * @return ZeroConfDriver
     */
    public function setMetadata(Source\AbstractSource $metadata)
    {
        $this->metadata = $metadata;
        return $this;
                
    }
    
}