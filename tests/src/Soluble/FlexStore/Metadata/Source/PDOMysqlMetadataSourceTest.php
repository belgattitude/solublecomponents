<?php

namespace Soluble\FlexStore\Metadata\Source;

use Soluble\FlexStore\Metadata\Column;


/**
 * PDO_MySQL in PHP 5.3 does not return column names 
 * @requires PHP 5.4
 */
class PDOMysqlMetadataSourceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PDOMysqlMetadataSource
     */
    protected $metadata;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $driver = 'PDO_Mysql';
        $this->adapter = \SolubleTestFactories::getDbAdapter(null, $driver);
        $conn = $this->adapter->getDriver()->getConnection()->getResource();
        $this->metadata = new PDOMysqlMetadataSource($conn);

    }

    public function testGetColumnsMetadata()
    {

        $sql = "select * from test_table_types";
        $md = $this->metadata->getColumnsMetadata($sql);

        $this->assertTrue($md['id']->isPrimary());
        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['id']->getDatatype());
        $this->assertEquals('test_table_types', $md['id']->getTableName());
        $this->assertEquals(false, $md['id']->isNullable());
        $this->assertEquals('test_table_types', $md['id']->getTableAlias());
        $this->assertEquals(1, $md['id']->getOrdinalPosition());
        $this->assertEquals(null, $md['id']->getCatalog());
        $this->assertEquals(null, $md['id']->isAutoIncrement());

        $this->assertEquals(Column\Type::TYPE_STRING, $md['test_varchar_255']->getDatatype());
        $this->assertEquals('VARCHAR', $md['test_varchar_255']->getNativeDatatype());
        $this->assertEquals($md['test_char_10']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals('CHAR', $md['test_char_10']->getNativeDatatype());
        // This does not work (bug in mysqli)
        //$this->assertEquals($md['test_char_10']->getCharacterMaximumLength(), 10);

        $this->assertEquals(Column\Type::TYPE_BLOB, $md['test_text_2000']->getDatatype());
        $this->assertEquals('BLOB', $md['test_text_2000']->getNativeDatatype());

        $this->assertEquals(Column\Type::TYPE_STRING, $md['test_binary_3']->getDatatype());
        $this->assertEquals('CHAR', $md['test_binary_3']->getNativeDatatype());

        $this->assertEquals($md['test_varbinary_10']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_varbinary_10']->getNativeDatatype(), 'VARCHAR');

        $this->assertEquals($md['test_int_unsigned']->getDatatype(), Column\Type::TYPE_INTEGER);
        // Cannot tell in PDO
        //$this->assertTrue($md['test_int_unsigned']->isNumericUnsigned());

        $this->assertEquals($md['test_bigint']->getDatatype(), Column\Type::TYPE_INTEGER);
        // Cannot tell in PDO
        //$this->assertFalse($md['test_bigint']->isNumericUnsigned());
        $this->assertEquals($md['test_bigint']->getNativeDatatype(), 'BIGINT');

        $this->assertEquals($md['test_decimal_10_3']->getDatatype(), Column\Type::TYPE_DECIMAL);
        $this->assertEquals($md['test_decimal_10_3']->getNativeDatatype(), 'DECIMAL');
        $this->assertEquals(3, $md['test_decimal_10_3']->getNumericPrecision());
        $this->assertEquals(10, $md['test_decimal_10_3']->getNumericScale());



        $this->assertEquals($md['test_float']->getDatatype(), Column\Type::TYPE_FLOAT);
        $this->assertEquals($md['test_float']->getNativeDatatype(), 'FLOAT');


        $this->assertEquals($md['test_tinyint']->getDatatype(), Column\Type::TYPE_INTEGER);
        $this->assertEquals($md['test_tinyint']->getNativeDatatype(), 'TINYINT');

        $this->assertEquals($md['test_mediumint']->getDatatype(), Column\Type::TYPE_INTEGER);
        $this->assertEquals($md['test_mediumint']->getNativeDatatype(), 'MEDIUMINT');


        $this->assertEquals($md['test_double']->getDatatype(), Column\Type::TYPE_FLOAT);
        $this->assertEquals($md['test_double']->getNativeDatatype(), 'DOUBLE');


        $this->assertEquals($md['test_smallint']->getDatatype(), Column\Type::TYPE_INTEGER);
        $this->assertEquals($md['test_smallint']->getNativeDatatype(), 'SMALLINT');

        $this->assertEquals($md['test_date']->getDatatype(), Column\Type::TYPE_DATE);
        $this->assertEquals($md['test_date']->getNativeDatatype(), 'DATE');


        $this->assertEquals($md['test_datetime']->getDatatype(), Column\Type::TYPE_DATETIME);
        $this->assertEquals($md['test_datetime']->getNativeDatatype(), 'DATETIME');

        $this->assertEquals($md['test_timestamp']->getDatatype(), Column\Type::TYPE_DATETIME);
        $this->assertEquals($md['test_timestamp']->getNativeDatatype(), 'TIMESTAMP');


        $this->assertEquals($md['test_time']->getDatatype(), Column\Type::TYPE_TIME);
        $this->assertEquals($md['test_time']->getNativeDatatype(), 'TIME');

        $this->assertEquals($md['test_blob']->getDatatype(), Column\Type::TYPE_BLOB);
        $this->assertEquals($md['test_blob']->getNativeDatatype(), 'BLOB');

        $this->assertEquals($md['test_tinyblob']->getDatatype(), Column\Type::TYPE_BLOB);
        $this->assertEquals($md['test_tinyblob']->getNativeDatatype(), 'BLOB');


        $this->assertEquals($md['test_mediumblob']->getDatatype(), Column\Type::TYPE_BLOB);
        $this->assertEquals($md['test_mediumblob']->getNativeDatatype(), 'BLOB');

        $this->assertEquals($md['test_longblob']->getDatatype(), Column\Type::TYPE_BLOB);
        $this->assertEquals($md['test_longblob']->getNativeDatatype(), 'BLOB');

        $this->assertEquals($md['test_enum']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_enum']->getNativeDatatype(), 'CHAR');


        $this->assertEquals($md['test_set']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_set']->getNativeDatatype(), 'CHAR');



    }


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

}
