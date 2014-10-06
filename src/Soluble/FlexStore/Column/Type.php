<?php

namespace Soluble\FlexStore\Column;

class Type
{

    const TYPE_INTEGER  = 'integer';
    const TYPE_DECIMAL  = 'decimal';
    const TYPE_STRING   = 'string';
    const TYPE_BOOLEAN  = 'boolean';
    const TYPE_DATETIME = 'datetime';
    const TYPE_BLOB     = 'blob';
    const TYPE_DATE     = 'date';
    const TYPE_TIME     = 'time';
    const TYPE_BIT      = 'bit';

    protected static $typesMap = array(
        self::TYPE_INTEGER  => 'Type\IntegerType',
        self::TYPE_DECIMAL  => 'Type\DecimalType',
        self::TYPE_STRING   => 'Type\StringType',
        self::TYPE_BOOLEAN  => 'Type\BooleanType',
        self::TYPE_DATETIME => 'Type\DatetimeType',
        self::TYPE_BLOB     => 'Type\BlobType',
        self::TYPE_DATE     => 'Type\DateType',
        self::TYPE_TIME     => 'Type\TimeType',
        self::TYPE_BIT      => 'Type\BitType',
    );


    
    /**
     *
     * @param string $type_name

     * @throws Exception\InvalidArgumentException
     * @return \Soluble\FlexStore\Column\Type\AbstractType
     */
    public static function createType($type_name)
    {
        if (!self::isSupported($type_name)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Type '$type_name' is not supported.");
        }
        $class = __NAMESPACE__ . '\\' . self::$typesMap[strtolower($type_name)];
        return new $class();
    }
    
    /**
     * Whether a type is supported
     *
     * @param string $type_name
     * @return boolean
     */
    public static function isSupported($type_name)
    {
        return array_key_exists(strtolower($type_name), self::$typesMap);
    }
    
    /**
     * Return supported types
     *
     * @return array
     */
    public static function getSupported()
    {
        return array_keys(self::$typesMap);
    }
}
