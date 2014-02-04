<?php

namespace Soluble\FlexStore\Metadata\Column;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-02-04 at 13:55:32.
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Type
     */
    protected $type;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    public function testCreateColumnDefinitionThrowsUnsupportedDatatypeException()
    {
        $this->setExpectedException('Soluble\FlexStore\Metadata\Exception\UnsupportedDatatypeException');
        $type = Type::createColumnDefinition('NOTAVAILDTYPE', 'cool');
    }

}
