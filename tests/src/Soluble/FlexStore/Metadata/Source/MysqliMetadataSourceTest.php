<?php

namespace Soluble\FlexStore\Metadata\Source;

use Soluble\FlexStore\Metadata\Column;

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

    
    
    public function testGetColumnsMetadataThrowsEmptyQueryException()
    {
        $this->setExpectedException('Soluble\FlexStore\Metadata\Exception\EmptyQueryException');
        $sql = "";
        $md = $this->metadata->getColumnsMetadata($sql);
    }

    public function testGetColumnsMetadataThrowsInvalidQueryException()
    {
        $this->setExpectedException('Soluble\FlexStore\Metadata\Exception\InvalidQueryException');
        $sql = "select * from sss";
        $md = $this->metadata->getColumnsMetadata($sql);

    }

    
    public function testGetColumnsMetadataNonCached()
    {
        $sql = "select id from test_table_types";
        $this->metadata->setStaticCache(false);
        $md = $this->metadata->getColumnsMetadata($sql);
        $this->metadata->setStaticCache(true);

    }

    
    public function testGetColumnsMetadataThrowsAmbiguousColumnException()
    {
        $this->setExpectedException('Soluble\FlexStore\Metadata\Exception\AmbiguousColumnException');
        $sql = "select id, id from test_table_types";
        $md = $this->metadata->getColumnsMetadata($sql);

    }    

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
        $this->assertEquals(true, $md['id']->isNumericUnsigned());
        $this->assertEquals(true, $md['id']->getNumericUnsigned());

        $this->assertEquals($md['test_varchar_255']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_varchar_255']->getNativeDatatype(), 'VARCHAR');
        $this->assertEquals($md['test_char_10']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals($md['test_char_10']->getNativeDatatype(), 'VARCHAR');
        
        // This does not work (cause utf8 store in multibyte)
        // @todo utf8 support in getCharacterMaximumLength
        //  Divide by 3
        // Sould be $this->assertEquals(10, $md['test_char_10']->getCharacterMaximumLength());
        // But returned
        $this->assertEquals(30, $md['test_char_10']->getCharacterMaximumLength());
        
        $this->assertGreaterThanOrEqual(10, $md['test_char_10']->getCharacterMaximumLength());

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
        $this->assertFalse($md['test_decimal_10_3']->getNumericUnsigned());
        $this->assertFalse($md['test_decimal_10_3']->isNumericUnsigned());

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

        $this->assertEquals(255, $md['test_tinyblob']->getCharacterOctetLength());
        $this->assertEquals(16777215, $md['test_mediumblob']->getCharacterOctetLength());
        $this->assertEquals(4294967295, $md['test_longblob']->getCharacterOctetLength());
        
        
        $this->assertEquals($md['test_enum']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals('ENUM', $md['test_enum']->getNativeDatatype());


        $this->assertEquals($md['test_set']->getDatatype(), Column\Type::TYPE_STRING);
        $this->assertEquals('SET', $md['test_set']->getNativeDatatype());



    }


    public function testGetColumnsMetadataWithDefaults()
    {

        $sql = "select * from test_table_with_default";
        $md = $this->metadata->getColumnsMetadata($sql);

        if (true) {
            // IN PHP 5.5 always return null (?)
            $this->assertEquals(null, $md['default_5']->getColumnDefault());
            $this->assertEquals(null, $md['default_cool']->getColumnDefault());
            $this->assertEquals(null, $md['default_yes']->getColumnDefault());
            
        } else {
            $this->assertEquals(5, $md['default_5']->getColumnDefault());
            $this->assertEquals('cool', $md['default_cool']->getColumnDefault());
            $this->assertEquals('yes', $md['default_yes']->getColumnDefault());
        }
        
    }

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
        
        $this->assertEquals(null, $md['test_string']->getSchemaName());
        $this->assertEquals($this->adapter->getCurrentSchema(), $md['filesize']->getSchemaName());

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
