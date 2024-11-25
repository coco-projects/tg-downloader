<?php

    use Coco\tableManager\TableRegistry;

    require '../common.php';

//    $method = TableRegistry::makeMethod($manager->getMessageTable()->getFieldsSqlMap());
//    $method = TableRegistry::makeMethod($manager->getTypeTable()->getFieldsSqlMap());
//    $method = TableRegistry::makeMethod($manager->getPostTable()->getFieldsSqlMap());
//    $method = TableRegistry::makeMethod($manager->getFileTable()->getFieldsSqlMap());

    $method = TableRegistry::makeMethod($manager->getAccountTable()->getFieldsSqlMap());
//    $method = TableRegistry::makeMethod($manager->getPagesTable()->getFieldsSqlMap());

    print_r($method);
