<?php
namespace Soluble\Db\Metadata\Source\Mysql;

use Zend\Db\Adapter\Adapter;
use Soluble\Db\Metadata\Exception;
use Zend\Config\Config;
use Soluble\Db\Metadata\Source;

class InformationSchema extends Source\AbstractSource
{
    
    /**
     * Schema name
     * @var string
     */
    protected $schema;

    /**
     * @var Adapter
     */
    protected $adapter;
    
    /**
     * @var Zend\Db\Metadata\Source\AbstractSource
     */
    protected $metadata_reader;
    
    
    /**
     * Used to restore innodb stats mysql global variable
     * @var string
     */
    protected $mysql_innodbstats_value;
    
    /**
     *
     * @var array
     */
    static protected $localCache = array();
    
    
    /**
     *
     * @var boolean
     */
    protected $useLocalCaching = true;

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
        if ($schema === null) $schema = $this->schema;
        
        $this->loadCacheInformation($schema, $table);
        return self::$localCache[$schema]['tables'][$table]['unique_keys'];
        
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
        if ($schema === null) $schema = $this->schema;
        $this->loadCacheInformation($schema, $table);
        return self::$localCache[$schema]['tables'][$table]['indexes'];
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
     * @return null|array primary key
     */
    public function getPrimaryKeys($table, $schema=null)
    {
        if ($schema === null) $schema = $this->schema;
        
        $this->loadCacheInformation($schema, $table);
        $pks = self::$localCache[$schema]['tables'][$table]['primary_keys'];
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
        if ($schema === null) $schema = $this->schema;
        $this->loadCacheInformation($schema, $table);
        return self::$localCache[$schema]['tables'][$table]['columns'];
        
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
        if ($schema === null) $schema = $this->schema;
        $this->loadCacheInformation($schema, $table);
        return self::$localCache[$schema]['tables'][$table]['foreign_keys'];
        
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
        if ($schema === null) $schema = $this->schema;
        $this->loadCacheInformation($schema, null);
        return self::$localCache[$schema]['tables'];
    }


    
    /**
     * Get a table configuration
     * 
     * @throws Exception\ErrorException
     * @throws Exception\TableNotFoundException
     * 
     * @param string $table table name
     * @param string $schema schema name
     * @param boolean $include_options include extended information
     * @return array
     */
    function getTableConfig($table, $schema=null, $include_options=false)
    {
        if ($schema === null) $schema = $this->schema;

        if ($this->useLocalCaching) {
            if ( in_array($schema, self::$fullyCachedSchemas)
                 || (array_key_exists($schema, self::$localCache) &&
                     array_key_exists('tables', self::$localCache[$schema]) &&
                     array_key_exists($table, self::$localCache[$schema]['tables']))) 
                { 
                
                return self::$localCache[$schema]['tables'][$table];
            } 
        }
        
        
        
        $config = $this->getObjectConfig($table, $schema, $include_options);
        if (!array_key_exists($table, $config['tables'])    ) {
            throw new Exception\TableNotFoundException(__METHOD__ . ". Table '$table' in database schema '$schema' not found.");
        }        
        
        if ($this->useLocalCaching) {
            if (!array_key_exists($schema, self::$localCache)) {
                self::$localCache[$schema] = array();
            }
            self::$localCache[$schema] = array_merge_recursive(self::$localCache[$schema], $config);
        }
        
        return $config['tables'][$table];
    }
    

    /**
     * Get schema configuration
     * 
     * @throws Exception\ErrorException
     * @throws Exception\SchemaNotFoundException
     * 
     * @param string $schema if not given will take active schema from database adapter
     * @param boolean $include_options include extended information
     * @return array
     */
    function getSchemaConfig($schema=null, $include_options=false)
    {
        
        if ($schema === null) $schema = $this->schema;
        if ($this->useLocalCaching && in_array($schema, self::$fullyCachedSchemas)) {
            return self::$localCache[$schema];
        }
        
        
        $table = null;
        $config = $this->getObjectConfig($table, $schema, $include_options);
        if (count($config['tables']) == 0) {
            throw new Exception\SchemaNotFoundException(__METHOD__ . " Error: schema '$schema' not found or without any table or view");
        }
        if ($this->useLocalCaching) {
            self::$localCache[$schema] = $config;
            self::$fullyCachedSchemas[] = $schema;
        }
        return $config;
    }

