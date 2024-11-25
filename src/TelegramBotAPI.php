<?php

    declare(strict_types = 1);

    namespace Coco\tgDownloader;

    use Coco\commandBuilder\abstract\NamedCommand;

    class TelegramBotAPI extends NamedCommand
    {
        protected static function resolveName(): string
        {
            return '';
        }


        // 启用本地请求支持
        public function allowLocalRequests(): static
        {
            $this->addOption('local');

            return $this;
        }

        // 设置 API ID
        public function setApiId(string $apiId): static
        {
            $this->addOption('api-id', $apiId);

            return $this;
        }

        // 设置 API Hash
        public function setApiHash(string $apiHash): static
        {
            $this->addOption('api-hash', $apiHash);

            return $this;
        }

        // 设置 HTTP 监听端口
        public function setHttpPort(int $port): static
        {
            $this->addOption('http-port', $port);

            return $this;
        }

        // 设置 HTTP 统计端口
        public function setHttpStatPort(int $port): static
        {
            $this->addOption('http-stat-port', $port);

            return $this;
        }

        // 设置工作目录
        public function setWorkingDirectory(string $dir): static
        {
            $this->addOption('dir', $dir);

            return $this;
        }

        // 设置临时目录
        public function setTempDirectory(string $dir): static
        {
            $this->addOption('temp-dir', $dir);

            return $this;
        }

        // 设置过滤条件，允许 bot 的 bot_user_id % modulo == remainder
        public function setFilter(string $filter): static
        {
            $this->addOption('filter', $filter);

            return $this;
        }

        // 设置每个 bot 的最大 webhook 连接数
        public function setMaxWebhookConnections(int $maxConnections): static
        {
            $this->addOption('max-webhook-connections', $maxConnections);

            return $this;
        }

        // 设置 HTTP 服务的本地 IP 地址
        public function setHttpIpAddress(string $ip): static
        {
            $this->addOption('http-ip-address', $ip);

            return $this;
        }

        // 设置 HTTP 统计服务的本地 IP 地址
        public function setHttpStatIpAddress(string $ip): static
        {
            $this->addOption('http-stat-ip-address', $ip);

            return $this;
        }

        // 设置日志文档路径
        public function setLogFilePath(string $filePath): static
        {
            $this->addOption('log', $filePath);

            return $this;
        }

        // 设置日志详细度
        public function setLogVerbosity(int $verbosity): static
        {
            $this->addOption('verbosity', $verbosity);

            return $this;
        }

        // 设置内存日志详细度
        public function setMemoryLogVerbosity(int $verbosity): static
        {
            $this->addOption('memory-verbosity', $verbosity);

            return $this;
        }

        // 设置日志文档最大大小，超过该大小后自动轮换
        public function setMaxLogFileSize(int $size): static
        {
            $this->addOption('log-max-file-size', $size);

            return $this;
        }

        // 设置有效的用户名
        public function setUsername(string $username): static
        {
            $this->addOption('username', $username);

            return $this;
        }

        // 设置有效的组名
        public function setGroupname(string $groupname): static
        {
            $this->addOption('groupname', $groupname);

            return $this;
        }

        // 设置最大连接数
        public function setMaxConnections(int $maxConnections): static
        {
            $this->addOption('max-connections', $maxConnections);

            return $this;
        }

        // 设置 CPU 亲和性（作为 64 位掩码）
        public function setCpuAffinity(string $mask): static
        {
            $this->addOption('cpu-affinity', $mask);

            return $this;
        }

        // 设置主线程的 CPU 亲和性（作为 64 位掩码）
        public function setMainThreadAffinity(string $mask): static
        {
            $this->addOption('main-thread-affinity', $mask);

            return $this;
        }

        // 设置 HTTP 请求的代理服务器
        public function setProxy(string $proxy): static
        {
            $this->addOption('proxy', $proxy);

            return $this;
        }

        // 显示帮助信息
        public function showHelp(): static
        {
            $this->addFlag('h');

            return $this;
        }

        // 显示版本号
        public function showVersion(): static
        {
            $this->addFlag('version');

            return $this;
        }
    }
