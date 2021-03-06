<?php

namespace Soluble\Normalist\Synthetic\Table;

use Soluble\Normalist\Synthetic\Table;
use Soluble\Normalist\Synthetic\TableManager;
use Zend\Db\Adapter\Adapter;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-02-08 at 14:32:16.
 */
class RelationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TableManager
     */
    protected $tableManager;


    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;


    /**
     *
     * @var Table
     */
    protected $table;



    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

        //$this->adapter = \SolubleTestFactories::getDbAdapter();

        //$cache   = \SolubleTestFactories::getCacheStorage();
        //$metadata = new Source\MysqlISMetadata($this->adapter);
        //$metadata = new Source\MysqlInformationSchema($this->adapter);
        //$metadata->setCache($cache);

        //$this->tableManager = new TableManager($this->adapter);
        //$this->tableManager->setMetadata($metadata);
        $this->tableManager = \SolubleTestFactories::getTableManager();
        $this->adapter = $this->tableManager->getDbAdapter();


        $this->table = $this->tableManager->table('product_category');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testGetParent()
    {
        $data = $this->createMediaRecordData('phpunit_testGetParent');
        $medias = $this->tableManager->table('media');
        $media = $medias->insertOnDuplicateKey($data, ['legacy_mapping']);

        $parent = $medias->relation()->getParent($media, "media_container");

        $this->assertEquals($media['container_id'], $parent['container_id']);
    }



    public function testGetParentThrowsRelationNotFoundException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\RelationNotFoundException');
        $data = $this->createMediaRecordData('phpunit_testGetParent');
        $medias = $this->tableManager->table('media');
        $media = $medias->insertOnDuplicateKey($data, ['legacy_mapping']);

        $parent = $medias->relation()->getParent($media, "product_category");
    }

    public function testGetParentThrowsLogicException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\LogicException');
        $data = $this->createMediaRecordData('phpunit_testGetParent');
        $medias = $this->tableManager->table('media');
        $media = $medias->insertOnDuplicateKey($data, ['legacy_mapping']);
        $media->delete();

        $parent = $medias->relation()->getParent($media, "media_container");
    }


    public function testGetParentThrowsTableNotFoundException2()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\RelationNotFoundException');
        $data = $this->createMediaRecordData('phpunit_testGetParent');
        $medias = $this->tableManager->table('media');
        $media = $medias->insertOnDuplicateKey($data, ['legacy_mapping']);

        $parent = $medias->relation()->getParent($media, "ptablenotexists");
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
        $data  = [
            'filename'  => 'phpunit_tablemanager.pdf',
            'filemtime' => 111000,
            'filesize'  => 5000,
            'container_id' => $container_id,
            'legacy_mapping' => $legacy_mapping
        ];
        return $data;
    }
}