    /**
     * Return object (table/schema) configuration
     * 
     * @throws Exception\ErrorException
     *  
     * @param string $table
     * @param string $schema
     * @param boolean $include_options
     * @return array
     */
    protected function getObjectConfig($table=null, $schema=null, $include_options=false)
    {
        if ($schema === null) $schema = $this->schema;
        $qSchema = $this->adapter->getPlatform()->quoteValue($schema);

        if ($table !== null) {
            $qTable = $this->adapter->getPlatform()->quoteValue($table);
            $table_clause = "and (t.TABLE_NAME = $qTable or (kcu.referenced_table_name = $qTable and kcu.constraint_name = 'FOREIGN KEY'))";
            $table_join_condition = "(t.table_name = kcu.table_name or  kcu.referenced_table_name = t.table_name)";
        } else {
            $table_join_condition = "t.table_name = kcu.table_name";
            $table_clause = '';
        }

        $query = "

            SELECT 
                    t.table_name, 
                    c.column_name, 
                    c.data_type,
                    c.column_type,  

                    c.extra, 

                    tc.constraint_type,
                    kcu.constraint_name,
                    kcu.referenced_table_name,
                    kcu.referenced_column_name,

                    c.column_default, 
                    c.is_nullable, 
                    c.numeric_precision, 
                    c.numeric_scale, 
                    c.character_octet_length, 
                    c.character_maximum_length,
                    c.ordinal_position, 
                    
					c.column_key, -- UNI/MUL/PRI
					c.character_set_name,


                    c.collation_name, 

                    c.column_comment, 

                    t.table_type, 
                    t.engine, 
                    t.table_comment, 
                    t.table_collation

            FROM `INFORMATION_SCHEMA`.`COLUMNS` c 
            INNER JOIN `INFORMATION_SCHEMA`.`TABLES` t on c.TABLE_NAME = t.TABLE_NAME 
            LEFT JOIN `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` kcu 
               on (
                    $table_join_condition
                     and kcu.table_schema = t.table_schema 
                     and kcu.column_name = c.column_name
                 )
              LEFT JOIN
                `INFORMATION_SCHEMA`.`TABLE_CONSTRAINTS` tc
               on (
                     t.table_name = tc.table_name
                      and tc.table_schema = t.table_schema 
                      and tc.constraint_name = kcu.constraint_name
                  )


            where c.TABLE_SCHEMA = $qSchema 
            and t.TABLE_SCHEMA = $qSchema
            $table_clause
            and (kcu.table_schema = $qSchema  or kcu.table_schema is null)
            
            and (kcu.column_name = c.column_name or kcu.column_name is null)
            order by t.table_name, c.ordinal_position
        ";
        $this->disableInnoDbStats();
        try {
            $results = $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        } catch (\Exception $e) {
            $this->restoreInnoDbStats();
            throw new Exception\ErrorException($e->getMessage());
        }
        $this->restoreInnoDbStats();
        
        $references = array();
        $config = new Config(array('tables' => array()), true);
        
        foreach($results as $r) {
            // Setting table information
            $table_name = $r['table_name'];
            if (!$config->tables->offsetExists($table_name)) {
                
                $table_def = array(
                    'name'          => $table_name,
                    'columns'       => array(),
                    'primary_keys'  => array(),
                    'unique_keys'   => array(),
                    'foreign_keys'  => array(),
                    'references'    => array(),
                    'indexes'       => array(),
                ); 
                if ($include_options) {
                    $table_def['options'] = array(
                       'engine'    => $r['engine'],
                       'comment'   => $r['table_comment'],
                       'collation' => $r['table_collation'],
                       'type'      => $r['table_type'],
                        
                    );
                }
                $config->tables[$table_name] = $table_def;
            }    
            $table   = $config->tables[$table_name];
            $columns = $table->columns;
            $column_name = $r['column_name'];
            $col_def = array(
                'data_type'         => $r['data_type'],
                'is_primary'        => $r['constraint_type'] == 'PRIMARY KEY',
                'is_autoincrement'  => $r['extra'] == 'auto_increment',
                'is_nullable'       => $r['is_nullable'] == 'YES',
                'default'           => $r['column_default']
            );
            
            if (in_array($r['data_type'], array('int', 'tinyint', 'mediumint', 'bigint', 'decimal'))) {
                $col_def['precision'] = $r['numeric_precision'];
                $col_def['scale']     = $r['numeric_scale'];
                
            } elseif (!in_array($r['data_type'], array('timestamp', 'date', 'time', 'datetime'))) {
                $col_def['octet_length'] = $r['character_octet_length'];
                $col_def['length'] = $r['length'];
            }
            if ($include_options) {
            
                $col_def['options'] = array(
                        'column_type'       => $r['column_type'],
                        'column_key'        => $r['column_key'],
                        'ordinal_position'  => $r['ordinal_position'],
                        'constraint_type'   => $r['constraint_type'], // 'PRIMARY KEY', 'FOREIGN_KEY', 'UNIQUE' 
                        'charset'           => $r['character_set_name'],
                        'collation'         => $r['collation_name'],
                    
                    );
            }
            
            $columns[$column_name] = $col_def;

            $foreign_keys = $table->foreign_keys;
            $unique_keys  = $table->unique_keys;
            
            $constraint_name = $r['constraint_name'];
            $referenced_table_name = $r['referenced_table_name'];
            $referenced_column_name = $r['referenced_column_name'];
            switch ($r['constraint_type']) {
                case 'PRIMARY KEY':
                    $table->primary_keys = array_merge($table->primary_keys->toArray(), (array) $column_name); 
                    break;
                case 'UNIQUE':
                    if (!$unique_keys->offsetExists($constraint_name)) {
                        $unique_keys[$constraint_name] = array();
                    }
                    $unique_keys[$constraint_name] = array_merge($unique_keys[$constraint_name]->toArray(), (array) $column_name); 
                    break;
                case 'FOREIGN KEY':
                    /*
                    if (!$foreign_keys->offsetExists($constraint_name)) {
                        $foreign_keys[$constraint_name] = array();
                    }
                     * 
                     */
                    $fk = array(
                       'referenced_table'  => $referenced_table_name,
                       'referenced_column' => $referenced_column_name,
                       'constraint_name' => $constraint_name
                    );
                    $foreign_keys[$column_name] = $fk;              
                    //$table->references[$referenced_table_name] = array($column_name => $r['referenced_column_name']);
                    
                    if (!array_key_exists($referenced_table_name, $references)) {
                        $references[$referenced_table_name] = array();
                    }
                    
                    $references[$referenced_table_name][] = array(
                        'column' => $column_name,
                        //'referenced_table' => $table_name,
                        'referenced_column' => $referenced_column_name,
                        'constraint_name' => $constraint_name
                    );
                    break;
            }
             
        }
        
        
        foreach ($references as $referenced_table_name => $refs) {
            if ($config->tables->offsetExists($referenced_table_name)) {
                $table = $config->tables[$referenced_table_name];
                $references = $table->references;
                $references[$referenced_table_name] = $refs;
            }
        }
        $array = $config->toArray();
        unset($config);
        return $array;
        
    }
    
