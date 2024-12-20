<?php

    declare(strict_types = 1);

    namespace Coco\tgDownloader\tables;

    use Coco\tableManager\TableAbstract;

    class Account extends TableAbstract
    {
        public string $comment = 'telegraph 账号表';

        public array $fieldsSqlMap = [
            "short_name"   => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '简称',",
            "author_name"  => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作者名',",
            "author_url"   => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作者主页',",
            "access_token" => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'token',",
            "auth_url"     => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '修改url',",
            "page_count"   => "`__FIELD__NAME__` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '页面数量',",
            "revoke_time"  => "`__FIELD__NAME__` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '重置时间',",
            "time"         => "`__FIELD__NAME__` INT (10) UNSIGNED NOT NULL DEFAULT '0',",
        ];

        public function setShortNameField(string $value): static
        {
            $this->setFeildName('short_name', $value);

            return $this;
        }

        public function getShortNameField(): string
        {
            return $this->getFieldName('short_name');
        }

        public function setAuthorNameField(string $value): static
        {
            $this->setFeildName('author_name', $value);

            return $this;
        }

        public function getAuthorNameField(): string
        {
            return $this->getFieldName('author_name');
        }

        public function setAuthorUrlField(string $value): static
        {
            $this->setFeildName('author_url', $value);

            return $this;
        }

        public function getAuthorUrlField(): string
        {
            return $this->getFieldName('author_url');
        }

        public function setAccessTokenField(string $value): static
        {
            $this->setFeildName('access_token', $value);

            return $this;
        }

        public function getAccessTokenField(): string
        {
            return $this->getFieldName('access_token');
        }

        public function setAuthUrlField(string $value): static
        {
            $this->setFeildName('auth_url', $value);

            return $this;
        }

        public function getAuthUrlField(): string
        {
            return $this->getFieldName('auth_url');
        }

        public function setPageCountField(string $value): static
        {
            $this->setFeildName('page_count', $value);

            return $this;
        }

        public function getPageCountField(): string
        {
            return $this->getFieldName('page_count');
        }

        public function setRevokeTimeField(string $value): static
        {
            $this->setFeildName('revoke_time', $value);

            return $this;
        }

        public function getRevokeTimeField(): string
        {
            return $this->getFieldName('revoke_time');
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