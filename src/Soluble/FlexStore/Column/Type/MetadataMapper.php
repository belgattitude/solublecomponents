<?php

namespace Soluble\FlexStore\Column\Type;

use Soluble\FlexStore\Column\Type\AbstractType;
use Soluble\FlexStore\Column\ColumnType;
use Soluble\FlexStore\Column\ColumnModel;
use Soluble\FlexStore\Column\Column;
use Soluble\Db\Metadata\Column\Type as MetadataType;
use Soluble\FlexStore\Column\Exception;
use ArrayObject;

class MetadataMapper
{
    protected static $mapper = array(
        MetadataType::TYPE_BIT => ColumnType::TYPE_BIT,
        MetadataType::TYPE_BOOLEAN => ColumnType::TYPE_BOOLEAN,
        MetadataType::TYPE_BLOB => ColumnType::TYPE_BLOB,
        MetadataType::TYPE_DATE => ColumnType::TYPE_DATE,
        MetadataType::TYPE_DATETIME => ColumnType::TYPE_DATETIME,
        MetadataType::TYPE_DECIMAL => ColumnType::TYPE_DECIMAL,
        MetadataType::TYPE_FLOAT => ColumnType::TYPE_DECIMAL,
        MetadataType::TYPE_INTEGER => ColumnType::TYPE_INTEGER,
        MetadataType::TYPE_SPATIAL_GEOMETRY => ColumnType::TYPE_STRING,
        MetadataType::TYPE_TIME => ColumnType::TYPE_TIME,
        MetadataType::TYPE_STRING => ColumnType::TYPE_STRING
    );

    /**
     *
     * @param string $metadata_type
     * @return AbstractType
     */
    public static function getColumnTypeByMetadataType($metadata_type)
    {
        if (!array_key_exists($metadata_type, self::$mapper)) {
            $mt = (string) $metadata_type;
            throw new Exception\InvalidArgumentException(__METHOD__ . " Cannot map the metadata type '$mt' to a column model type");
        }
        return ColumnType::createType(self::$mapper[$metadata_type]);
    }

    /**
     *
     * @param ArrayObject $metadata_columns
     * @return ColumnModel
     */
    public static function getColumnModelFromMetadata(ArrayObject $metadata_columns)
    {
        $cm = new ColumnModel();
        $cm->setMetatadata($metadata_columns);
        foreach ($metadata_columns as $name => $meta) {
            $column = new Column($name);
            $column->setType(self::getColumnTypeByMetadataType($meta->getDataType()));
            $column->setVirtual(false);
            $cm->add($column);
        }
        return $cm;
    }
}