    /**
     * Disbale innodbstats will increase speed of metadata lookups
     * 
     * @return void
     */
    protected function disableInnoDbStats()
    {
        $sql = "show global variables like 'innodb_stats_on_metadata'";
        try {
            $results = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $row = $results->current();
            $value = strtoupper($row['Value']);
            // if 'on' no need to do anything
            if ($value != 'OFF') {
                $this->mysql_innodbstats_value = $value;
                // disabling innodb_stats 
                $this->adapter->query("set global innodb_stats_on_metadata='off'");
            }
        } catch (\Exception $e) {
            // do nothing, silently fallback
        }

    }
    
    
    /**
     * Restore old innodbstats variable
     * @return void
     */
    protected function restoreInnoDbStats()
    {
        $value = $this->mysql_innodbstats_value;
        if ($value !== null) {
            // restoring old variable
            $this->adapter->query("set global innodb_stats_on_metadata='$value'");
        }
    }
    
    
    /**
     * @param string $schema     
     * @param string $table
     */
    protected function loadCacheInformation($schema=null, $table=null)
    {
        if ($schema === null) $schema = $this->schema;
        
        $this->validateSchemaTable($schema, $table);
        
        
        if (!in_array($schema, self::$fullyCachedSchemas)) {
            if ($table !== null) {
                  $this->getTableConfig($table, $schema);
            } else {
                  $this->getSchemaConfig($schema);
            }
        }
           
    }
    
    protected function validateSchemaTable($schema, $table=null)
    {
        if (!is_string($schema) || trim($schema) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Schema name must be a valid string or an empty string detected");
        }
        if ($table !== null) {
            if (!is_string($table) || trim($table) == '') {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Table name must be a valid string or an empty string detected");
            }
        }
        
    }
    
}