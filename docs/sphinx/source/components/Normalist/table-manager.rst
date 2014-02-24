:tocdepth: 3
TableManager
============



.. code-block:: php
   :linenos:

    <?php
    use Soluble\Normalist\Synthetic\TableManager;
    use Soluble\Db\Metadata\Source;
    use Zend\Db\Adapter\Adapter;


    $tm = new TableManager($adapter);
    $userTable = $tm->table('user');
    $userTable->find(1);

    $select = $tm->select();
    $select->from('user')->where(function(Select $select) {
         $select->where->like('name', 'Brit%');
         $select->order('name ASC')->limit(2);
    });

    $tm->transaction()->start();
    $tm->transaction()->commit();
    $tm->transaction()->rollback();

    $tm->getDbAdapter();

    $tm->setTablePrefix();
    $tm->getTablePrefix();
    $tm->getPrefixedTable($table);



    //Soluble\Db\Metadata\Source\AbstractSource
    $metadata = $tm->metadata();

