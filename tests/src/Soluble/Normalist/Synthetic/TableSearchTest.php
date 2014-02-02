<?php

namespace Soluble\Normalist\Synthetic;

use Soluble\Db\Metadata\Source;
use Soluble\Db\Metadata\Exception;

use Zend\Db\Sql\Where;
use \Zend\Db\Sql\Predicate;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-02-01 at 12:55:35.
 */
class TableSearchTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var TableManager
     */
    protected $tableManager;


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
        $adapter = \SolubleTestFactories::getDbAdapter();
        $cache   = \SolubleTestFactories::getCacheStorage();
        $metadata = new Source\MysqlISMetadata($adapter);
        $metadata->setCache($cache);

        $this->tableManager = new TableManager($adapter);
        $this->tableManager->setMetadata($metadata);

        $this->table = $this->tableManager->table('product_category');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::limit
     */
    public function testLimit()
    {
        $results = $this->table->search()->limit(10)->toArray();
        $this->assertEquals(10, count($results));
        
    }

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::offset
     * @todo   Implement testOffset().
     */
    public function testOffset()
    {
        $rs1 = $this->table->search()->limit(10)->toArray();
        $this->assertEquals(10, count($rs1));
        
        $rs2 = $this->table->search()->limit(10)->offset(1)->toArray();
        $this->assertEquals(10, count($rs2));
        
        $this->assertEquals($rs1[1], $rs2[0]);
    }
    
    

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::columns
     */
    public function testColumns()
    {
        $results = $this->table->search()->columns(array('reference'))->limit(1)->toArray();
        $keys = array_keys($results[0]);
        $this->assertEquals(1, count($keys));
        $this->assertEquals('reference', $keys[0]);
    }

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::group
     * @todo   Implement testGroup().
     */
    public function testGroup()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::order
     */
    public function testOrder()
    {
        $rs1 = $this->table->search()
                        ->order(array('reference DESC'))
                        ->toArray();

        $rs2 = $this->table->search()
                        ->order(array('reference ASC'))
                        ->toArray();
        
        
        $this->assertInternalType('array', $rs1);
        $this->assertInternalType('array', $rs2);
        
        $firstrs1 = $rs1[0]['reference'];
        $lastrs2 = $rs2[count($rs2)-1]['reference'];
        
        $this->assertEquals($firstrs1, $lastrs2);
        
        
    }


    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::where
     */
    public function testWhere()
    {
        // Simple where
        $results = $this->table->search()
                        ->where(array('reference' => 'AC'))
                        ->order(array('reference DESC', 'category_id ASC'))
                        ->toArray();
        $this->assertInternalType('array', $results);
        $this->assertEquals(1, count($results));
        $this->assertEquals('AC', $results[0]['reference']);
        
        // Simple with constant
        $results = $this->table->search()
                        ->where('category_id = 12')
                        ->toArray();
        $this->assertEquals(1, count($results));
        $this->assertEquals('AC', $results[0]['reference']);
        
        // Advanced with testing null
        $results = $this->table->search()
                        ->where(array(
                                 'category_id' => 12,
                                 'updated_by' => null ))
                        ->toArray();
        $this->assertEquals('AC', $results[0]['reference']);
        

        $results = $this->table->search()
                        ->where(array(
                                 'category_id' => 12,
                                 'root' => null ))
                        ->toArray();
        $this->assertEquals(0, count($results));
        
        
        // Advanced with getting non null
        
        $results = $this->table->search()
                        ->where(array(
                                 'category_id' => 12,
                                 new Predicate\IsNotNull('root') 
                                ))
                        ->toArray();
        
        $this->assertEquals('AC', $results[0]['reference']);

        // Advanced with predicate IN
        
        $results = $this->table->search()
                        ->where(array(
                                 new Predicate\In('category_id', array(12, 10)) 
                                ))
                        ->order('category_id DESC')            
                        ->toArray();
        $this->assertEquals(2, count($results));
        $this->assertEquals(12, $results[0]['category_id']);
        $this->assertEquals(10, $results[1]['category_id']);
        

        // Advanced with operator constant
        $results = $this->table->search()
                ->where('category_id < 10')
                ->toArray();
        $test_min = true;
        foreach($results as $row) {
            if ($row['category_id'] > 9) {
                $test_min = false;
            }
        }
        $this->assertTrue($test_min);

        // advanced with range
        $results = $this->table->search()
                ->where('category_id < 10')
                ->where('category_id > 5')
                ->toArray();
        $test_min = true;
        $test_max = true;
        foreach($results as $row) {
            if ($row['category_id'] > 9) {
                $test_min = false;
            }
            if ($row['category_id'] < 5) {
                $test_max = false;
            }
        }
        $this->assertTrue($test_min);
        $this->assertTrue($test_max);
        
        // Advanced OR
        $results = $this->table->search()
                 ->where(array(
                            'reference' => 'AC',
                            'legacy_mapping' =>  'GT'
                          ),
                          Predicate\PredicateSet::OP_OR
                        )
                 ->order('reference ASC')
                ->toArray();
        $this->assertEquals(2, count($results));
        $this->assertEquals('AC', $results[0]['reference']);
        $this->assertEquals('GT', $results[1]['reference']);

        // Advanced OR version 2
        $results = $this->table->search()
                 ->where(array(
                            "reference =   'AC'",
                            "reference = 'GT'"
                          ),
                          Predicate\PredicateSet::OP_OR
                        )
                 ->order('reference ASC')
                ->toArray();
        
        $this->assertEquals(2, count($results));
        $this->assertEquals('AC', $results[0]['reference']);
        $this->assertEquals('GT', $results[1]['reference']);
        
        // Advanced where with closure
        $results = $this->table->search()
                        ->where(function (Where $where) {
                                   $where->like('reference', 'AC%');
                                })
                         ->columns(array('reference'))
                         ->limit(100)               
                        ->toArray();
                                
        $test_start = true;                        
        foreach ($results as $row) {
            if (!preg_match('/^AC/', $row['reference'])) {
                $test_start = false;
            }
        }
        $this->assertTrue($test_start);
        
    }
    
    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::orWhere
     */
    public function testOrWhere() {
        
        $results = $this->table->search()
                 ->orWhere(array(
                            'reference' => 'AC',
                            'legacy_mapping' =>  'GT'
                          )
                        )
                 ->order('reference ASC')
                ->toArray();
        $this->assertEquals(2, count($results));
        $this->assertEquals('AC', $results[0]['reference']);
        $this->assertEquals('GT', $results[1]['reference']);
    }    
    

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::join
     */
    public function testJoin()
    {
        
        
        //$results = $this->table->join('product_category_translation', '')
        
    }

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::getSelect
     */
    public function testGetSelect()
    {
        $select = $this->table->search()->getSelect();
        $this->assertInstanceOf('Soluble\Db\Sql\Select', $select);
    }

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::getSql
     * @todo   Implement testGetSql().
     */
    public function testGetSql()
    {
        $sql = $this->table->search()->getSql();
        $this->assertInternalType('string', $sql);
        $this->assertContains('SELECT', $sql);
    }

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::toJson
     */
    public function testToJson()
    {
        $results = $this->table->search()->toJson();
        $this->assertInternalType('string', $results);
        $decoded = json_decode($results, $assoc=true);
        
        $results = $this->table->search()->toArray();
        $this->assertEquals($results, $decoded);
        
    }

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::toArray
     */
    public function testToArray()
    {
        $results = $this->table->search()->toArray();
        $this->assertInternalType('array', $results);
    }

    /**
     * @covers Soluble\Normalist\Synthetic\TableSearch::toArrayColumn
     */
    public function testToArrayColumn()
    {
        $results = $this->table->search()
                        ->where(array('reference' => 'AC'))
                        ->order(array('reference DESC', 'category_id ASC'))
                        ->toArrayColumn('category_id', 'reference');
        $this->assertInternalType('string', $results['AC']);
        $this->assertArrayHasKey('AC', $results);
        $this->assertEquals(12, $results['AC']);        
    }
    
}
