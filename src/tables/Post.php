<?php

    declare(strict_types = 1);

    namespace Coco\tgDownloader\tables;

    use Coco\tableManager\TableAbstract;

    class Post extends TableAbstract
    {
        public string $comment = '文件信息';

        public array $fieldsSqlMap = [
            "type_id"        => "`__FIELD__NAME__` BIGINT (11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'type_id',",
            "contents"       => "`__FIELD__NAME__` LONGTEXT COLLATE utf8mb4_unicode_ci COMMENT '文本信息',",
            "media_group_id" => "`__FIELD__NAME__` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'media_group_id',",
            "date"           => "`__FIELD__NAME__` INT (10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '信息发送时间',",
            "time"           => "`__FIELD__NAME__` INT (10) UNSIGNED NOT NULL DEFAULT '0',",
        ];

        protected array $indexSentence = [
            "type_id"        => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "media_group_id" => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
        ];


        public function setMediaGroupIdField(string $value): static
        {
            $this->setFeildName('media_group_id', $value);

            return $this;
        }

        public function getMediaGroupIdField(): string
        {
            return $this->getFieldName('media_group_id');
        }

        public function setTypeIdField(string $value): static
        {
            $this->setFeildName('type_id', $value);

            return $this;
        }

        public function getTypeIdField(): string
        {
            return $this->getFieldName('type_id');
        }

        public function setContentsField(string $value): static
        {
            $this->setFeildName('contents', $value);

            return $this;
        }

        public function getContentsField(): string
        {
            return $this->getFieldName('contents');
        }

        public function setDateField(string $value): static
        {
            $this->setFeildName('date', $value);

            return $this;
        }

        public function getDateField(): string
        {
            return $this->getFieldName('date');
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
