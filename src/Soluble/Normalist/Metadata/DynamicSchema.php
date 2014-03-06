<?php
namespace Soluble\Normalist\Metadata;
use Soluble\Db\Metadata\Source;
use Zend\Db\Adapter\Adapter;
use Zend\Config\Config;
use Zend\Config\Writer;
use Soluble\Db\Metadata\Exception;


class DynamicSchema extends Source\AbstractSource
{
    
    /**
     *
     * @var Adapter
     */
    protected $adapter;
    
    
    /**
     *
     * @var boolean 
     */
    protected $initialized;

    /**
     *
     * @var array
     */
    static protected $localCache = array();
    

    /**
     *
     * @var array
     */
    static protected $fullyCachedSchemas = array();
    
    /**
     *
     * @param Adapter $adapter
     * @param string $schema default schema, taken from adapter if not given
     * @throws Exception\InvalidArgumentException if schema parameter not valid
     */
    public function __construct(Adapter $adapter, $schema=null)
    {
        $this->adapter = $adapter;
        if ($schema === null) {
            $schema = $adapter->getCurrentSchema();
        }
        $this->setDefaultSchema($schema);
        $this->initialize();
    }    
    
    
    /**
     * Get unique keys on table
     *
     * @param string $table table name
     * @param string $schema schema name
     * @param boolean $include_primary include primary keys in the list
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     * @return array
     */
    public function getUniqueKeys($table, $schema=null, $include_primary=false) 
    {
        
        return self::$localCache['tables'][$table]['unique_keys'];
    }


    /**
     * Return indexes information on a table
     *
     * @param string $table table name
     * @param string $schema schema name
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @return array
     */
    public function getIndexesInformation($table, $schema=null)
    {
        
        return self::$localCache['tables'][$table]['indexes'];
    }

    /**
     * Return unique table primary key
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException when no pk 
     * @throws Exception\MultiplePrimaryKeyException when multiple pk found
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @param string $schema
     * @return string|int primary key
     */
    public function getPrimaryKey($table, $schema=null)
    {
        if ($schema === null) $schema = $this->schema;
        $pks = $this->getPrimaryKeys($table, $schema);
        if (count($pks) > 1) {
            $keys = join(',', $pks);
            throw new Exception\MultiplePrimaryKeyException(__METHOD__ . ". Multiple primary keys found on table '$schema'.'$table':  $keys");
        }
        return $pks[0];
    }


    /**
     * Return composite primary keys
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @param string $schema
     * @return array primary key
     */
    public function getPrimaryKeys($table, $schema=null)
    {
        if ($schema === null) $schema = $this->schema;
        $pks = self::$localCache['tables'][$table]['primary_keys'];
        if (count($pks) == 0) {
            throw new Exception\NoPrimaryKeyException(__METHOD__ . ". No primary keys found on table '$schema'.'$table'.");
        }
        return $pks;
    }


    /**
     * Return column information
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @param string $schema
     * @return array associative array [column_name => infos]
     */
    public function getColumnsInformation($table, $schema=null)
    {
        
        return self::$localCache['tables'][$table]['columns'];
        
    }


    /**
     * Return relations information
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @param string $schema
     *
     * @return array
     */
    public function getRelations($table, $schema=null)
    {
        
        return self::$localCache['tables'][$table]['foreign_keys'];
        
    }

    /**
     * Return table informations
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     *
     * @param string $schema
     * @return array associative array indexed by table_name
     */
    public function getTablesInformation($schema=null)
    {
    
        return self::$localCache['tables'];
    }
    
    
    protected function initialize()
    {
        $schema_file = '/tmp/a_file.php';
        if (!$this->inialized) {
            $this->initialized = true;
            if (!file_exists($schema_file)) {
                $reader = new Source\Mysql\InformationSchema($this->adapter, $this->schema);
                $schemaCfg = $reader->getSchemaConfig($this->schema, $include_options=false);
                $config = new Config($schemaCfg, true);
                $writer = new Writer\PhpArray();
                $string = $writer->toString($config);
                file_put_contents($schema_file, $string);
                self::$localCache = $schemaCfg;
            } elseif (count(self::$localCache) == 0) {
                self::$localCache = include $schema_file;
            }
        }
    }
}