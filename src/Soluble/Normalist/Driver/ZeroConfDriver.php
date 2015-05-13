<?php
namespace Soluble\Normalist\Driver;

use Soluble\Normalist\Driver\Exception;
use Soluble\Db\Metadata\Source;
use Zend\Db\Adapter\Adapter;
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
    protected $params;


    /**
     *
     * @var array
     */
    protected $default_options = array(
       'alias'          => 'default',
       'path'           => null,
       'version'        => 'latest',
       'schema'         => null,
       'permissions'    => 0666

    );

    /**
     *
     * @var array
     */
    protected static $metadataCache = array();



    /**
     * Underlying database adapter
     * @var Adapter
     */
    protected $adapter;

    /**
     * Construct a new Zero configuration driver
     *
     * $params allows you to specify the
     *   path    : where to store the model definition (default to sys_get_temp_dir())
     *   alias   : the alias to use when using multiple schemas, default: 'default'
     *   version : the version to use, default to 'latest'
     *   schema  : the database schema name, default to current adapter connection
     *   permissions: by default the model file is created with permission 0666
     *
     *
     * @param Adapter $adapter
     * @param array|Traversable $params [alias,path,version]
     * @throws Exception\ModelPathNotFoundException
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(Adapter $adapter, $params = array())
    {
        $this->setDbAdapter($adapter);

        if (!is_array($params) && !$params instanceof \Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' $params parameter expects an array or Traversable object');
        }

        $this->params = array_merge($this->default_options, (array) $params);

        if (!is_string($this->params['alias']) || trim($this->params['alias']) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' $params["alias"] parameter expects valid string');
        }

        if ($this->params['schema'] !== null &&
                (!is_string($this->params['schema']) || trim($this->params['schema']) == '')) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' $params["schema"] parameter expects valid string');
        }


        if (!is_scalar($this->params['version']) || trim($this->params['version']) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' $params["version"] parameter expects valid scalar value');
        }

        if ($this->params['path'] == '') {
            $this->params['path'] = sys_get_temp_dir();
        } elseif (!is_string($this->params['path']) || trim($this->params['path']) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' $params["path"] parameter expects valid string value');
        }

        if (!is_dir($this->params['path'])) {
            $path = (string) $this->params['path'];
            throw new Exception\ModelPathNotFoundException(__METHOD__ . " Model directory not found '" . $path . "'");
        }

        if ($this->params['permissions'] != '') {
            if (!is_scalar($this->params['permissions'])) {
                throw new Exception\InvalidArgumentException(__METHOD__ . ' $params["permission"] parameter expects string|interger|octal value');
            }
        }
    }


    /**
     * Return models configuration file
     * @return string
     */
    public function getModelsConfigFile()
    {
        $o = $this->params;
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
        if (!file_exists($file) || !is_readable($file)) {
            throw new Exception\ModelFileNotFoundException(__METHOD__ . " Model configuration file '$file' does not exists or not readable");
        }

        if (defined('HHVM_VERSION')) {
            // As an 'evil' workaround, waiting for hhvm to comply
            // see https://github.com/facebook/hhvm/issues/1447
            $definition = false;
            $file_content = file_get_contents($file);
            $file_content = trim(str_replace('<?php', '', $file_content));
            $file_content = trim(str_replace('return array(', '$definition = array(', $file_content));
            eval($file_content);
            
        } else {
            $definition = include $file;
        }
        
        if (!$definition) {
            throw new Exception\ModelFileCorruptedException(__METHOD__ . " Model configuration file '$file' cannot be included");
        }
        if (!is_array($definition)) {
            throw new Exception\ModelFileCorruptedException(__METHOD__ . " Model configuration file '$file' was included but is not a valid array");
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
    protected function saveModelsDefinition(array $models_definition)
    {
        $file = $this->getModelsConfigFile();
        if (file_exists($file) && !is_writable($file)) {
            throw new Exception\ModelFileNotWritableException(__METHOD__ . "Model configuration file '$file' cannot be overwritten, not writable.");
        }

        //$config = new Config($models_defintion, true);
        $writer = new Writer\PhpArray();
        $models_definition['normalist'] = array('model_version' => Metadata\NormalistModels::VERSION);
        $writer->toFile($file, $models_definition, $exclusiveLock = true);
        $perms = $this->params['permissions'];
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
    protected function setDbAdapter(Adapter $adapter)
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
        $cache_key = md5(serialize($this->params));
        if (!array_key_exists($cache_key, self::$metadataCache)) {
            if ($this->metadata === null) {
                self::$metadataCache[$cache_key] = $this->getDefaultMetadata();
            } else {
                self::$metadataCache[$cache_key] = $this->metadata;
            }
        }
        return self::$metadataCache[$cache_key];
    }


    /**
     *
     * @return ZeroConfDriver
     */
    public function clearMetadataCache()
    {
        self::$metadataCache = array();
        return $this;
    }

    /**
     *
     * @return Metadata\NormalistModels
     */
    protected function getDefaultMetadata()
    {
        try {
            $model_definition = $this->getModelsDefinition();
        } catch (Exception\ExceptionInterface $e) {
            // means model definition does not exists
            // lets load it from the current connection
            if ($this->params['schema'] == '') {
                $schema = null;
            } else {
                $schema = $this->params['schema'];
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
