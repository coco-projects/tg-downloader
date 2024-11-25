<?php

    declare(strict_types = 1);

    namespace Coco\tgDownloader\tables;

    use Coco\tableManager\TableAbstract;

    class File extends TableAbstract
    {
        public string $comment = '文件信息';

        public array $fieldsSqlMap = [
            "post_id"        => "`__FIELD__NAME__` BIGINT (11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'post_id',",
            "file_size"      => "`__FIELD__NAME__` BIGINT (11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'file_size',",
            "file_name"      => "`__FIELD__NAME__` TEXT COLLATE utf8mb4_unicode_ci COMMENT '文件名',",
            "path"           => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '本地地址',",
            "media_group_id" => "`__FIELD__NAME__` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'media_group_id',",
            "ext"            => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '后缀',",
            "mime_type"      => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mime_type',",
            "time"           => "`__FIELD__NAME__` INT (10) UNSIGNED NOT NULL DEFAULT '0',",
        ];

        protected array $indexSentence = [
            "post_id"        => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
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

        public function setPostIdField(string $value): static
        {
            $this->setFeildName('post_id', $value);

            return $this;
        }

        public function getPostIdField(): string
        {
            return $this->getFieldName('post_id');
        }

        public function setPathField(string $value): static
        {
            $this->setFeildName('path', $value);

            return $this;
        }

        public function getPathField(): string
        {
            return $this->getFieldName('path');
        }

        public function setFileSizeField(string $value): static
        {
            $this->setFeildName('file_size', $value);

            return $this;
        }

        public function getFileSizeField(): string
        {
            return $this->getFieldName('file_size');
        }

        public function setFileNameField(string $value): static
        {
            $this->setFeildName('file_name', $value);

            return $this;
        }

        public function getFileNameField(): string
        {
            return $this->getFieldName('file_name');
        }

        public function setExtField(string $value): static
        {
            $this->setFeildName('ext', $value);

            return $this;
        }

        public function getExtField(): string
        {
            return $this->getFieldName('ext');
        }

        public function setMimeTypeField(string $value): static
        {
            $this->setFeildName('mime_type', $value);

            return $this;
        }

        public function getMimeTypeField(): string
        {
            return $this->getFieldName('mime_type');
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
