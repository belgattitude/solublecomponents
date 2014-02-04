<?php

namespace Soluble\FlexStore\Metadata\Source;

use Soluble\FlexStore\Metadata\Column;

/**
 * @requires extension mysqli
 */

class MysqliMetadataSourceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MysqliMetadataSource
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
        $driver = 'Mysqli';
        $this->adapter = \SolubleTestFactories::getDbAdapter(null, $driver);
        $conn = $this->adapter->getDriver()->getConnection()->getResource();
        $this->metadata = new MysqliMetadataSource($conn);

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Soluble\FlexStore\Metadata\Source\MysqliMetadataSource::getColumnsMetadata
     */
    public function testGetColumnsMetadataThrowsEmptyQueryException()
    {
        $this->setExpectedException('Soluble\FlexStore\Metadata\Exception\EmptyQueryException');
        $sql = "";
        $md = $this->metadata->getColumnsMetadata($sql);
    }

    /**
     * @covers Soluble\FlexStore\Metadata\Source\MysqliMetadataSource::getColumnsMetadata
     */
    public function testGetColumnsMetadataThrowsInvalidQueryException()
    {
        $this->setExpectedException('Soluble\FlexStore\Metadata\Exception\InvalidQueryException');
        $sql = "select * from sss";
        $md = $this->metadata->getColumnsMetadata($sql);

    }

    /**
     * @covers Soluble\FlexStore\Metadata\Source\MysqliMetadataSource::getColumnsMetadata
     */
    public function testGetColumnsMetadataThrowsAmbiguousColumnException()
    {
        $this->setExpectedException('Soluble\FlexStore\Metadata\Exception\AmbiguousColumnException');
        $sql = "select id, id from test_table_types";
        $md = $this->metadata->getColumnsMetadata($sql);

    }


    /**
     * @covers Soluble\FlexStore\Metadata\Source\MysqliMetadataSource::getColumnsMetadata
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::isPrimary
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getDatatype
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getNativeDatatype
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getTableName
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::isNullable
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getTableAlias
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getOrdinalPosition
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getCatalog
     *
     * @covers Soluble\FlexStore\Metadata\Column\Definition\IntegerColumn::isAutoIncrement
     * @covers Soluble\FlexStore\Metadata\Column\Definition\IntegerColumn::isNumericUnsigned
     */
    public function testGetColumnsMetadata()
    {

        $sql = "select * from test_table_types";
        $md = $this->metadata->getColumnsMetadata($sql);

        $this->assertEquals($md['id']->isPrimary(), true);
        $this->assertEquals($md['id']->getDatatype(), Column\Type::TYPE_INTEGER);
        $this->assertEquals($md['id']->getTableName(), 'test_table_types');
        $this->assertEquals($md['id']->isNullable(), false);
        $this->assertEquals($md['id']->getTableAlias(), 'test_table_types');
        $this->assertEquals($md['id']->getOrdinalPosition(), 1);
        $this->assertEquals($md['id']->getCatalog(), 'def');
        $this->assertEquals($md['id']->isAutoIncrement(), true);

        $this->assertEquals($md['test_varchar_255']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_varchar_255']->getNativeDatatype(), 'VARCHAR');
        $this->assertEquals($md['test_char_10']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_char_10']->getNativeDatatype(), 'VARCHAR');
        // This does not work (bug in mysqli)
        //$this->assertEquals($md['test_char_10']->getCharacterMaximumLength(), 10);

        $this->assertEquals($md['test_text_2000']->getDatatype(), Column\Type::TYPE_BLOB);
        $this->assertEquals($md['test_text_2000']->getNativeDatatype(), 'BLOB');

        $this->assertEquals($md['test_binary_3']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_binary_3']->getNativeDatatype(), 'VARCHAR');

        $this->assertEquals($md['test_varbinary_10']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_varbinary_10']->getNativeDatatype(), 'VARCHAR');

        $this->assertEquals($md['test_int_unsigned']->getDatatype(), Column\Type::TYPE_INTEGER);
        $this->assertTrue($md['test_int_unsigned']->isNumericUnsigned());

        $this->assertEquals($md['test_bigint']->getDatatype(), Column\Type::TYPE_INTEGER);
        $this->assertFalse($md['test_bigint']->isNumericUnsigned());
        $this->assertEquals($md['test_bigint']->getNativeDatatype(), 'BIGINT');

        $this->assertEquals($md['test_decimal_10_3']->getDatatype(), Column\Type::TYPE_DECIMAL);
        $this->assertEquals($md['test_decimal_10_3']->getNativeDatatype(), 'DECIMAL');
        $this->assertEquals(10, $md['test_decimal_10_3']->getNumericScale());
        $this->assertEquals(3, $md['test_decimal_10_3']->getNumericPrecision());


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
        $this->assertEquals($md['test_enum']->getNativeDatatype(), 'ENUM');


        $this->assertEquals($md['test_set']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_set']->getNativeDatatype(), 'SET');



    }



    /**
     * @covers Soluble\FlexStore\Metadata\Source\MysqliMetadataSource::getColumnsMetadata
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::isPrimary
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getDatatype
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getNativeDatatype
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getTableName
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getTableAlias
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::isComputed
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::isGroup
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getName
     * @covers Soluble\FlexStore\Metadata\Column\Definition\AbstractColumn::getAlias
     */

    public function testGetColumsMetadataMultipleTableFunctions()
    {
        $sql = "
                SELECT 'cool' as test_string,
                        1.1 as test_float,
                        (10/2*3)+1 as test_calc,
                        (1+ mc.container_id) as test_calc_2,
                        m.container_id,
                        mc.container_id as mcid,
                        mc.title,
                        filesize,
                        count(*),
                        max(filemtime),
                        min(filemtime),
                        group_concat(filename),
                        avg(filemtime),
                        count(*) as count_media,
                        max(filemtime) as max_time,
                        min(filemtime) as min_time,
                        group_concat(filename) as files,
                        avg(filemtime) as avg_time,
                        sum(filesize) as sum_filesize

                FROM media m
                inner join media_container mc
                on mc.container_id = m.container_id
                group by 1,2,3,4,5,6,7,8
                order by 9 desc
        ";

        $md = $this->metadata->getColumnsMetadata($sql);

        $this->assertEquals(false, $md['test_string']->isPrimary());
        $this->assertEquals(Column\Type::TYPE_STRING, $md['test_string']->getDatatype());
        $this->assertEquals(null, $md['test_string']->getTableName());
        $this->assertEquals(false, $md['test_string']->isNullable());
        $this->assertEquals(null, $md['test_string']->getTableAlias());
        $this->assertEquals(1, $md['test_string']->getOrdinalPosition());
        $this->assertEquals('def', $md['test_string']->getCatalog());

        $this->assertEquals(Column\Type::TYPE_DECIMAL, $md['test_calc']->getDatatype());
        $this->assertEquals(null, $md['test_calc']->getTableName());

        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['test_calc_2']->getDatatype());
        $this->assertEquals(false, $md['test_calc_2']->isAutoIncrement());
        $this->assertEquals(null, $md['test_calc_2']->getTableName());

        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['filesize']->getDatatype());
        $this->assertEquals('media', $md['filesize']->getTableName());
        $this->assertEquals('m', $md['filesize']->getTableAlias());

        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['container_id']->getDatatype());
        $this->assertEquals('media', $md['container_id']->getTableName());
        $this->assertEquals('m', $md['container_id']->getTableAlias());


        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['mcid']->getDatatype());
        $this->assertEquals('media_container', $md['mcid']->getTableName());
        $this->assertEquals('mc', $md['mcid']->getTableAlias());


        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['max(filemtime)']->getDatatype());
        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['max_time']->getDatatype());
        $this->assertEquals('INTEGER', $md['max_time']->getNativeDatatype());

        // Testing computed
        $this->assertTrue($md['min_time']->isComputed());
        $this->assertTrue($md['max_time']->isComputed());
        $this->assertTrue($md['avg_time']->isComputed());
        $this->assertTrue($md['files']->isComputed());
        $this->assertTrue($md['test_string']->isComputed());
        $this->assertTrue($md['test_float']->isComputed());
        $this->assertTrue($md['test_calc']->isComputed());
        $this->assertTrue($md['test_calc_2']->isComputed());
        $this->assertFalse($md['container_id']->isComputed());

        // TESTING Aliased

        $this->assertEquals('mcid', $md['mcid']->getAlias());
        $this->assertEquals('container_id', $md['mcid']->getName());
        $this->assertEquals('min_time', $md['min_time']->getName());
        $this->assertEquals('min_time', $md['min_time']->getAlias());

        // TEST if column is part of a group
        $this->assertTrue($md['count_media']->isGroup());
        $this->assertTrue($md['min_time']->isGroup());

        $this->assertTrue($md['max_time']->isGroup());
        $this->assertTrue($md['min(filemtime)']->isGroup());
        $this->assertTrue($md['max(filemtime)']->isGroup());

        // WARNING BUGS IN MYSQL (should be true)
        $this->assertFalse($md['avg(filemtime)']->isGroup());
        $this->assertFalse($md['avg_time']->isGroup());
        $this->assertFalse($md['files']->isGroup());
        $this->assertFalse($md['group_concat(filename)']->isGroup());

        // Various type returned by using functions
        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['count_media']->getDatatype());
        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['max_time']->getDatatype());
        $this->assertEquals(Column\Type::TYPE_INTEGER, $md['min_time']->getDatatype());
        $this->assertEquals(Column\Type::TYPE_DECIMAL, $md['avg_time']->getDatatype());

    }


    public function testMakeQueryEmpty()
    {
        $queries = array(
            'select 1, 2',
            'select 1 limit 10',
            'select media_id from media',
            'select media_id from media limit 1 offset 2',
            'select media_id from media
                 LimiT   10',
            '(select media_id from media
                 LimiT   10 )',


        );

        $mysqli = $this->adapter->getDriver()->getConnection()->getResource();

        foreach($queries as $query) {
            $sql = $this->invokeMethod($this->metadata, 'makeQueryEmpty', array($query));


            $stmt = $mysqli->prepare($sql);

            if (!$stmt) {
                $message = $mysqli->error;
                throw new \Exception("Sql is not correct : $message");
            }

            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            var_dump($sql);
            var_dump($num_rows);
            $stmt->close();


        }

    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
