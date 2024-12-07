<?php

    declare(strict_types = 1);

    namespace Coco\tgDownloader;

    use Coco\commandBuilder\command\Curl;
    use Coco\commandRunner\DaemonLauncher;
    use Coco\commandRunner\Launcher;
    use Coco\queue\MissionManager;
    use Coco\scanner\abstract\MakerAbastact;
    use Coco\scanner\LoopScanner;
    use Coco\scanner\LoopTool;
    use Coco\scanner\maker\CallbackMaker;
    use Coco\scanner\maker\FilesystemMaker;
    use Coco\scanner\processor\CallbackProcessor;
    use Coco\tableManager\TableRegistry;
    use Coco\tgDownloader\tables\Account;
    use Coco\tgDownloader\tables\Pages;
    use DI\Container;
    use GuzzleHttp\Client;
    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
    use Symfony\Component\Cache\Marshaller\DeflateMarshaller;
    use Symfony\Component\Finder\Finder;

    use Coco\tgDownloader\tables\Message;
    use Coco\tgDownloader\tables\Type;
    use Coco\tgDownloader\tables\File;
    use Coco\tgDownloader\tables\Post;

    class Manager
    {
        protected MissionManager $manager;
        protected ?Container     $container             = null;
        protected array          $tables                = [];
        protected array          $typeMap               = [];
        protected ?string        $proxy                 = null;
        protected ?string        $tempJsonPath          = null;
        protected ?string        $mediaStorePath        = null;
        protected ?string        $telegramBotApiPath    = null;
        protected bool           $debug                 = false;
        protected int            $maxDownloading        = 10;
        protected int            $maxDownloadTimeout    = 360000;
        protected int            $downloadDelayInSecond = 1;
        protected ?string        $webHookUrl            = null;
        protected ?string        $mediaOwner            = 'www';

        protected ?string $messageTableName = null;
        protected ?string $postTableName    = null;
        protected ?string $fileTableName    = null;
        protected ?string $typeTableName    = null;
        protected ?string $accountTableName = null;
        protected ?string $pagesTableName   = null;

        const DOWNLOAD_LOCK_KEY      = 'download_lock_key';
        const SCANNER_DOWNLOAD_MEDIA = 'download_media';
        const SCANNER_FILE_MOVE      = 'file_move';
        const SCANNER_MIGRATION      = 'migration';

        const PAGE_INDEX  = 1;
        const PAGE_TYPE   = 2;
        const PAGE_DETAIL = 3;

        public function __construct(protected string $bootToken, protected int $localServerPort = 8081, protected int $statisticsPort = 8082, protected string $basePath = './data', ?Container $container = null)
        {
            $this->envCheck();

            if (!is_null($container))
            {
                $this->container = $container;
            }
            else
            {
                $this->container = new Container();
            }

            $this->manager = new MissionManager($this->container);
            $this->manager->setStandardLogger('te-page-manager');

            $this->basePath = rtrim($this->basePath, '/');
            is_dir($this->basePath) or mkdir($this->basePath, 0777, true);
            $this->basePath = realpath($this->basePath) . '/';

            $this->tempJsonPath       = $this->basePath . 'json';
            $this->mediaStorePath     = $this->basePath . 'media';
            $this->telegramBotApiPath = $this->basePath . 'telegramBotApi';
        }

        /*
         *
         * ---------------------------------------------------------
         *
         * */

        public function isDebug(): bool
        {
            return $this->debug;
        }

        public function setDebug(bool $debug): void
        {
            $this->debug = $debug;
        }

        public function getContainer(): Container
        {
            return $this->container;
        }

        public function getTempJsonPath(): ?string
        {
            return $this->tempJsonPath;
        }

        public function setMediaStorePath(?string $mediaStorePath): static
        {
            $this->mediaStorePath = $mediaStorePath;

            return $this;
        }

        public function getMediaStorePath(): ?string
        {
            return $this->mediaStorePath;
        }

        public function getBootToken(): string
        {
            return $this->bootToken;
        }

        public function getBootId(): int
        {
            [
                $id,
                $_,
            ] = explode(':', $this->bootToken);

            return (int)$id;
        }

        public function setMaxDownloading(int $maxDownloading): static
        {
            $this->maxDownloading = $maxDownloading;

            return $this;
        }

        public function setDownloadDelayInSecond(int $downloadDelayInSecond): static
        {
            $this->downloadDelayInSecond = $downloadDelayInSecond;

            return $this;
        }

        public function setMaxDownloadTimeout(int $maxDownloadTimeout): static
        {
            $this->maxDownloadTimeout = $maxDownloadTimeout;

            return $this;
        }

        public function setWebHookUrl(?string $webHookUrl): static
        {
            $this->webHookUrl = $webHookUrl;

            return $this;
        }

        public function setMediaOwner(?string $mediaOwner): static
        {
            $this->mediaOwner = $mediaOwner;

            return $this;
        }

        public function setTypeMap(array $typeMap): static
        {
            $this->typeMap = $typeMap;

            return $this;
        }

        public function getTypeIdBySender($sender): int
        {
            if (isset($this->typeMap[$sender]))
            {
                return $this->typeMap[$sender];
            }

            return -1;
        }

        /*
         * ---------------------------------------------------------
         * */

        // 使用 curl 下载器
        protected function useCurlDownloader(): static
        {
            $callback = function($data, CallbackMaker $maker_) {

                foreach ($data as $k => $item)
                {
                    $file_id    = $item['file_id'];
                    $apiUrl     = $this->resolveEndponit('getFile', [
                        "file_id" => $file_id,
                    ]);
                    $outputPath = $this->tempJsonPath . DIRECTORY_SEPARATOR . $item['id'] . '.json';

                    $command = Curl::getIns();
                    $command->silent();
                    $command->outputToFile(escapeshellarg($outputPath));
                    $command->setMaxTime($this->maxDownloadTimeout);
                    $command->url(escapeshellarg($apiUrl));

                    // 创建 curl 命令
                    $command = sprintf('curl -s -o %s --max-time %d %s', escapeshellarg($outputPath), $this->maxDownloadTimeout, escapeshellarg($apiUrl));

                    $maker_->getScanner()->logInfo('exec:' . $command);

                    $launcher = new Launcher($command);

                    $launcher->launch();
                }
            };

            $this->initDownloadMediaMaker($callback);

            return $this;
        }

        /*
         * ---------------------------------------------------------
         * */

        public function initTheDownloadMediaScanner(): static
        {
            is_dir($this->tempJsonPath) or mkdir($this->tempJsonPath, 0777, true);
            if (!is_dir($this->tempJsonPath))
            {
                throw new \Exception('文件夹不存在：' . $this->tempJsonPath);
            }

            $this->useCurlDownloader();
            $this->initDownloadMediaScanner();

            return $this;
        }

        public function initTheFileMoveScanner(): static
        {
            is_dir($this->mediaStorePath) or mkdir($this->mediaStorePath, 0777, true);
            if (!is_dir($this->mediaStorePath))
            {
                throw new \Exception('文件夹不存在：' . $this->mediaStorePath);
            }

            $this->initToFileMoveMaker($this->tempJsonPath);
            $this->initToFileMoveScanner();

            return $this;
        }

        public function initTheMigrationScanner(): static
        {
            $this->initMigrationMaker();
            $this->initMigrationScanner();

            return $this;
        }

        /*
         * ---------------------------------------------------------
         * */

        /**
         * 扫描库中的 updates, 获取 getFileStatusField 为 0 的记录，然后推入队列，再把 getFileStatusField 改为 1
         * 下载一次就把 getDownloadTimesField 加 1,开始下载时更新 download_time 时间
         * getFileIdField 可能会有重复的，即多个 updates 指向同一个文件
         *
         * 0:未下载文件，1:下载中，2:下载完成移动到指定位置中，3:移动完成
         *
         * @return $this
         */
        protected function initDownloadMediaMaker(callable $processorCallback): static
        {
            $this->container->set('downloadMediaMaker', function(Container $container) use ($processorCallback) {

                /*-------------------------------------------*/
                $maker = new CallbackMaker(function() {

                    if ($this->isTelegramBotApiStarted())
                    {
                        $this->manager->logInfo('API服务正常');
                    }
                    else
                    {
                        $this->manager->logInfo('【------- API服务熄火 -------】');

                        return [];
                    }

                    while ($this->getRedisClient()->get(static::DOWNLOAD_LOCK_KEY))
                    {
                        $this->manager->logInfo('锁定中，等待...');
                        usleep(1000 * 250);
                    }

                    if ($this->maxDownloading < 1)
                    {
                        $this->maxDownloading = 1;
                    }

                    $msgTable = $this->getMessageTable();
                    //没有文件的信息直接设置状态为2，不用处理文件
                    //getFileIdField 为空，并且getFileStatusField为0的
                    $data = [
                        $msgTable->getFileStatusField() => 2,
                    ];

                    $msgTable->tableIns()->where($msgTable->getFileIdField(), '=', '')
                        ->where($msgTable->getFileStatusField(), 'in', [0])->update($data);

                    /**
                     * ---------------------------------------
                     * 先获取正在下载的任务个数,限制最多只能同时下载多少个任务
                     * ---------------------------------------
                     */
                    $downloading = $this->getDownloadingCount();

                    /**
                     * ---------------------------------------
                     * 剩余待下载任务数
                     * ---------------------------------------
                     */
                    $downloadingRemain = $this->getDownloadingRemainCount();

                    $this->manager->logInfo('正在下载任务数：' . $downloading . '，剩余：' . $downloadingRemain);

                    //如果正在下载的任务大于等于最大限制
                    if ($downloading >= $this->maxDownloading)
                    {
                        $this->manager->logInfo('正在下载任务数大于等于设定最大值，暂停下载');

                        return [];
                    }

                    /**
                     * ---------------------------------------
                     * 获取需要下载文件的 file_id
                     * ---------------------------------------
                     */

                    /*
                     * getFileStatusField 为 0,并且 path 为空，每次获取 maxDownloading 个
                     *
                     * --------------*/
                    $data1 = $msgTable->tableIns()->field(implode(',', [
                        $msgTable->getPkField(),
                        $msgTable->getFileIdField(),
                        $msgTable->getDownloadTimeField(),
                        $msgTable->getFileSizeField(),
                    ]))->where($msgTable->getFileStatusField(), 'in', [0])
                        ->limit(0, $this->maxDownloading - $downloading)->order($msgTable->getPkField())->select();
                    $data1 = $data1->toArray();

                    /*
                     * 获取所有下载时间超时 maxDownloadTimeout,并且状态还是下载中的记录, getFileStatusField 为 1
                     *
                     * --------------*/

                    $t = $msgTable->tableIns()->field(implode(',', [
                        $msgTable->getPkField(),
                        $msgTable->getFileIdField(),
                        $msgTable->getDownloadTimeField(),
                        $msgTable->getFileSizeField(),
                    ]))->where($msgTable->getDownloadTimeField(), '>', 0)
                        ->where($msgTable->getDownloadTimeField(), '<', time() - $this->maxDownloadTimeout)
                        ->where($msgTable->getFileStatusField(), 'in', [1])
                        ->limit(0, $this->maxDownloading - $downloading)->order($msgTable->getPkField())
//                         ->fetchSql(true)
                        ->select();

                    $data2 = $t->toArray();

                    /**
                     * ---------------------------------------
                     * 最终需要下载的文件
                     * ---------------------------------------
                     */

                    $data = array_merge($data1, $data2);
                    $ids  = [];

                    foreach ($data as $k => $v)
                    {
                        $ids[] = $v[$msgTable->getPkField()];
                    }

                    //更新下载状态和开始下载时间
                    $timeNow = time();
                    $msgTable->tableIns()->where($msgTable->getPkField(), 'in', $ids)->update([
                        $msgTable->getFileStatusField()   => 1,
                        $msgTable->getDownloadTimeField() => $timeNow,
                    ]);

                    //更新下载次数
                    $msgTable->tableIns()->where($msgTable->getPkField(), 'in', $ids)
                        ->inc($msgTable->getDownloadTimesField(), 1);

                    return $data;
                });

                /*-------------------------------------------*/
                $maker->init(function(CallbackMaker $maker_) {

                });

                /*-------------------------------------------*/
                $maker->addProcessor(new CallbackProcessor($processorCallback));

                return $maker;
            });

            return $this;
        }

        protected function getDownloadMediaMaker()
        {
            return $this->container->get('downloadMediaMaker');
        }

        public function initDownloadMediaScanner(): static
        {
            $this->container->set('downloadMediaScanner', function(Container $container) {
                $scanner = new  LoopScanner();
                $scanner->setDelayMs(3000);
                $scanner->setStandardLogger('te-page-media-download');
                $scanner->setName(static::SCANNER_DOWNLOAD_MEDIA);

                return $scanner;
            });

            return $this;
        }

        protected function getDownloadMediaScanner(): LoopScanner
        {
            return $this->container->get('downloadMediaScanner');
        }

        public function scanAndDownload(): void
        {
            $this->getDownloadMediaScanner()->setMaker($this->getDownloadMediaMaker())->listen();
        }

        public function getDownloadingCount(): int
        {
            $msgTable = $this->getMessageTable();

            return $msgTable->tableIns()->where($msgTable->getFileStatusField(), 'in', [1])->count();
        }

        public function getDownloadingRemainCount(): int
        {
            $msgTable = $this->getMessageTable();

            return $msgTable->tableIns()->where($msgTable->getFileStatusField(), 'in', [0])->count();
        }

        public function stopDownloadMedia(): void
        {
            LoopTool::getIns()->stop(static::SCANNER_DOWNLOAD_MEDIA);
        }

        /*
         * ---------------------------------------------------------
         * */

        /**
         * 扫描curl请求完成的结果json，读取文件中的内容，将文件移动到指定文件夹，更新path，file_status
         * file_id 可能重复
         *
         * @param string $destPath
         *
         * @return $this
         */
        protected function initToFileMoveMaker(string $destPath): static
        {
            $this->container->set('toFileMoveMaker', function(Container $container) use ($destPath) {

                $maker = new FilesystemMaker($destPath);
                $maker->init(function(string $path, Finder $finder) {

                    is_dir($path) or mkdir($path, 777, true);

                    $finder->depth('< 1')->in($path)->files();
                });

                $maker->addProcessor(new CallbackProcessor(function(Finder $finder, FilesystemMaker $maker_) {
                    $msgTable = $this->getMessageTable();

                    foreach ($finder as $k => $pathName)
                    {
                        $fullSourcePath = $pathName->getRealPath();

                        $id = pathinfo($fullSourcePath, PATHINFO_FILENAME);

                        $json = file_get_contents($fullSourcePath);

                        $jsonInfo = json_decode($json, true);

                        //有时候json文件是空的,删除json文件，更新状态为0，这个id重新下载
                        if (!$jsonInfo)
                        {
                            $this->getToFileMoveScanner()->logInfo('文件为空，删除：' . $fullSourcePath);
                            $this->getToFileMoveScanner()->logInfo('暂停' . $this->downloadDelayInSecond . '秒');

                            $this->getRedisClient()->setex(static::DOWNLOAD_LOCK_KEY, $this->downloadDelayInSecond, 1);

                            unlink($fullSourcePath);

                            $msgTable->tableIns()->where($msgTable->getPkField(), '=', $id)->update([
                                $msgTable->getFileStatusField() => 0,
                            ]);

                            return;
                        }

                        if ($jsonInfo['ok'] !== true)
                        {
                            $this->getToFileMoveScanner()->logInfo('json 出错：' . $json);

                            unlink($fullSourcePath);

                            $msgTable->tableIns()->where($msgTable->getPkField(), '=', $id)->update([
                                $msgTable->getFileStatusField() => 0,
                            ]);

                            continue;
                        }

                        // /www/wwwroot/tg-bot-server/data/6026303590:AAGvMcaxTRBbcPxs_ShGu-G4CffyCyI_6Ek/videos/file_8
                        $source = $jsonInfo['result']['file_path'];
                        $t      = explode('/', $source);

                        //videos
                        $fileType = $t[count($t) - 2];

                        $updateInfo = $msgTable->tableIns()->where($msgTable->getPkField(), $id)->find();
//                        $updateInfo = $msgTable->tableIns()->where($msgTable->getFileIdField(), $jsonInfo['result']['file_id'])->find();

                        $targetPath = static::makePath($jsonInfo['result']['file_id'], $fileType) . '.' . $updateInfo[$msgTable->getExtField()];

                        // /var/www/6025/new/coco-tgDownloader/examples/data/mediaStore/2024-10/photos/A/AQADdrcxGxbnIFR-.jpg
                        $target = rtrim($this->mediaStorePath) . '/' . ltrim($targetPath);

                        is_dir(dirname($target)) or mkdir(dirname($target), 0777, true);

                        $this->getToFileMoveScanner()->logInfo('移动：' . $source . '->' . $target);

                        //下载的媒体文件不存在
                        if (!is_file($source))
                        {
                            //如果文件不存在，查看是否有相同 file_id 文件在数据库中，有直接指向
                            //由于file_id 可能重复，可能文件之前被移走，被更新到数据库中
                            //这种情况直接把数据库中同file_id 的path 更新过来就行

                            //查找同 file_id 记录的有没有已经存在的path
                            $path = $msgTable->tableIns()
                                ->where($msgTable->getFileIdField(), '=', $jsonInfo['result']['file_id'])
                                ->where($msgTable->getPathField(), '<>', '')->value($msgTable->getPathField());

                            if ($path)
                            {
                                $this->getToFileMoveScanner()->logInfo('源文件重复：' . $source);

                                //如果有的话，把所有同 file_id 的 path 都更新
                                $data = [
                                    $msgTable->getFileStatusField() => 2,
                                    $msgTable->getPathField()       => $path,
                                ];

                                $res = $msgTable->tableIns()
                                    ->where($msgTable->getFileIdField(), '=', $jsonInfo['result']['file_id'])
                                    ->where($msgTable->getPathField(), '=', '')->update($data);

                                $this->getToFileMoveScanner()->logInfo('更新重复文件 path：' . $res);
                                $this->getToFileMoveScanner()->logInfo('删除：' . $fullSourcePath);
                            }

                            unlink($fullSourcePath);
                        }
                        else
                        {
                            if (rename($source, $target))
                            {
                                chmod($target, 0777);
                                chown($target, $this->mediaOwner);

                                //移动成功
                                $this->getToFileMoveScanner()
                                    ->logInfo('更新：' . $updateInfo[$msgTable->getPkField()] . '->' . $target);

                                $data = [
                                    $msgTable->getPathField()       => $targetPath,
                                    $msgTable->getFileStatusField() => 2,
                                ];

                                $res = $msgTable->tableIns()
                                    ->where($msgTable->getPkField(), $updateInfo[$msgTable->getPkField()])
                                    ->update($data);

                                if ($res)
                                {
                                    $this->getToFileMoveScanner()->logInfo('删除：' . $fullSourcePath);

                                    unlink($fullSourcePath);
                                }
                                else
                                {
                                    $this->getToFileMoveScanner()
                                        ->logError('更新失败：' . $updateInfo[$msgTable->getPkField()] . '->' . $target);
                                }
                            }
                            else
                            {
                                $this->getToFileMoveScanner()->logError('文件 rename 失败：' . $source . '->' . $target);

                                unlink($fullSourcePath);

                                $msgTable->tableIns()->where($msgTable->getPkField(), '=', $id)->update([
                                    $msgTable->getFileStatusField() => 0,
                                ]);

                            }
                        }
                    }

                }));

                return $maker;
            });

            return $this;
        }

        protected function getToFileMoveMaker(): MakerAbastact
        {
            return $this->container->get('toFileMoveMaker');
        }

        protected function initToFileMoveScanner(): static
        {
            $this->container->set('toFileMove', function(Container $container) {

                $scanner = new  LoopScanner();
                $scanner->setDelayMs(3000);
                $scanner->setStandardLogger('te-page-fileMove');
                $scanner->setName(static::SCANNER_FILE_MOVE);

                return $scanner;
            });

            return $this;
        }

        protected function getToFileMoveScanner(): LoopScanner
        {
            return $this->container->get('toFileMove');
        }

        public function scanAndMoveFile(): void
        {
            $this->getToFileMoveScanner()->setMaker($this->getToFileMoveMaker())->listen();
        }

        public function stopFileMove(): void
        {
            LoopTool::getIns()->stop(static::SCANNER_FILE_MOVE);
        }
        /*
         * ---------------------------------------------------------
         * */

        /**
         * 扫描状态为 2 的记录，根据media_group_id分组，media的个数等于redis里保存的个数说明文件都处理完了，
         * 先把文件 path 写入 file 表
         * 再把 caption 写入post表
         *
         * @return $this
         */
        public function initMigrationMaker(): static
        {
            $this->container->set('migrationMaker', function(Container $container) {
                $msgTable  = $this->getMessageTable();
                $postTable = $this->getPostTable();
                $typeTable = $this->getTypeTable();
                $fileTable = $this->getFileTable();

                /*-------------------------------------------*/
                $maker = new CallbackMaker(function() use (
                    $msgTable, $postTable, $typeTable, $fileTable
                ) {

                    /**
                     * ---------------------------------------
                     * 获取message
                     * ---------------------------------------
                     */

                    /*
                     * getFileStatusField 为 2
                     *
                     * --------------*/

                    $data = $msgTable->tableIns()
                        ->where($msgTable->getFileStatusField(), 'in', [2])
                        ->limit(0, 100)->order($msgTable->getPkField())->select();
                    $data = $data->toArray();

                    $group  = [];
                    $result = [];

                    //根据media_group_id 分组
                    foreach ($data as $k => $v)
                    {
                        if (!isset($group[$v[$msgTable->getMediaGroupIdField()]]))
                        {
                            $group[$v[$msgTable->getMediaGroupIdField()]] = [];
                        }
                        $group[$v[$msgTable->getMediaGroupIdField()]][] = $v;
                    }

                    foreach ($group as $group_id => $item)
                    {
                        //一共应该有几个媒体
                        $totalMediaCount = $this->getMediaGroupCount($group_id);

                        //当前查出了几个有媒体
                        $currentHasMediaCount = 0;
                        foreach ($item as $k => $v)
                        {
                            if ($v[$msgTable->getFileUniqueIdField()])
                            {
                                $currentHasMediaCount++;
                            }
                        }

                        //如果相等说明一条消息中所有媒体已经下载完
                        if ($totalMediaCount == $currentHasMediaCount)
                        {
                            $result[$group_id] = $item;
                        }
                    }

                    return $result;
                });

                /*-------------------------------------------*/
                $maker->init(function(CallbackMaker $maker_) {

                });

                /*-------------------------------------------*/
                $maker->addProcessor(new CallbackProcessor(function($data, CallbackMaker $maker_) use (
                    $msgTable, $postTable, $typeTable, $fileTable
                ) {

                    foreach ($data as $mediaGroupId => $item)
                    {
                        $ids   = [];
                        $files = [];

                        //计算出文本信息
                        $content = '';
                        foreach ($item as $k => $messageInfo)
                        {
                            if ($messageInfo[$msgTable->getCaptionField()])
                            {
                                $content = $messageInfo[$msgTable->getCaptionField()];
                                break;
                            }

                            if ($messageInfo[$msgTable->getTextField()])
                            {
                                $content = $messageInfo[$msgTable->getTextField()];
                                break;
                            }
                        }

                        $content = trim($content);

                        //没有文件也没有文本，空消息
                        if (!$content && !$messageInfo[$msgTable->getPathField()])
                        {
                            break;
                        }

                        $postId = $postTable->calcPk();

                        //构造文件数组，写入文件表
                        foreach ($item as $k => $messageInfo)
                        {
                            if ($messageInfo[$msgTable->getFileIdField()])
                            {
                                $files[] = [
                                    $fileTable->getPkField()           => $fileTable->calcPk(),
                                    $fileTable->getPostIdField()       => $postId,
                                    $fileTable->getPathField()         => $messageInfo[$msgTable->getPathField()],
                                    $fileTable->getFileSizeField()     => $messageInfo[$msgTable->getFileSizeField()],
                                    $fileTable->getFileNameField()     => $messageInfo[$msgTable->getFileNameField()],
                                    $fileTable->getExtField()          => $messageInfo[$msgTable->getExtField()],
                                    $fileTable->getMimeTypeField()     => $messageInfo[$msgTable->getMimeTypeField()],
                                    $fileTable->getMediaGroupIdField() => $mediaGroupId,
                                    $fileTable->getTimeField()         => time(),
                                ];
                            }

                            $ids[] = $messageInfo[$msgTable->getPkField()];
                        }

                        $content = preg_replace('#[\r\n]+#', ' ', $content);
                        $maker_->getScanner()->logInfo('mediaGroupId:' . "$mediaGroupId: " . $content);
                        $maker_->getScanner()->logInfo(PHP_EOL);

                        if (count($files))
                        {
                            $fileTable->tableIns()->insertAll($files);
                            $maker_->getScanner()->logInfo('写入 file 表:' . $messageInfo[$msgTable->getPathField()]);
                        }

                        //向 post 插入一个记录
                        //有可能当前这个 message 不是消息组中第一个带有 caption 的消息
                        $postTable->tableIns()->insert([
                            $postTable->getPkField()           => $postId,
                            $postTable->getTypeIdField()       => $messageInfo[$msgTable->getTypeIdField()],
                            $postTable->getContentsField()     => $content,
                            $postTable->getMediaGroupIdField() => $mediaGroupId,
                            $postTable->getDateField()         => $messageInfo[$msgTable->getDateField()],
                            $postTable->getTimeField()         => time(),
                        ]);

                        //删除redis记录的条数
                        $this->deleteMediaGroupCount($mediaGroupId);

                        //更新状态为数据已经迁移
                        $msgTable->tableIns()->where($msgTable->getPkField(), 'in', $ids)->update([
                            $msgTable->getFileStatusField() => 3,
                        ]);
                    }
                }));

                return $maker;
            });

            return $this;
        }

        protected function getMigrationMaker(): MakerAbastact
        {
            return $this->container->get('migrationMaker');
        }

        protected function initMigrationScanner(): static
        {
            $this->container->set('migration', function(Container $container) {

                $scanner = new  LoopScanner();
                $scanner->setDelayMs(3000);
                $scanner->setStandardLogger('te-page-migration');
                $scanner->setName(static::SCANNER_MIGRATION);

                return $scanner;
            });

            return $this;
        }

        protected function getMigrationScanner(): LoopScanner
        {
            return $this->container->get('migration');
        }

        public function scanAndMirgrateMediaToDb(): void
        {
            $this->getMigrationScanner()->setMaker($this->getMigrationMaker())->listen();
        }

        public function stopMigration(): void
        {
            LoopTool::getIns()->stop(static::SCANNER_MIGRATION);
        }

        /*
         * ---------------------------------------------------------
         * */
        public function initTelegramBotApi($apiId, $apiHash): static
        {
            is_dir($this->telegramBotApiPath) or mkdir($this->telegramBotApiPath, 0777, true);
            if (!is_dir($this->telegramBotApiPath))
            {
                throw new \Exception('文件夹不存在：' . $this->telegramBotApiPath);
            }

            $this->container->set('telegramBotApi', function(Container $container) use ($apiId, $apiHash) {

                $binPath = dirname(__DIR__) . '/tg-bot-server/bin/telegram-bot-api';

                $telegramApiCommand = TelegramBotAPI::getIns($binPath);

                $telegramApiCommand->setApiId((string)$apiId);
                $telegramApiCommand->setApiHash($apiHash);
                $telegramApiCommand->allowLocalRequests();
                $telegramApiCommand->setHttpPort($this->localServerPort);
                $telegramApiCommand->setHttpStatPort($this->statisticsPort);
                $telegramApiCommand->setWorkingDirectory($this->telegramBotApiPath);
                $telegramApiCommand->setTempDirectory('temp');
                $telegramApiCommand->setLogFilePath('log.log');
                $telegramApiCommand->setLogVerbosity(1);

                $launcher = new DaemonLauncher((string)$telegramApiCommand);
                $launcher->setStandardLogger('te-page-launcher');

                return $launcher;
            });

            return $this;
        }

        public function getTelegramBotApi(): Launcher
        {
            return $this->container->get('telegramBotApi');
        }

        public function startTelegramBotApi(): void
        {
            $this->getTelegramBotApi()->launch();
        }

        public function restartTelegramBotApi(): void
        {
            if ($this->isTelegramBotApiStarted())
            {
                $this->stopTelegramBotApi();
            }

            $this->startTelegramBotApi();
        }

        public function stopTelegramBotApi(): void
        {
            $this->getTelegramBotApi()->killByKeyword('telegram-bot-api');
        }

        public function isTelegramBotApiStarted(): bool
        {
            $processes = $this->getTelegramBotApi()->getProcessListByKeyword('telegram-bot-api');

            return count($processes) > 0;
        }

        public function getTelegramApiInfo(): array
        {
            $apiUrl   = 'http://127.0.0.1:' . $this->statisticsPort;
            $contents = $this->getTelegramApiGuzzle()->get($apiUrl);
            $body     = $contents->getBody()->getContents();

            $result = [];

            $data        = preg_split('#[\r\n]{2}#', $body);
            $performance = array_shift($data);
            $bots        = $data;

            $performanceLines = static::parseLine($performance);

            foreach ($performanceLines as $k => $v)
            {
                $t = static::parseField($v);
                if (count($t['value']) == 1)
                {
                    $result['performance'][$t['key']] = $t['value'][0];
                }
                else
                {
                    $result['performance'][$t['key']] = $t['value'];
                }
            }

            foreach ($bots as $k => $v)
            {
                $botLines = static::parseLine($v);

                $botInfo = [];
                foreach ($botLines as $k1 => $v1)
                {
                    $t = static::parseField($v1);
                    if (count($t['value']) == 1)
                    {
                        $botInfo[$t['key']] = $t['value'][0];
                    }
                    else
                    {
                        $botInfo[$t['key']] = $t['value'];
                    }

                }

                $result['bots'][$botInfo['id']] = $botInfo;
            }

            return $result;
        }

        /*
         * ---------------------------------------------------------
         * */

        public function initMysql($db, $host = '127.0.0.1', $username = 'root', $password = 'root', $port = 3306): static
        {
            $this->container->set('mysqlClient', function(Container $container) use ($host, $port, $username, $password, $db) {

                $registry = TableRegistry::initMysqlClient($db, $host, $username, $password, $port);
                $registry->setStandardLogger('te-page-mysql');

                return $registry;
            });

            return $this;
        }

        public function getMysqlClient(): TableRegistry
        {
            return $this->container->get('mysqlClient');
        }

        /*
         * ---------------------------------------------------------
         * */

        public function initRedis($host = '127.0.0.1', $password = '', $port = 6379, $db = 9): static
        {
            $this->container->set('redisClient', function(Container $container) {
                return (new \Redis());
            });

            $this->manager->initRedisClient(function(MissionManager $missionManager) use ($host, $password, $port, $db) {
                /**
                 * @var \Redis $redis
                 */
                $redis = $missionManager->getContainer()->get('redisClient');
                $redis->connect($host, $port);
                $password && $redis->auth($password);
                $redis->select($db);

                return $redis;
            });

            $this->initCache();

            return $this;
        }

        protected function getRedisClient(): \Redis
        {
            return $this->container->get('redisClient');
        }

        /*
         * ---------------------------------------------------------
         * */

        protected function getTelegramApiGuzzle(): Client
        {
            return $this->container->get('telegramApiGuzzle');
        }

        public function initTelegramApiGuzzle(): static
        {
            $this->container->set('telegramApiGuzzle', function(Container $container) {
                return new Client([
                    'timeout' => 50,
                    'debug'   => $this->debug,
                ]);
            });

            return $this;
        }

        /*
         *
         * ---------------------------------------------------------
         *
         * */

        /**********************/
        public function initMessageTable(string $name, callable $callback): static
        {
            $this->messageTableName = $name;

            $this->getMysqlClient()->initTable($name, Message::class, $callback);

            return $this;
        }

        public function getMessageTable(): Message
        {
            return $this->getMysqlClient()->getTable($this->messageTableName);
        }

        /**********************/
        public function initTypeTable(string $name, callable $callback): static
        {
            $this->typeTableName = $name;

            $this->getMysqlClient()->initTable($name, Type::class, $callback);

            return $this;
        }

        public function getTypeTable(): Type
        {
            return $this->getMysqlClient()->getTable($this->typeTableName);
        }

        /**********************/
        public function initFileTable(string $name, callable $callback): static
        {
            $this->fileTableName = $name;

            $this->getMysqlClient()->initTable($name, File::class, $callback);

            return $this;
        }

        public function getFileTable(): File
        {
            return $this->getMysqlClient()->getTable($this->fileTableName);
        }


        /**********************/
        public function initPostTable(string $name, callable $callback): static
        {
            $this->postTableName = $name;

            $this->getMysqlClient()->initTable($name, Post::class, $callback);

            return $this;
        }

        public function getPostTable(): Post
        {
            return $this->getMysqlClient()->getTable($this->postTableName);
        }

        /**********************/
        public function initAccountTable(string $name, callable $callback): static
        {
            $this->accountTableName = $name;

            $this->getMysqlClient()->initTable($name, Account::class, $callback);

            return $this;
        }

        public function getAccountTable(): Account
        {
            return $this->getMysqlClient()->getTable($this->accountTableName);
        }

        /**********************/
        public function initPagesTable(string $name, callable $callback): static
        {
            $this->pagesTableName = $name;

            $this->getMysqlClient()->initTable($name, Pages::class, $callback);

            return $this;
        }

        public function getPagesTable(): Pages
        {
            return $this->getMysqlClient()->getTable($this->pagesTableName);
        }

        /**********************/

        public function getAllTableStatus(): array
        {
            $data = [];

            $a                                         = $this->getMessageTable()->isTableCerated();
            $data[$this->getMessageTable()->getName()] = [
                'is_created' => (int)$a,
                'count'      => $a ? (int)$this->getMessageTable()->getCount() : 0,
            ];

            $b                                      = $this->getTypeTable()->isTableCerated();
            $data[$this->getTypeTable()->getName()] = [
                'is_created' => (int)$b,
                'count'      => $b ? (int)$this->getTypeTable()->getCount() : 0,
            ];

            $c                                      = $this->getFileTable()->isTableCerated();
            $data[$this->getFileTable()->getName()] = [
                'is_created' => (int)$c,
                'count'      => $c ? (int)$this->getFileTable()->getCount() : 0,
            ];

            $d                                      = $this->getPostTable()->isTableCerated();
            $data[$this->getPostTable()->getName()] = [
                'is_created' => (int)$d,
                'count'      => $d ? (int)$this->getPostTable()->getCount() : 0,
            ];

            $e                                         = $this->getAccountTable()->isTableCerated();
            $data[$this->getAccountTable()->getName()] = [
                'is_created' => (int)$e,
                'count'      => $e ? (int)$this->getAccountTable()->getCount() : 0,
            ];

            $f                                       = $this->getPagesTable()->isTableCerated();
            $data[$this->getPagesTable()->getName()] = [
                'is_created' => (int)$f,
                'count'      => $f ? (int)$this->getPagesTable()->getCount() : 0,
            ];

            return $data;
        }
        /**********************/

        /*
         * ---------------------------------------------------------
         * */

        public function getCacheManager(): RedisAdapter
        {
            return $this->container->get('cacheManager');
        }

        protected function initCache(): static
        {
            $this->container->set('cacheManager', function(Container $container) {
                $marshaller   = new DeflateMarshaller(new DefaultMarshaller());
                $cacheManager = new RedisAdapter($container->get('redisClient'), 'tg_download_', 0, $marshaller);

                return $cacheManager;
            });

            return $this;
        }

        /*
         *
         * ---------------------------------------------------------
         *
         * */

        public function enableEchoHandler(): static
        {
            $this->manager->addStdoutHandler(callback: $this->manager::getStandardFormatter());

            $this->getMysqlClient()->addStdoutHandler(callback: $this->manager::getStandardFormatter());

            $this->getDownloadMediaScanner()->addStdoutHandler(callback: $this->manager::getStandardFormatter());

            $this->getToFileMoveScanner()->addStdoutHandler(callback: $this->manager::getStandardFormatter());

            $this->getMigrationScanner()->addStdoutHandler(callback: $this->manager::getStandardFormatter());

            $this->getTelegramBotApi()->addStdoutHandler(callback: $this->manager::getStandardFormatter());

            return $this;
        }

        public function enableRedisHandler(string $redisHost = '127.0.0.1', int $redisPort = 6379, string $password = '', int $db = 10, string $logName = 'redis_log'): static
        {
            $this->manager->addRedisHandler(redisHost: $redisHost, redisPort: $redisPort, password: $password, db: $db, logName: $logName . '-manager', callback: $this->manager::getStandardFormatter());

            $this->getMysqlClient()
                ->addRedisHandler(redisHost: $redisHost, redisPort: $redisPort, password: $password, db: $db, logName: $logName . '-MysqlClient', callback: $this->manager::getStandardFormatter());

            $this->getDownloadMediaScanner()
                ->addRedisHandler(redisHost: $redisHost, redisPort: $redisPort, password: $password, db: $db, logName: $logName . '-DownloadMediaScanner', callback: $this->manager::getStandardFormatter());

            $this->getToFileMoveScanner()
                ->addRedisHandler(redisHost: $redisHost, redisPort: $redisPort, password: $password, db: $db, logName: $logName . '-ToFileMoveScanner', callback: $this->manager::getStandardFormatter());

            $this->getMigrationScanner()
                ->addRedisHandler(redisHost: $redisHost, redisPort: $redisPort, password: $password, db: $db, logName: $logName . '-MigrationScanner', callback: $this->manager::getStandardFormatter());

            $this->getTelegramBotApi()
                ->addRedisHandler(redisHost: $redisHost, redisPort: $redisPort, password: $password, db: $db, logName: $logName . '-TelegramBotApi', callback: $this->manager::getStandardFormatter());

            return $this;
        }

        /*
         * ---------------------------------------------------------
         * */

        public function getMe()
        {
            $guzzle = $this->getTelegramApiGuzzle();

            $apiUrl = $this->resolveEndponit('getMe');

            $config = [];

            $response = $guzzle->get($apiUrl, $config);

            $contents = $response->getBody()->getContents();

            $json = json_decode($contents, true);

            return $json;
        }

        public function isWebHookSeted(): bool
        {
            $info = $this->getTelegramApiInfo();

            $bots = $info['bots'];

            return isset($bots[$this->getBootId()]);
        }

        public function updateWebHook()
        {
            $guzzle = $this->getTelegramApiGuzzle();

            $apiUrl = $this->resolveEndponit('setWebhook', [
                "url" => $this->webHookUrl,
            ]);

            $config = [];

            $response = $guzzle->get($apiUrl, $config);

            $contents = $response->getBody()->getContents();

            $json = json_decode($contents, true);

            return $json;
        }

        public function deleteWebHook()
        {
            $guzzle = $this->getTelegramApiGuzzle();

            $apiUrl = $this->resolveEndponit('deleteWebHook');

            $config = [];

            $response = $guzzle->get($apiUrl, $config);

            $contents = $response->getBody()->getContents();

            $json = json_decode($contents, true);

            return $json;
        }

        public function webHookEndPoint(string $message): void
        {
            $msg = UpdateMessage::parse($message, $this->getBootId());

            $typeId = $this->getTypeIdBySender($msg->senderId);

            if ($msg->isNeededType() && $typeId > 0)
            {
                $msgTable = $this->getMessageTable();

                $data = [
                    $msgTable->getPkField()                 => $msgTable->calcPk(),
                    $msgTable->getBotIdField()              => $msg->bootId,
                    $msgTable->getUpdateIdField()           => $msg->updateId,
                    $msgTable->getSenderIdField()           => $msg->senderId,
                    $msgTable->getMediaGroupIdField()       => $msg->mediaGroupId,
                    $msgTable->getMessageLoadTypeField()    => $msg->messageLoadType,
                    $msgTable->getMessageFromTypeField()    => $msg->messageFromType,
                    $msgTable->getFileIdField()             => $msg->fileId,
                    $msgTable->getFileUniqueIdField()       => $msg->fileUniqueId,
                    $msgTable->getFileSizeField()           => $msg->fileSize,
                    $msgTable->getFileNameField()           => $msg->fileName,
                    $msgTable->getCaptionField()            => $msg->caption,
                    $msgTable->getChatTypeField()           => $msg->chatType,
                    $msgTable->getChatSourceTypeField()     => $msg->chatSourceType,
                    $msgTable->getChatSourceUsernameField() => $msg->chatSourceUsername,
                    $msgTable->getTextField()               => $msg->text,
                    $msgTable->getRawField()                => $msg->message,
                    $msgTable->getDateField()               => $msg->date,
                    $msgTable->getExtField()                => $msg->ext,
                    $msgTable->getMimeTypeField()           => $msg->mimeType,
                    $msgTable->getTypeIdField()             => $typeId,
                    $msgTable->getTimeField()               => time(),
                ];

                $msgTable->tableIns()->insert($data);

                //更新每个信息有几个media图文
                if ($data[$msgTable->getFileUniqueIdField()])
                {
                    $this->incMediaGroupCount($msg->mediaGroupId);
                }
            }
        }

        protected function resolveEndponit($endpoint, array $query = []): ?string
        {
            $queryStr = '';
            if (count($query))
            {
                $queryStr = '?' . http_build_query($query);
            }

            return implode('/', [
                rtrim('http://127.0.0.1:' . $this->localServerPort, '/'),
                'bot' . $this->bootToken,
                trim($endpoint, '/') . $queryStr,
            ]);
        }

        /*
         *
         * ---------------------------------------------------------
         *
         * */
        protected static function makePath(string $input, string $fileType): string
        {
            $year        = date('Y');
            $month       = date('m');
            $day         = date('d');
            $firstLetter = strtoupper(substr(md5($input), 0, 1));

            return "$year-$month/$day/$fileType/$firstLetter/" . hrtime(true);
        }

        protected static function parseField(string $line): array
        {
            $res = explode("\t", $line);

            $key   = array_shift($res);
            $value = $res;

            return [
                "key"   => $key,
                "value" => $value,
            ];
        }

        protected static function parseLine(string $data): array
        {
            return preg_split('#[\r\n]+#', $data, -1, PREG_SPLIT_NO_EMPTY);
        }

        private function envCheck(): void
        {
            // 检查 PHP 版本
            if (version_compare(PHP_VERSION, '8.1.0', '<'))
            {
                throw new \Exception('PHP version must be 8.1 or higher.');
            }

            // 检查 exec 函数是否可用
            if (!function_exists('exec'))
            {
                throw new \Exception('The exec function is disabled.');
            }

            // 检查操作系统
            // 使用 exec 函数检查 /etc/os-release
            $output    = [];
            $returnVar = 0;
            exec('cat /etc/os-release', $output, $returnVar);

            if ($returnVar !== 0 || empty($output))
            {
                throw new \Exception('Unable to read /etc/os-release.');
            }

            $osRelease = implode("\n", $output);
            if (!str_contains($osRelease, 'fedora'))
            {
                throw new \Exception('This application must run on a system compatible with CentOS (like Fedora).');
            }

        }


        /*
         *
         * ---------------------------------------------------------
         *
         * */

        public function isIndexPageCreated(): bool
        {
            $pagesTable = $this->getPagesTable();

            return !!$pagesTable->tableIns()
                ->where($pagesTable->getIdentificationField(), '=', $this->makeIndexPageId())
                ->where($pagesTable->getPageTypeField(), '=', static::PAGE_INDEX)->find();
        }

        public function isTypePageCreated(string|int $typeId, string|int $page): bool
        {
            $pagesTable = $this->getPagesTable();

            return !!$pagesTable->tableIns()
                ->where($pagesTable->getIdentificationField(), '=', $this->makeTypePageId($typeId, $page))
                ->where($pagesTable->getPageTypeField(), '=', static::PAGE_TYPE)->find();
        }

        public function isDetailPageCreated(string|int $pageId): bool
        {
            $pagesTable = $this->getPagesTable();

            return !!$pagesTable->tableIns()
                ->where($pagesTable->getIdentificationField(), '=', $this->makeDetailPageId($pageId))
                ->where($pagesTable->getPageTypeField(), '=', static::PAGE_DETAIL)->find();
        }

        protected function makeIndexPageId(): string
        {
            return '-';
        }

        protected function makeTypePageId(string|int $typeId, string|int $page): string
        {
            return $typeId . '-' . $page;

        }

        protected function makeDetailPageId(string|int $pageId): string
        {
            return (string)$pageId;
        }

        protected function makeMediaGroupCountName($mediaGroupId): string
        {
            return 'media_group_count:' . $mediaGroupId;
        }

        protected function incMediaGroupCount($mediaGroupId): static
        {
            $this->getRedisClient()->incr($this->makeMediaGroupCountName($mediaGroupId));

            return $this;
        }

        protected function getMediaGroupCount($mediaGroupId): int
        {
            return (int)$this->getRedisClient()->get($this->makeMediaGroupCountName($mediaGroupId));
        }

        protected function deleteMediaGroupCount($mediaGroupId): int
        {
            return (int)$this->getRedisClient()->del($this->makeMediaGroupCountName($mediaGroupId));
        }

    }
