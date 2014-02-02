<?php

/**
 *  Soluble Components (http://belgattitude.github.io/solublecomponents)
 *  
 *  @link      http://github.com/belgattitude/solublecomponents for the canonical source repository
 *  @copyright Copyright (c) 2013-2014 Sébastien Vanvelthem
 *  @license   https://github.com/belgattitude/solublecomponents/blob/master/LICENSE.txt MIT License
*/


    /**
     * Set table alias when referencing this table in a join
     *
     * @param string $table_alias alias name for table when using join
     * @throws Exception\InvalidArgumentException
     * @return Table
     */
    public function setTableAlias($table_alias)
    {
        if (!is_string($table_alias)) {
            throw new Exception\InvalidArgumentException("Table alias must be a string");
        }

        if (!preg_match('/^[A-Za-z]([A-Za-z0-9_-])+$/', $table_alias)) {
            throw new Exception\InvalidArgumentException("Invalid table alias '$table_alias'");
        }
        $this->table_alias = $table_alias;
        return $this;
    }


    /**
     * Return the table alias, if not set will return the table name
     *
     * @return string
     */
    public function getTableAlias()
    {
        if ($this->table_alias == '') {
            return $this->getTableName();
        }
        return $this->table_alias;
    }


    /**
     * @covers Soluble\Normalist\Synthetic\Table::setTableAlias
     */
    public function testSetTableAlias()
    {
        // Test 1: with table alias set
        $table = $this->tableManager->table('product_category');
        $tableAlias = $table->setTableAlias('pc')->getTableAlias();
        $this->assertEquals('pc', $tableAlias);
        
        // Test 2: with table alias not set 
        $table = $this->tableManager->table('product_category');
        $tableAlias = $table->getTableAlias();
        $this->assertEquals('product_category', $tableAlias);
        
    }
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::getTableAlias
     */
    public function testSetTableAliasThrowsException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('product_category');
        $table->setTableAlias('88');
    }
    
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::setTableAlias
     */
    public function testSetTableAliasThrowsInvalidArgumentException()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('product_category');
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Table', $table);
        $tableAlias = $table->setTableAlias('77')->getTableAlias();
        $this->assertEquals('pc', $tableAlias);
    }
    
    /**
     * @covers Soluble\Normalist\Synthetic\Table::setTableAlias
     */
    public function testSetTableAliasThrowsInvalidArgumentExceptionWithEmpty()
    {
        $this->setExpectedException('Soluble\Normalist\Synthetic\Exception\InvalidArgumentException');
        $table = $this->tableManager->table('product_category');
        $this->assertInstanceOf('\Soluble\Normalist\Synthetic\Table', $table);
        $tableAlias = $table->setTableAlias('')->getTableAlias();
        $this->assertEquals('pc', $tableAlias);
    }
    
