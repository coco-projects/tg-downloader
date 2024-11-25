<?php

    use Coco\tableManager\TableRegistry;

    require '../common.php';

    $sql = <<<'SQL'

DROP TABLE IF EXISTS `te_type` ;
CREATE TABLE `te_type` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(2600) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',
  `time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '文章分类表' ;

SQL;

    $arrDefine = TableRegistry::makeFieldsSqlMap($sql);

    print_r($arrDefine);
