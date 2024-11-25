<?php

    declare(strict_types = 1);

    namespace Coco\tgDownloader\tables;

    use Coco\tableManager\TableAbstract;

    class Message extends TableAbstract
    {
        public string $comment = '机器人信息表';

        public array $fieldsSqlMap = [
            "bot_id"               => "`__FIELD__NAME__` bigint(11) NOT NULL DEFAULT '0' COMMENT '机器人 telegramid',",
            "update_id"            => "`__FIELD__NAME__` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'update_id',",
            "sender_id"            => "`__FIELD__NAME__` bigint(11) NOT NULL DEFAULT '0' COMMENT 'sender_id',",
            "media_group_id"       => "`__FIELD__NAME__` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'media_group_id',",
            "message_load_type"    => "`__FIELD__NAME__` tinyint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'text=1, video=2, photo=3, audio=4, document=5, animation=6, sticker=7, location=8, contact=9, news=10, poll=11',",
            "message_from_type"    => "`__FIELD__NAME__` tinyint(11) unsigned NOT NULL DEFAULT '0' COMMENT '1:message, 2:edited_message, 3:channel_post, 4:edited_channel_post',",
            "type_id"              => "`__FIELD__NAME__` tinyint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'type_id',",
            "file_id"              => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'file_id',",
            "file_unique_id"       => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'file_unique_id',",
            "file_size"            => "`__FIELD__NAME__` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT 'file_size',",
            "file_name"            => "`__FIELD__NAME__` text COLLATE utf8mb4_unicode_ci COMMENT '文件名',",
            "chat_type"            => "`__FIELD__NAME__` tinyint(4) DEFAULT NULL COMMENT 'private=1, group=2, supergroup=3, channel=4',",
            "mime_type"            => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mime_type',",
            "ext"                  => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '后缀',",
            "chat_source_type"     => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'channel or user',",
            "chat_source_username" => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'username',",
            "path"                 => "`__FIELD__NAME__` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '本地地址',",
            "download_times"       => "`__FIELD__NAME__` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',",
            "download_time"        => "`__FIELD__NAME__` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始下载时间',",
            "file_status"          => "`__FIELD__NAME__` tinyint(11) unsigned NOT NULL DEFAULT '0' COMMENT '0:未下载文件，1:下载中，2:移动完成，3:取出写入到post表',",
            "caption"              => "`__FIELD__NAME__` text COLLATE utf8mb4_unicode_ci COMMENT '标题',",
            "text"                 => "`__FIELD__NAME__` longtext COLLATE utf8mb4_unicode_ci COMMENT 'text 信息',",
            "raw"                  => "`__FIELD__NAME__` longtext COLLATE utf8mb4_unicode_ci COMMENT '原生json',",
            "date"                 => "`__FIELD__NAME__` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '信息发送时间',",
            "time"                 => "`__FIELD__NAME__` int(10) unsigned NOT NULL DEFAULT '0',",
        ];

        protected array $indexSentence = [
            "file_id"              => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "update_id"            => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "sender_id"            => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "media_group_id"       => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "message_load_type"    => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "message_from_type"    => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "chat_type"            => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "chat_source_type"     => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
            "chat_source_username" => "KEY `__INDEX__NAME___index` ( __FIELD__NAME__ ),",
        ];

        public function setTypeIdField(string $value): static
        {
            $this->setFeildName('type_id', $value);

            return $this;
        }

        public function getTypeIdField(): string
        {
            return $this->getFieldName('type_id');
        }

        public function setBotIdField(string $value): static
        {
            $this->setFeildName('bot_id', $value);

            return $this;
        }

        public function getBotIdField(): string
        {
            return $this->getFieldName('bot_id');
        }

        public function setUpdateIdField(string $value): static
        {
            $this->setFeildName('update_id', $value);

            return $this;
        }

        public function getUpdateIdField(): string
        {
            return $this->getFieldName('update_id');
        }

        public function setSenderIdField(string $value): static
        {
            $this->setFeildName('sender_id', $value);

            return $this;
        }

        public function getSenderIdField(): string
        {
            return $this->getFieldName('sender_id');
        }

        public function setMediaGroupIdField(string $value): static
        {
            $this->setFeildName('media_group_id', $value);

            return $this;
        }

        public function getMediaGroupIdField(): string
        {
            return $this->getFieldName('media_group_id');
        }

        public function setMessageLoadTypeField(string $value): static
        {
            $this->setFeildName('message_load_type', $value);

            return $this;
        }

        public function getMessageLoadTypeField(): string
        {
            return $this->getFieldName('message_load_type');
        }

        public function setMessageFromTypeField(string $value): static
        {
            $this->setFeildName('message_from_type', $value);

            return $this;
        }

        public function getMessageFromTypeField(): string
        {
            return $this->getFieldName('message_from_type');
        }

        public function setFileIdField(string $value): static
        {
            $this->setFeildName('file_id', $value);

            return $this;
        }

        public function getFileIdField(): string
        {
            return $this->getFieldName('file_id');
        }

        public function setFileUniqueIdField(string $value): static
        {
            $this->setFeildName('file_unique_id', $value);

            return $this;
        }

        public function getFileUniqueIdField(): string
        {
            return $this->getFieldName('file_unique_id');
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

        public function setCaptionField(string $value): static
        {
            $this->setFeildName('caption', $value);

            return $this;
        }

        public function getCaptionField(): string
        {
            return $this->getFieldName('caption');
        }

        public function setChatTypeField(string $value): static
        {
            $this->setFeildName('chat_type', $value);

            return $this;
        }

        public function getChatTypeField(): string
        {
            return $this->getFieldName('chat_type');
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

        public function setExtField(string $value): static
        {
            $this->setFeildName('ext', $value);

            return $this;
        }

        public function getExtField(): string
        {
            return $this->getFieldName('ext');
        }

        public function setChatSourceTypeField(string $value): static
        {
            $this->setFeildName('chat_source_type', $value);

            return $this;
        }

        public function getChatSourceTypeField(): string
        {
            return $this->getFieldName('chat_source_type');
        }

        public function setChatSourceUsernameField(string $value): static
        {
            $this->setFeildName('chat_source_username', $value);

            return $this;
        }

        public function getChatSourceUsernameField(): string
        {
            return $this->getFieldName('chat_source_username');
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

        public function setDownloadTimesField(string $value): static
        {
            $this->setFeildName('download_times', $value);

            return $this;
        }

        public function getDownloadTimesField(): string
        {
            return $this->getFieldName('download_times');
        }

        public function setDownloadTimeField(string $value): static
        {
            $this->setFeildName('download_time', $value);

            return $this;
        }

        public function getDownloadTimeField(): string
        {
            return $this->getFieldName('download_time');
        }

        public function setFileStatusField(string $value): static
        {
            $this->setFeildName('file_status', $value);

            return $this;
        }

        public function getFileStatusField(): string
        {
            return $this->getFieldName('file_status');
        }

        public function setTextField(string $value): static
        {
            $this->setFeildName('text', $value);

            return $this;
        }

        public function getTextField(): string
        {
            return $this->getFieldName('text');
        }

        public function setRawField(string $value): static
        {
            $this->setFeildName('raw', $value);

            return $this;
        }

        public function getRawField(): string
        {
            return $this->getFieldName('raw');
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
