<?php
/**
 *  Soluble Components (http://belgattitude.github.io/solublecomponents)
 *
 *  @link      http://github.com/belgattitude/solublecomponents for the canonical source repository
 *  @copyright Copyright (c) 2013-2014 Sébastien Vanvelthem
 *  @license   https://github.com/belgattitude/solublecomponents/blob/master/LICENSE.txt MIT License
 */

namespace Soluble\Normalist\Synthetic\Table;

use Soluble\Normalist\Synthetic\Table;
use Soluble\Normalist\Synthetic\Record;
use Soluble\Normalist\Synthetic\Exception;

class Relation
{
    /**
     *
     * @var Table
     */
    protected $table;

    /**
     *
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }


    /**
     * Return parent record
     *
     * @throws Exception\LogicException
     * @throws Exception\RelationNotFoundException
     *
     * @param Record $record
     * @param string $parent_table
     * @return Record|false
     */
    public function getParent(Record $record, $parent_table)
    {
        if ($record->getState() == Record::STATE_DELETED) {
            throw new Exception\LogicException(__METHOD__ . ": Logic exception, cannot operate on record that was deleted");
        }

        $tableName = $this->table->getTableName();
        $relations = $this->table->getTableManager()->metadata()->getRelations($tableName);
        //$rels = array();
        foreach ($relations as $column => $parent) {
            if ($parent['referenced_table'] == $parent_table) {
                // @todo, check the case when
                // table has many relations to the same parent
                // we'll have to throw an exception
                $record = $this->table->getTableManager()->table($parent_table)->findOneBy(array(
                    $parent['referenced_column'] => $record->offsetGet($column)
                ));
                return $record;
            }
        }
        throw new Exception\RelationNotFoundException(__METHOD__ . ": Cannot find parent relation between table '$tableName' and '$parent_table'");
    }
}
