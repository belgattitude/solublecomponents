<?php

namespace Soluble\Normalist\Synthetic;

use Soluble\Db\Metadata\Source;

class TableTest extends \PHPUnit_Framework_TestCase
{
    //protected $recordclass = 'ArrayObject';
    protected $recordclass = 'Soluble\Normalist\Synthetic\Record';

    /**
     * @var TableManager
     */
    protected $tableManager;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //$adapter = \SolubleTestFactories::getDbAdapter();
        //$cache   = \SolubleTestFactories::getCacheStorage();
        //$metadata = new Source\MysqlISMetadata($adapter);
        //$metadata->setCache($cache);
        //$metadata = new Source\MysqlInformationSchema($adapter);
        //$this->tableManager = new TableManager($adapter);
        $this->tableManager = \SolubleTestFactories::getTableManager();
        //$this->adapter = $this->tableManager->getDbAdapter();
        //$this->tableManager->setMetadata($metadata);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->tableManager);
    }

    public function testGetTableManager()
    {
        $table = $this->tableManager->table('media');
        $tm = $table->getTableManager();
        $this->assertInstanceOf('Soluble\Normalist\Synthetic\TableManager', $tm);
        $this->assertEquals($this->tableManager->metadata(), $tm->metadata());
        $this->assertEquals($this->tableManager, $tm);
    }


    public function testConstructThrowsInvalidArgumentException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');

        $table = new Table(['cool'], $this->tableManager);
    }

    public function testConstructThrowsInvalidArgumentException2()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');

        $table = new Table('', $this->tableManager);
    }

    public function testConstructThrowsInvalidArgumentException3()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');

        $table = new Table(" \n", $this->tableManager);
    }


    public function testRecord()
    {
        $tm = $this->tableManager;
        $product = $tm->table('product');
        $record = $product->record();
        $this->assertInstanceOf($this->recordclass, $record);
        $this->assertEquals(Record::STATE_NEW, $record->getState());
    }

    public function testRecordThrowsColumnNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\ColumnNotFoundException');
        $tm = $this->tableManager;
        $product = $tm->table('product');
        $record = $product->record(['invalid_column' => 'value'], false);
        $this->assertInstanceOf($this->recordclass, $record);
    }

    public function testRelation()
    {
        $tm = $this->tableManager;
        $product = $tm->table('product');
        $relation = $product->relation();
        $this->assertInstanceOf('Soluble\Normalist\Synthetic\Table\Relation', $relation);
    }

    public function testGetPrimaryKey()
    {
        $medias = $this->tableManager->table('media');
        $pk = $medias->getPrimaryKey();
        $this->assertEquals('media_id', $pk);

        // re-run for testing cached version
        $pk = $medias->getPrimaryKey();
        $this->assertEquals('media_id', $pk);
    }

    public function testGetColumnsInformation()
    {
        $table = $this->tableManager->table('product_category');
        $columns = $table->getColumnsInformation();
        $expected = [
            'category_id', 'parent_id', 'reference', 'slug', 'title',
            'description', 'sort_index', 'icon_class', 'lft', 'rgt',
            'root', 'lvl', 'created_at', 'updated_at', 'created_by',
            'updated_by', 'legacy_mapping', 'legacy_synchro_at'
        ];
        $keys = array_keys($columns);
        $this->assertEquals($expected, $keys);
    }

    public function testSelect()
    {
        $select = $this->tableManager->table('product_category')->select();
        $this->assertInstanceOf('\Soluble\Db\Sql\Select', $select);
    }

    public function testFind()
    {
        $table = $this->tableManager->table('product_category');
        $record = $table->find(1);
        $this->assertInstanceOf($this->recordclass, $record);
        $this->assertEquals(1, $record['category_id']);

        $record = $table->find(984546465);
        $this->assertFalse($record);
    }

    public function testFindOrFail()
    {
        $table = $this->tableManager->table('product_category');
        $record = $table->findOrFail(1);
        $this->assertInstanceOf($this->recordclass, $record);
        $this->assertEquals(1, $record['category_id']);
    }

    public function testFindThrowsInvalidArgumentException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('product_category');
        $record = $table->find(['cool']);
    }

    public function testFindThrowsInvalidArgumentException2()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('test_table_with_multipk');
        $record = $table->find(['cool']);
    }

    public function testFindThrowsInvalidArgumentException3()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('test_table_with_multipk');
        $record = $table->find(1);
    }

    public function testFindThrowsPrimaryKeyNotFoundException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\PrimaryKeyNotFoundException');
        $table = $this->tableManager->table('test_table_without_pk');

        $record = $table->find('cool0');
    }

    public function testFindOrFailThrowsNotFoundException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\NotFoundException');
        $table = $this->tableManager->table('product_category');
        $record = $table->findOrFail('cool');
    }

    public function testFindOneBy()
    {
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneBy(['category_id' => 12]);
        $this->assertInstanceOf($this->recordclass, $record);

        $this->assertEquals(12, $record['category_id']);

        $record = $table->find(984546465);
        $this->assertFalse($record);
    }
    /*
    public function testFindOneByThrowsZendInvalidArgumentException()
    {
        $this->setExpectedException('\Zend\Db\Sql\Exception\InvalidArgumentException');
        
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneBy(1);
        
    }
     *
     */


    public function testFindOneByThrowsColumnNotFoundException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\ColumnNotFoundException');
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneBy(['column_not_exists' => 50]);
    }

    public function testFindOneByThrowsInvalidArgumentException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneBy('count(*) <> media_id');
    }

    public function testFindOneByThrowsMultipleMatchesException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\MultipleMatchesException');
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneBy(['sort_index' => 50]);
    }

    public function testFindOneByOrFail()
    {
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneByOrFail(['category_id' => 12]);
        $this->assertInstanceOf($this->recordclass, $record);
        $this->assertEquals(12, $record['category_id']);
    }

    public function testFindOneByOrFailThrowsNotFoundException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\NotFoundException');
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneByOrFail(['category_id' => 'cool']);
    }

    public function testFindOneByOrFailThrowsColumnNotFoundException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\ColumnNotFoundException');
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneByOrFail(['column_not_exists' => 50]);
    }

    public function testFindOneByOrFailThrowsInvalidArgumentException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneByOrFail('count(*) <> media_id');
    }

    public function testFindOneByOrFailThrowsMultipleMatchesException()
    {
        $this->setExpectedException('\Soluble\Normalist\Synthetic\Exception\MultipleMatchesException');
        $table = $this->tableManager->table('product_category');
        $record = $table->findOneByOrFail(['sort_index' => 50]);
    }

    public function testCount()
    {
        $tm = $this->tableManager;
        $table = $tm->table('product_category');
        $count = $table->count();
        $this->assertInternalType('integer', $count);
        $this->assertEquals(1541, $count);

        $table = $tm->table('product_media');
        $count = $table->count();
        $this->assertEquals(0, $count);
    }

    public function testCountBy()
    {
        $tm = $this->tableManager;
        $table = $tm->table('product_category');
        $matching_count = $table->countBy(true);
        $total_count = $table->count();
        $this->assertEquals($total_count, $matching_count);
    }

    public function testExists()
    {
        $table = $this->tableManager->table('product_category');
        $exists = $table->exists(1);
        $this->assertTrue($exists);
        $this->assertInternalType('boolean', $exists);

        $exists = $table->exists(5464546545454);
        $this->assertFalse($exists);
        $this->assertInternalType('boolean', $exists);
    }

    public function testExistsThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');

        $table = $this->tableManager->table('product_category');
        $exists = $table->exists([10, 10]);
    }

    public function testExistsBy()
    {
        $pc = $this->tableManager->table('product_category');
        $exists = $pc->existsBy(['category_id' => 1]);
        $this->assertTrue($exists);
        $this->assertInternalType('boolean', $exists);

        $exists = $pc->existsBy(['category_id' => 'cool']);
        $this->assertFalse($exists);
        $this->assertInternalType('boolean', $exists);
    }

    public function testExistsByThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');

        $table = $this->tableManager->table('media');

        $exists = $table->existsBy(['qlskjdlk', 10]);
    }

    public function testSearch()
    {
        $table = $this->tableManager->table('product_category');
        $search = $table->search();
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\TableSearch', $search);
    }

    public function testInsertThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('media');
        $table->insert('cool');
    }

    public function testInsertThrowsColumnNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\ColumnNotFoundException');
        $table = $this->tableManager->table('media');

        $data = [
            'media_id' => 10,
            'column_not_exists' => 1
        ];
        $table->insert($data);
    }

    public function testInsertThrowsNoPrimaryKeyException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\PrimaryKeyNotFoundException');
        $table = $this->tableManager->table('test_table_without_pk');

        $data = [
            'test_field' => 20,
            'test_field2' => 10
        ];
        $table->insert($data);
    }

    public function testInsertThrowsForeignKeyException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\ForeignKeyException');
        $table = $this->tableManager->table('media');
        $data = [
            'filename' => 'phpunit_test.pdf',
            'filemtime' => 111000,
            'filesize' => 5000,
            'container_id' => 212313132132132121
        ];
        $table->insert($data);
    }

    public function testDelete()
    {
        $medias = $this->tableManager->table('media');
        $nb = $medias->delete(4546465456464);
        $this->assertEquals(0, $nb);

        $media = $medias->findOneBy(['legacy_mapping' => 'tobedeleted_phpunit_testdelete']);
        if ($media) {
            $medias->deleteOrFail($media['media_id']);
        }

        $data = $this->createMediaRecordData('tobedeleted_phpunit_testdelete');
        $media = $medias->insert($data);
        $nb = $medias->delete($media['media_id']);
        $this->assertEquals(1, $nb);

        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $stdClass = new \stdClass();
        $medias->delete($stdClass);
    }

    public function testFindThrowsInvalidArgumentException5()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $medias = $this->tableManager->table('media');
        $medias->find(new \stdClass());
    }

    public function testDeleteBy()
    {
        $tm = $this->tableManager;
        $ttwuk = $tm->table('test_table_with_unique_key');
        $multi_key = [
            'unique_id_1' => 100,
            'unique_id_2' => 900
        ];
        $data = array_merge($multi_key, ['comment' => 'cool']);
        $record = $ttwuk->findOneBy($multi_key);

        if ($record) {
            $ttwuk->deleteBy($multi_key);
        }

        $record = $ttwuk->insert($data);
        $this->assertEquals($multi_key['unique_id_1'], $record['unique_id_1']);
        $this->assertEquals($multi_key['unique_id_2'], $record['unique_id_2']);

        $ttwuk->deleteBy($multi_key);

        $this->assertFalse($ttwuk->exists($record['id']));
    }

    public function testDeleteThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $medias = $this->tableManager->table('media');
        $nb = $medias->delete(['cool']);
    }

    public function testDeleteOrFail()
    {
        $medias = $this->tableManager->table('media');

        $media = $medias->findOneBy(['legacy_mapping' => 'tobedeleted_phpunit_testdelete']);
        if ($media) {
            $medias->delete($media['media_id']);
        }

        $data = $this->createMediaRecordData('tobedeleted_phpunit_testdelete');
        $media = $medias->insert($data);
        $ret = $medias->deleteOrFail($media['media_id']);
        $this->assertInstanceOf('Soluble\Normalist\Synthetic\Table', $ret);
    }

    public function testDeleteOrFailThrowsNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\NotFoundException');
        $medias = $this->tableManager->table('media');
        $medias->deleteOrFail(987894546561);
    }

    public function testInsertThrowsDuplicateEntryException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\DuplicateEntryException');
        $medias = $this->tableManager->table('media');
        $media = $medias->findOneBy(['legacy_mapping' => 'duplicate_key_phpunit']);
        if ($media) {
            $medias->delete($media['media_id']);
        }

        $data = [
            'filename' => 'phpunit_test.pdf',
            'filemtime' => 111000,
            'filesize' => 5000,
            'container_id' => 1,
            'legacy_mapping' => 'duplicate_key_phpunit'
        ];
        $medias->insert($data);

        // Will throw the Exception
        $medias->insert($data);
    }

    public function testInsertThrowsRuntimeException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\RuntimeException');
        // Testing in non insertable record
        $data = [
            'non_insertable_column' => 10
        ];
        $ttwt = $this->tableManager->table('test_table_with_trigger');
        $ttwt->insert($data);
    }

    public function testInsertThrowsNotNullException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\NotNullException');
        // Testing in non insertable record
        // Insert with not null
        $ttwnn = $this->tableManager->table('test_table_with_non_null');
        $data = ['non_null_column' => null];
        $ttwnn->insert($data);
    }

    public function testInsert()
    {
        $legacy_mapping = 'phpunit_testInsert';
        $medias = $this->tableManager->table('media');
        $data = $this->createMediaRecordData($legacy_mapping);
        $media = $medias->findOneBy(['legacy_mapping' => $data['legacy_mapping']]);
        if ($media) {
            $medias->delete($media['media_id']);
        }

        $data['filename'] = 'my_test_filename';

        $media = $medias->insert($data);
        $this->assertEquals($data['filename'], $media['filename']);

        // Test with arrayObject
        $data = new \ArrayObject($this->createMediaRecordData($legacy_mapping));
        $media = $medias->findOneBy(['legacy_mapping' => $data['legacy_mapping']]);

        if ($media) {
            $medias->delete($media['media_id']);
        }

        $data['media_id'] = 999999999;
        $media = $medias->find(999999999);
        if ($media) {
            $medias->delete(999999999);
        }
        $media = $medias->insert($data);
        $this->assertEquals(999999999, $data['media_id']);
        $medias->delete(999999999);

        // On a table with multiple pk

        $tm = $this->tableManager;
        $ttwm = $tm->table('test_table_with_multipk');
        $multi_key = [
            'pk_1' => 1,
            'pk_2' => 1,
        ];
        $rec = $ttwm->find($multi_key);

        if ($rec) {
            $ttwm->delete($multi_key);
        }

        $record = $ttwm->insert(array_merge($multi_key, ['comment' => 'mmmmm']));
        $this->assertEquals('mmmmm', $record['comment']);
        $this->assertEquals('1', $record['pk_1']);
        $this->assertEquals('1', $record['pk_2']);
    }


    public function testInsertWithValidateDataType()
    {
        $legacy_mapping = 'phpunit_testInsert';
        $medias = $this->tableManager->table('media');
        $data = $this->createMediaRecordData($legacy_mapping);
        $media = $medias->findOneBy(['legacy_mapping' => $data['legacy_mapping']]);
        if ($media) {
            $medias->delete($media['media_id']);
        }

        $data['filename'] = 'my_test_filename';

        $media = $medias->insert($data, $validate = true);
        $this->assertEquals($data['filename'], $media['filename']);
    }

    public function testUpdateWithValidateDataType()
    {
        $legacy_mapping = 'phpunit_testUpdate_1';
        $tm = $this->tableManager;
        $medias = $tm->table('media');
        $data = $this->createMediaRecordData($legacy_mapping);
        $record = $medias->insertOnDuplicateKey($data);

        $media = $medias->findOneBy(['legacy_mapping' => $data['legacy_mapping']]);

        $affectedRows = $medias->update(['filename' => 'phpunit'], ['media_id' => $media['media_id']], null, $validate = true);
        $this->assertEquals(1, $affectedRows);
    }

    public function testUpdate()
    {
        $legacy_mapping = 'phpunit_testUpdate_1';
        $tm = $this->tableManager;
        $medias = $tm->table('media');
        $data = $this->createMediaRecordData($legacy_mapping);
        $record = $medias->insertOnDuplicateKey($data);

        $media = $medias->findOneBy(['legacy_mapping' => $data['legacy_mapping']]);

        $affectedRows = $medias->update(['filename' => 'phpunit'], ['media_id' => $media['media_id']]);
        $this->assertEquals(1, $affectedRows);

        $new_media = $medias->find($media['media_id']);

        $this->assertEquals($new_media['filename'], 'phpunit');

        $data = new \ArrayObject($this->createMediaRecordData('phpunit_testUpdate_2'));
        $medias->insertOnDuplicateKey($data);
        $media = $medias->findOneBy(['legacy_mapping' => $data['legacy_mapping']]);

        $affectedRows = $medias->update(new \ArrayObject(['filename' => 'phpunit']), ['media_id' => $media['media_id']]);
        $this->assertEquals(1, $affectedRows);

        $new_media = $medias->find($media['media_id']);

        $this->assertEquals($new_media['filename'], 'phpunit');

        // test mass update

        $affected = $medias->update(['created_by' => null], true);
        $this->assertEquals(1, $affectedRows);

        $tm->transaction()->start();

        $affected = $medias->update(['created_by' => 'unit_rollback'], true);

        $results = $medias->search()->limit(10)->where(['created_by' => 'unit_rollback'])->toArray();
        $this->assertEquals(10, count($results));
        $count = $medias->count();
        $count_matching = $medias->countBy(['created_by' => 'unit_rollback']);
        $this->assertEquals($count, $count_matching);

        $tm->transaction()->rollback();

        $count_matching = $medias->countBy(['created_by' => 'unit_rollback']);
        $this->assertEquals(0, $count_matching);

        // On a table with multiple pk

        $tm = $this->tableManager;
        $ttwm = $tm->table('test_table_with_multipk');
        $multi_key = [
            'pk_1' => 1,
            'pk_2' => 1,
        ];
        $rec = $ttwm->findOneBy($multi_key);
        if ($rec) {
            $ttwm->deleteBy($multi_key);
        }
        $record = $ttwm->insert(array_merge($multi_key, ['comment' => 'mmmmm']));
        $this->assertEquals('mmmmm', $record['comment']);
        $this->assertEquals('1', $record['pk_1']);
        $this->assertEquals('1', $record['pk_2']);

        $affected = $ttwm->update(['comment' => 'aaaaaa'], $multi_key);
        $this->assertEquals(1, $affectedRows);
        $record = $ttwm->find($multi_key);
        $this->assertEquals('1', $record['pk_1']);
        $this->assertEquals('1', $record['pk_2']);
        $this->assertEquals('aaaaaa', $record['comment']);
    }

    /**
     * # REMOVED BECAUSE MYSQL is quite messy with not null value
     * # it replace null by empty strings in an update clause
     *
     * # In case of insert it works as expected
     * # - INSERT INTO `test_table_with_non_null` (`id`, `non_null_column`) VALUES (NULL, null);
     * # -> return #1048 - Column 'non_null_column' cannot be null
     * # But in case of update
     * # - INSERT INTO `test_table_with_non_null` (`id`, `non_null_column`) VALUES (1000, 'cool');
     * # - UPDATE `test_table_with_non_null` SET `non_null_column` = NULL WHERE `id` = '1000'
     * # MYSQL ACCEPTS !!!!
     *
      public function testUpdateThrowsNotNullException()
      {
      $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\NotNullException');
      $ttwnn = $this->tableManager->table('test_table_with_non_null');
      $data = array('non_null_column' => 'test');
      $record = $ttwnn->insert($data);
      $data = array(
      'id' => $record['id'],
      'non_null_column' => null
      );

      $affected = $ttwnn->update($data, array('id' => $record['id']));

      }
     */
    public function testUpdateThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $legacy_mapping = 'phpunit_testUpdate_10';
        $tm = $this->tableManager;
        $medias = $tm->table('media');
        $data = $this->createMediaRecordData($legacy_mapping);
        $record = $medias->insertOnDuplicateKey($data);

        $media = $medias->findOneBy(['legacy_mapping' => $data['legacy_mapping']]);

        $affectedRows = $medias->update('cool', ['media_id' => $media['media_id']]);
    }


    public function testInsertOnDuplicateKeyWithValidateDataType()
    {
        $legacy_mapping = "phpunit_testInsertOnDuplicateKeyUpdate";
        $data = $this->createMediaRecordData($legacy_mapping);
        $tm = $this->tableManager;

        $medias = $tm->table('media');
        $media = $medias->findOneBy(['legacy_mapping' => $legacy_mapping]);

        if ($media) {
            $medias->delete($media['media_id']);
        }

        $record = $medias->insertOnDuplicateKey($data, ['legacy_mapping'], $validate = true);
        $pk = $record['media_id'];
        $this->assertTrue($medias->exists($pk));
    }

    public function testInsertOnDuplicateKey()
    {
        $legacy_mapping = "phpunit_testInsertOnDuplicateKeyUpdate";
        $data = $this->createMediaRecordData($legacy_mapping);
        $tm = $this->tableManager;

        $medias = $tm->table('media');
        $media = $medias->findOneBy(['legacy_mapping' => $legacy_mapping]);

        if ($media) {
            $medias->delete($media['media_id']);
        }

        $record = $medias->insertOnDuplicateKey($data, ['legacy_mapping']);
        $pk = $record['media_id'];
        $this->assertTrue($medias->exists($pk));


        $data['filesize'] = 8888;
        $record = $medias->insertOnDuplicateKey($data, ['legacy_mapping']);
        $this->assertEquals(8888, $record['filesize']);
        $this->assertEquals($pk, $record['media_id']);

        $data['filesize'] = 5000;
        $record = $medias->insertOnDuplicateKey($data);
        $this->assertEquals(5000, $record['filesize']);
        $this->assertEquals($pk, $record['media_id']);

        // TEsting with primary key set

        $legacy_mapping = "phpunit_testInsertOnDuplicateKeyUpdate";
        $data = $this->createMediaRecordData($legacy_mapping);
        $medias = $tm->table('media');
        $media = $medias->findOneBy(['legacy_mapping' => $legacy_mapping]);
        if ($media) {
            $medias->delete($media['media_id']);
        }
        $media = $medias->insert($data);

        $data['media_id'] = $media['media_id'];
        $data['filename'] = 'coolcool';
        $new_media = $medias->insertOnDuplicateKey($data);
        $this->assertEquals('coolcool', $new_media['filename']);
        $this->assertEquals($media['media_id'], $new_media['media_id']);

        // Testing with unique constraint
        $ttwuk = $tm->table('test_table_with_unique_key');
        $multi_key = [
            'unique_id_1' => 1000,
            'unique_id_2' => 9000
        ];
        $data = array_merge($multi_key, ['comment' => 'cool']);
        $record = $ttwuk->findOneBy($multi_key);

        if ($record) {
            $ttwuk->deleteBy($multi_key);
        }

        $record = $ttwuk->insert($data);

        $new_record = $ttwuk->insertOnDuplicateKey($data);
        $this->assertEquals($multi_key['unique_id_1'], $new_record['unique_id_1']);
        $this->assertEquals($multi_key['unique_id_2'], $new_record['unique_id_2']);
        $this->assertEquals('cool', $new_record['comment']);

        $data['id'] = $new_record['id'];
        $data['comment'] = null;

        $new_record = $ttwuk->insertOnDuplicateKey($data);
        $this->assertEquals($multi_key['unique_id_1'], $new_record['unique_id_1']);
        $this->assertEquals($multi_key['unique_id_2'], $new_record['unique_id_2']);
        $this->assertEquals(null, $new_record['comment']);
        $this->assertEquals($data['id'], $new_record['id']);
    }

    public function testInsertOnDuplicateKeyThrowsColumnNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\ColumnNotFoundException');

        $legacy_mapping = "phpunit_testInsertOnDuplicateKeyUpdate";
        $data = $this->createMediaRecordData($legacy_mapping);
        $tm = $this->tableManager;

        $medias = $tm->table('media');
        $media = $medias->findOneBy(['legacy_mapping' => $legacy_mapping]);

        if ($media) {
            $medias->delete($media['media_id']);
        }

        $data['unexistent_column'] = 'cool';
        $record = $medias->insertOnDuplicateKey($data, ['legacy_mapping']);
    }

    public function testInsertOnDuplicateKeyThrowsRuntimeException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\RuntimeException');

        // Testing in non insertable record
        $data = [
            'non_insertable_column' => 10
        ];
        $ttwt = $this->tableManager->table('test_table_with_trigger');
        $ttwt->insertOnDuplicateKey($data);
    }

    public function testAll()
    {
        $tm = $this->tableManager->table('media');
        $rs = $tm->all();
        $this->assertInstanceOf('Soluble\Normalist\Synthetic\ResultSet\ResultSet', $rs);
        $count = $tm->count();

        $this->assertEquals($count, count($rs));
    }

    public function testGetRelations()
    {
        $relations = $this->tableManager->table('product')->getRelations();

        $this->assertInternalType('array', $relations);
        $this->assertArrayHasKey('brand_id', $relations);
        $this->assertArrayHasKey('referenced_column', $relations['unit_id']);
        $this->assertArrayHasKey('referenced_table', $relations['unit_id']);

        $this->assertArrayHasKey('constraint_name', $relations['unit_id']);
    }

    public function testGetPrimaryKeys()
    {
        $tm = $this->tableManager;
        $ttwm = $tm->table('test_table_with_multipk');
        $pks = $ttwm->getPrimaryKeys();
        $this->assertInternalType('array', $pks);
        $this->assertEquals('pk_1', $pks[0]);
        $this->assertEquals('pk_2', $pks[1]);
    }

    public function testGetPrimaryKeyThrowsMultiplePrimaryKeysFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\MultiplePrimaryKeysFoundException');
        $tm = $this->tableManager;
        $ttwm = $tm->table('test_table_with_multipk');
        $pks = $ttwm->getPrimaryKey();
    }

    public function testGetPrefixedTableName()
    {
        $tm = $this->tableManager;
        $ttwm = $tm->table('test_table_with_multipk');
        $name = $ttwm->getPrefixedTableName();
        $this->assertEquals('test_table_with_multipk', $name);
    }

    /**
     * Return a media record suitable for database insertion
     * @return array
     */
    protected function createMediaRecordData($legacy_mapping = null)
    {
        $tm = $this->tableManager;
        $container = $tm->table('media_container')->findOneBy(['reference' => 'PRODUCT_MEDIAS']);
        $container_id = $container['container_id'];

        $data = [
            'filename' => 'phpunit_test.pdf',
            'filemtime' => 111000,
            'filesize' => 5000,
            'container_id' => $container_id,
            'legacy_mapping' => $legacy_mapping
        ];
        return $data;
    }
}
