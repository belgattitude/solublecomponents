<?php
namespace Soluble\FlexStore\Metadata\Column;


use Soluble\FlexStore\Metadata\Exception;


class Type
{
    const TYPE_INTEGER	= 'integer';
    const TYPE_DECIMAL	= 'decimal';
    const TYPE_STRING	= 'string';
    const TYPE_BOOLEAN	= 'boolean';
    const TYPE_DATETIME	= 'datetime';
    const TYPE_BLOB		= 'blob';
    const TYPE_DATE		= 'date';
    const TYPE_TIME		= 'time';
    const TYPE_FLOAT	= 'float';


    /**
     * @var array
     */
    private static $typesMap = array(

        self::TYPE_INTEGER	=> 'Definition\IntegerColumn',
        self::TYPE_DECIMAL	=> 'Definition\DecimalColumn',
        self::TYPE_STRING	=> 'Definition\StringColumn',
        self::TYPE_BOOLEAN	=> 'Definition\BooleanColumn',
        self::TYPE_DATETIME	=> 'Definition\DatetimeColumn',
        self::TYPE_BLOB		=> 'Definition\BlobColumn',
        self::TYPE_DATE		=> 'Definition\DateColumn',
        self::TYPE_TIME		=> 'Definition\TimeColumn',
        self::TYPE_FLOAT	=> 'Definition\FloatColumn',

    );



    /**
     *
     * @param string $datatype
     * @param string $name
     * @param string $tableName
     * @param tring $schemaName
     * @throws Exception\UnsupportedDatatypeException
     * @return \Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn
     */
    public static function createColumnDefinition($datatype, $name, $tableName = null, $schemaName = null)
    {
        if (!array_key_exists($datatype, self::$typesMap)) {
            throw new Exception\UnsupportedDatatypeException("Type '$datatype', is not supported");
        }
        $class = __NAMESPACE__ . '\\' . self::$typesMap[$datatype];

        return new $class($name, $tableName, $schemaName);
    }

}
