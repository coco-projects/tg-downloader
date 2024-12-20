<?php

    declare(strict_types = 1);

    namespace Coco\tgDownloader\tables;

    use Coco\tableManager\TableAbstract;

    class Type extends TableAbstract
    {
        public string $comment = '文件信息';

        public array $fieldsSqlMap = [
            "name"     => "`__FIELD__NAME__` VARCHAR(2600) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '分类名称',",
            "group_id" => "`__FIELD__NAME__` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '这个类转发到指定的群',",
            "time"     => "`__FIELD__NAME__` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',",
        ];

        protected array $indexSentence = [];

        public function setNameField(string $value): static
        {
            $this->setFeildName('name', $value);

            return $this;
        }

        public function getNameField(): string
        {
            return $this->getFieldName('name');
        }

        public function setGroupIdField(string $value): static
        {
            $this->setFeildName('group_id', $value);

            return $this;
        }

        public function getGroupIdField(): string
        {
            return $this->getFieldName('group_id');
        }

        public function setTimeField(string $value): static
        {
            $this->setFeildName('time', $value);

            return $this;
        }

        public function getTimeField(): string
        {
            return $this->getFieldName('time');
        }

    }
