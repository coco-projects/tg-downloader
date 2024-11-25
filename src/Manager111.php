<?php

    namespace Coco\tePages;

    use Coco\queue\MissionManager;
    use Coco\queue\missionProcessors\GuzzleMissionProcessor;
    use Coco\queue\Queue;
    use Coco\queue\resultProcessor\CustomResultProcessor;
    use Coco\tableManager\TableRegistry;
    use Coco\tePages\missions\TelegraphMission;
    use Coco\tePages\styles\Style1;
    use Coco\tePages\styles\StyleAbstract;
    use Coco\tePages\tables\Account;
    use Coco\tePages\tables\AccountPages;
    use Coco\tePages\tables\MediaSourceCollection;
    use Coco\tePages\tables\MediaSourceItem;
    use Coco\tePages\tables\MediaSourceType;
    use Coco\tePages\tables\WebPages;
    use DI\Container;
    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Marshaller\DeflateMarshaller;
    use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
    use Coco\telegraph\dom\E;

    class Manager
    {
        const BASE_QUEUE                   = 'BASE_QUEUE';
        const CREATE_FIRST_TYPE_PAGE_QUEUE = 'CREATE_FIRST_TYPE_PAGE_QUEUE';
        const CREATE_DETAIL_PAGE_QUEUE     = 'CREATE_DETAIL_PAGE_QUEUE';
        const CREATE_TYPE_ALL_PAGE_QUEUE   = 'CREATE_TYPE_ALL_PAGE_QUEUE';
        const UPDATE_TYPE_ALL_PAGE_QUEUE   = 'UPDATE_TYPE_ALL_PAGE_QUEUE';
        const UPDATE_DETAIL_PAGE_QUEUE     = 'UPDATE_DETAIL_PAGE_QUEUE';

        public Queue $baseQueue;
        public Queue $createFirstTypePageQueue;
        public Queue $createDetailPageQueue;
        public Queue $createTypeAllPageQueue;
        public Queue $updateTypeAllPageQueue;
        public Queue $updateDetailPageQueue;

        protected ?Container     $container    = null;
        protected ?StyleAbstract $style        = null;
        protected MissionManager $manager;
        protected ?string        $proxy        = null;
        protected ?string        $shortName    = 'bob';
        protected ?string        $authorName   = 'tily';
        protected ?string        $authorUrl    = '';
        protected ?string        $brandTitle   = 'telegraph_page';
        protected int            $pageRow      = 10;
        protected int            $timeout      = 30;
        protected int            $maxTimes     = 10;
        protected int            $delay        = 0;
        protected bool           $exitOnfinish = true;
        protected array          $tables       = [];

        public function __construct(?Container $container = null, StyleAbstract $style = null)
        {
            if (!is_null($container))
            {
                $this->container = $container;
            }
            else
            {
                $this->container = new Container();
            }

            if (!is_null($style))
            {
                $this->style = $style;
            }
            else
            {
                $this->style = new Style1();
            }

            $this->style->setManager($this);

            $this->manager = new MissionManager($this->container);

            $this->manager->setStandardLogger('te-page');

        }

        public function monitor(): void
        {
            $this->manager->getAllQueueInfoTable();
        }

        public function setDelay(int $delay): static
        {
            $this->delay = $delay;

            return $this;
        }

        public function setExitOnfinish(bool $exitOnfinish): static
        {
            $this->exitOnfinish = $exitOnfinish;

            return $this;
        }

        public function setTimeout(int $timeout): static
        {
            $this->timeout = $timeout;

            return $this;
        }

        public function setBrandTitle(?string $brandTitle): static
        {
            $this->brandTitle = $brandTitle;

            return $this;
        }

        public function setPageRow(int $pageRow): static
        {
            $this->pageRow = $pageRow;

            return $this;
        }

        public function setMaxTimes(int $maxTimes): static
        {
            $this->maxTimes = $maxTimes;

            return $this;
        }

        public function getContainer(): Container
        {
            return $this->container;
        }

        public function setProxy(?string $proxy): static
        {
            $this->proxy = $proxy;

            return $this;
        }

        public function setAuthorName(?string $authorName): static
        {
            $this->authorName = $authorName;

            return $this;
        }

        public function setAuthorUrl(?string $authorUrl): static
        {
            $this->authorUrl = $authorUrl;

            return $this;
        }

        public function setShortName(?string $shortName): static
        {
            $this->shortName = $shortName;

            return $this;
        }

        public function restoreFailureMission(): void
        {
            $this->createFirstTypePageQueue->restoreErrorMission();
            $this->createDetailPageQueue->restoreErrorMission();
            $this->createTypeAllPageQueue->restoreErrorMission();
            $this->updateTypeAllPageQueue->restoreErrorMission();
            $this->updateDetailPageQueue->restoreErrorMission();

            $this->createFirstTypePageQueue->restoreTimesReachedMission();
            $this->createDetailPageQueue->restoreTimesReachedMission();
            $this->createTypeAllPageQueue->restoreTimesReachedMission();
            $this->updateTypeAllPageQueue->restoreTimesReachedMission();
            $this->updateDetailPageQueue->restoreTimesReachedMission();
        }

        /*
         *
         * ------------------------------------------------------
         *
         * */

        public function initAccountTable(string $name, callable $callback): static
        {
            $this->tables['Account'] = $name;

            $table = new Account($name);

            $this->getMysqlClient()->addTable($table, $callback);

            return $this;
        }

        public function getAccountTable(): Account
        {
            return $this->getMysqlClient()->getTable($this->tables['Account']);
        }


        /*
         *
         * ------------------------------------------------------
         *
         * */

        public function initAccountPagesTable(string $name, callable $callback): static
        {
            $this->tables['AccountPages'] = $name;

            $table = new AccountPages($name);

            $this->getMysqlClient()->addTable($table, $callback);

            return $this;
        }

        public function getAccountPagesTable(): AccountPages
        {
            return $this->getMysqlClient()->getTable($this->tables['AccountPages']);
        }


        /*
         *
         * ------------------------------------------------------
         *
         * */

        public function initMediaSourceCollectionTable(string $name, callable $callback): static
        {
            $this->tables['MediaSourceCollection'] = $name;

            $table = new MediaSourceCollection($name);

            $this->getMysqlClient()->addTable($table, $callback);

            return $this;
        }

        public function getMediaSourceCollectionTable(): MediaSourceCollection
        {
            return $this->getMysqlClient()->getTable($this->tables['MediaSourceCollection']);
        }


        /*
         *
         * ------------------------------------------------------
         *
         * */

        public function initMediaSourceItemTable(string $name, callable $callback): static
        {
            $this->tables['MediaSourceItem'] = $name;

            $table = new MediaSourceItem($name);

            $this->getMysqlClient()->addTable($table, $callback);

            return $this;
        }

        public function getMediaSourceItemTable(): MediaSourceItem
        {
            return $this->getMysqlClient()->getTable($this->tables['MediaSourceItem']);
        }


        /*
         *
         * ------------------------------------------------------
         *
         * */

        public function initMediaSourceTypeTable(string $name, callable $callback): static
        {
            $this->tables['MediaSourceType'] = $name;

            $table = new MediaSourceType($name);

            $this->getMysqlClient()->addTable($table, $callback);

            return $this;
        }

        public function getMediaSourceTypeTable(): MediaSourceType
        {
            return $this->getMysqlClient()->getTable($this->tables['MediaSourceType']);
        }


        /*
         *
         * ------------------------------------------------------
         *
         * */

        public function initWebPagesTable(string $name, callable $callback): static
        {
            $this->tables['WebPages'] = $name;

            $table = new WebPages($name);

            $this->getMysqlClient()->addTable($table, $callback);

            return $this;
        }

        public function getWebPagesTable(): WebPages
        {
            return $this->getMysqlClient()->getTable($this->tables['WebPages']);
        }



        /*
         *
         * ------------------------------------------------------
         *
         * */
        public function createAccount(int $number): void
        {
            $this->baseQueue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, 1);

                if ($result['ok'])
                {
                    $accountTab = $this->getAccountTable();

                    $data = [
                        $accountTab->getShortNameField()   => $result['result']['short_name'],
                        $accountTab->getAuthorUrlField()   => $result['result']['author_url'],
                        $accountTab->getAuthorNameField()  => $result['result']['author_name'],
                        $accountTab->getAuthUrlField()     => $result['result']['auth_url'],
                        $accountTab->getAccessTokenField() => $result['result']['access_token'],
                        $accountTab->getCreateTimeField()  => time(),
                    ];

                    if (!$accountTab->isPkAutoInc())
                    {
                        $data[$accountTab->getPkField()] = $accountTab->calcPk();
                    }

                    $res = $accountTab->tableIns()->insert($data);

                    if ($res)
                    {
                        $this->manager->logInfo('创建成功: ' . $mission->index);
                    }
                    else
                    {
                        $this->manager->logError('写入错误: ' . $mission->index);
                    }

                }
                else
                {
                    $this->manager->logError($mission->index . ' -- ' . $result['error']);
                }
            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->manager->logError($exception->getMessage());
            };

            $this->baseQueue->addResultProcessor(new CustomResultProcessor($success, $catch));
            $this->baseQueue->setMaxTimes($this->maxTimes);

            for ($i = 1; $i <= $number; $i++)
            {
                $mission        = new TelegraphMission();
                $mission->index = $i;

                if (!is_null($this->proxy))
                {
                    $mission->setProxy($this->proxy);
                }

                $mission->createAccount($this->shortName, $this->authorName, $this->authorUrl);

                $this->baseQueue->execMissionDirect($mission);

                sleep(1);
            }
        }

        public function createIndexPage(): void
        {
            $token = $this->getRandToken();

            $this->baseQueue->setMissionProcessor(new GuzzleMissionProcessor());
            $this->baseQueue->setMaxTimes($this->maxTimes);

            $success = function(TelegraphMission $mission) use ($token) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, 1);

                if ($result['ok'])
                {
                    $webPageTab = $this->getWebPagesTable();
                    $data       = [
                        $webPageTab->getPathField()             => $result['result']['path'],
                        $webPageTab->getUrlField()              => $result['result']['url'],
                        $webPageTab->getTitleField()            => $result['result']['title'],
                        $webPageTab->getDescriptionField()      => $result['result']['description'],
                        $webPageTab->getContentField()          => json_encode($result['result']['content'], 256),
                        $webPageTab->getViewsField()            => $result['result']['views'],
                        $webPageTab->getCanEditField()          => (int)$result['result']['can_edit'],
                        $webPageTab->getTokenField()            => $token,
                        $webPageTab->getPageTypeField()         => 1,
                        $webPageTab->getIsFirstTypePageField()  => 0,
                        $webPageTab->getCollectionTypeIdField() => 0,
                        $webPageTab->getCollectionIdField()     => 0,
                        $webPageTab->getPageNumField()          => 0,
                        $webPageTab->getPageNumListField()      => '',
                        $webPageTab->getUpdateTimeField()       => time(),
                        $webPageTab->getCreateTimeField()       => time(),
                        $webPageTab->getParamsField()           => json_encode([], 256),
                    ];

                    if (!$webPageTab->isPkAutoInc())
                    {
                        $data[$webPageTab->getPkField()] = $webPageTab->calcPk();
                    }

                    $re = $webPageTab->tableIns()->insert($data);

                    if ($re)
                    {
                        $this->manager->logInfo('index 创建 ok');
                    }
                    else
                    {
                        $this->manager->logError($json);
                    }
                }
                else
                {
                    $this->manager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->manager->logError($exception->getMessage());
            };

            $this->baseQueue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $mission = new TelegraphMission();
            $mission->setTimeout($this->timeout);

            if (!is_null($this->proxy))
            {
                $mission->setProxy($this->proxy);
            }

            $json = $this->style->placeHolder('index 建设中...');
            $mission->setAccessToken($token);
            $mission->createPage($this->brandTitle, $json, true);

            $this->baseQueue->execMissionDirect($mission);
        }

        public function createFirstTypePageToQueue(): void
        {
            $typeTab = $this->getMediaSourceTypeTable();

            $types = $typeTab->tableIns()->field(implode(',', [
                $typeTab->getPkField(),
                $typeTab->getNameField(),
            ]))->select();

            foreach ($types as $k => $type)
            {
                $token    = $this->getRandToken();
                $typeName = $type[$typeTab->getNameField()];

                $mission = new TelegraphMission();
                $mission->setTimeout($this->timeout);
                $mission->token    = $token;
                $mission->typeInfo = $type;

                if (!is_null($this->proxy))
                {
                    $mission->setProxy($this->proxy);
                }

                $title = $this->makePageName($typeName);
                $json  = $this->style->placeHolder($title . ' 建设中...');
                $mission->setAccessToken($token);
                $mission->createPage($title, $json, true);

                $this->manager->logInfo('createFirstTypePageToQueue: ' . $title);

                $this->createFirstTypePageQueue->addNewMission($mission);
            }
        }

        public function createDetailPageToQueue(): void
        {
            $collectionTab = $this->getMediaSourceCollectionTable();
            $typeTab       = $this->getMediaSourceTypeTable();
            $itemTab       = $this->getMediaSourceItemTable();

            $collectionTab->tableIns()->alias('collection')->field(implode(',', [
                'collection.*',
                'type.' . $typeTab->getNameField() . ' as type_name',
            ]))
                ->join($typeTab->getName() . ' type', 'collection.' . $collectionTab->getTypeIdField() . ' = type.' . $typeTab->getPkField(), 'left')
//                ->fetchSql(true)
//                ->select();

                ->chunk(500, function($collections) use (
                    $collectionTab, $typeTab, $itemTab
                ) {

                    foreach ($collections as $collection)
                    {
                        $token = $this->getRandToken();
                        $title = $collection['name'];

                        $mission = new TelegraphMission();
                        $mission->setTimeout($this->timeout);
                        $mission->token      = $token;
                        $mission->collection = $collection;

                        if (!is_null($this->proxy))
                        {
                            $mission->setProxy($this->proxy);
                        }

                        $title = $this->makePageName($title);
                        $json  = $this->style->placeHolder($title . ' 建设中...');
                        $mission->setAccessToken($token);
                        $mission->createPage($title, $json, true);

                        $this->manager->logInfo('createDetailPageToQueue: ' . $collection['id'] . ' - ' . $title);

                        $this->createDetailPageQueue->addNewMission($mission);
                    }

                }, 'collection.' . $collectionTab->getPkField());
        }

        public function createTypeAllPageToQueue(): void
        {
            $webPageTab = $this->getWebPagesTable();
            $typeTab    = $this->getMediaSourceTypeTable();

            $whereType2 = [
                [
                    $webPageTab->getPageTypeField(),
                    '=',
                    2,
                ],
            ];

            $whereType3 = [
                [
                    $webPageTab->getPageTypeField(),
                    '=',
                    3,
                ],
            ];

            //所有涉及到的分类
            $typeIds = $webPageTab->tableIns()->where($whereType3)->group($webPageTab->getCollectionTypeIdField())
                ->column($webPageTab->getCollectionTypeIdField());

            //遍历分类，生成分页信息
            foreach ($typeIds as $k => $typeId)
            {
                //查出分类详细信息
                $type = $typeTab->tableIns()->where([$typeTab->getPkField() => $typeId,])->find();

                $typeName = $type[$typeTab->getNameField()];

                //当前分类总记录数
                $count = $webPageTab->tableIns()->where($whereType3)
                    ->where([$webPageTab->getCollectionTypeIdField() => $typeId])->count();

                //折合总页数
                $totalPages = (int)ceil($count / $this->pageRow);

                //生成当前分类页数信息，为每页构造列表页面
                for ($pageNow = 1; $pageNow <= $totalPages; $pageNow++)
                {
                    $results = $webPageTab->tableIns()->where($whereType3)
                        ->where([$webPageTab->getCollectionTypeIdField() => $typeId,])
                        ->order($webPageTab->getCollectionIdField(), 'asc')->paginate([
                            'list_rows' => $this->pageRow,
                            'page'      => $pageNow,
                        ]);

                    preg_match_all('%\d+(?=</a>|</span>)%im', (string)$results->render(), $result, PREG_PATTERN_ORDER);
                    $pagesNum = $result[0];

                    sort($pagesNum);
                    //如果是第一页，要更新之前生成的第一页的数据
                    if ($pageNow == 1)
                    {
                        $webPageTab->tableIns()->where($whereType2)
                            ->where([$webPageTab->getIsFirstTypePageField() => 1,])
                            ->where([$webPageTab->getCollectionTypeIdField() => $typeId,])->update([
                                // page_num 不更新也可以，在生成页面时已经写入
                                // $webPageTab->getPageNumField()     => $pageNow,
                                $webPageTab->getPageNumListField() => implode(',', $pagesNum),
                            ]);
                    }
                    else
                    {
                        $token = $this->getRandToken();

                        $mission = new TelegraphMission();
                        $mission->setTimeout($this->timeout);
                        $mission->token    = $token;
                        $mission->typeInfo = $type;
                        $mission->pageNow  = $pageNow;
                        $mission->pagesNum = $pagesNum;

                        if (!is_null($this->proxy))
                        {
                            $mission->setProxy($this->proxy);
                        }

                        $title = $this->makePageName($typeName);
                        $json  = $this->style->placeHolder($title . ' 建设中...');
                        $mission->setAccessToken($token);
                        $mission->createPage($title, $json, true);

                        $this->manager->logInfo('createTypeAllPageToQueue: ' . $typeName . ' - ' . $pageNow);

                        $this->createTypeAllPageQueue->addNewMission($mission);
                    }
                }
            }

        }

        public function updateTypeAllPageToQueue(): void
        {
            $webPageTab = $this->getWebPagesTable();

            $whereType2 = [
                [
                    $webPageTab->getPageTypeField(),
                    '=',
                    2,
                ],
            ];

            $whereType3 = [
                [
                    $webPageTab->getPageTypeField(),
                    '=',
                    3,
                ],
            ];

            //查询遍历所有的详情页面
            $listPages = $webPageTab->tableIns()->where($whereType2)->cursor();

            foreach ($listPages as $k => $page_)
            {
                $params   = json_decode($page_[$webPageTab->getParamsField()], 1);
                $typeInfo = $params['type'];
                $title    = $typeInfo[$this->getMediaSourceTypeTable()->getNameField()];

                //分页按钮
                $pageUrls = $webPageTab->tableIns()->where($whereType2)->where([
                    [
                        $webPageTab->getPageNumField(),
                        'in',
                        explode(',', $page_[$webPageTab->getPageNumListField()]),
                    ],
                    [
                        $webPageTab->getCollectionTypeIdField(),
                        '=',
                        $page_[$webPageTab->getCollectionTypeIdField()],
                    ],
                ])->order($webPageTab->getPageNumField(), 'asc')->field(implode(',', [
                    $webPageTab->getUrlField(),
                    $webPageTab->getPageNumField(),
                ]))->select();

                $pageButtons = [];

                foreach ($pageUrls as $urls)
                {
                    $pageButtons[] = [
                        "href"    => $urls[$webPageTab->getUrlField()],
                        "caption" => ($urls[$webPageTab->getPageNumField()] !== $page_[$webPageTab->getPageNumField()]) ? $urls[$webPageTab->getPageNumField()] : "<{$urls[$webPageTab->getPageNumField()]}>",
                    ];
                }

                //中间条目列表
                $contentsList = [];

                $items = $webPageTab->tableIns()->where($whereType3)->where([
                    [
                        $webPageTab->getCollectionTypeIdField(),
                        '=',
                        $page_[$webPageTab->getCollectionTypeIdField()],
                    ],
                ])->field(implode(',', [
                    $webPageTab->getUrlField(),
                    $webPageTab->getTitleField(),
                ]))->order($webPageTab->getCollectionIdField(), 'asc')->paginate([
                    'list_rows' => $this->pageRow,
                    'page'      => $page_[$webPageTab->getPageNumField()],
                ]);

                foreach ($items as $item)
                {
                    $contentsList[] = [
                        "href"    => $item[$webPageTab->getUrlField()],
                        "caption" => $item[$webPageTab->getTitleField()],
                    ];
                }

                $this->style->updateTypePage($typeInfo, $pageButtons, $contentsList);

                $token   = $page_[$webPageTab->getTokenField()];
                $mission = new TelegraphMission();
                $mission->setTimeout($this->timeout);

                if (!is_null($this->proxy))
                {
                    $mission->setProxy($this->proxy);
                }

                $mission->setAccessToken($token);
                $mission->editPage($page_[$webPageTab->getPathField()], $this->makePageName($title), $this->style->toJson(), true);

                $this->manager->logInfo('updateTypeAllPageToQueue: ' . $title . ' - ' . $k);

                $this->updateTypeAllPageQueue->addNewMission($mission);
            }
        }

        public function updateDetailPageToQueue(): void
        {
            $webPageTab    = $this->getWebPagesTable();
            $typeTab       = $this->getMediaSourceTypeTable();
            $collectionTab = $this->getMediaSourceCollectionTable();
            $itemTab       = $this->getMediaSourceItemTable();

            $whereType3 = [
                [
                    $webPageTab->getPageTypeField(),
                    '=',
                    3,
                ],
            ];

            $webPageTab->tableIns()->alias('webpages')
                ->join($typeTab->getName() . ' type', 'webpages.' . $webPageTab->getCollectionTypeIdField() . ' = type.' . $typeTab->getPkField(), 'left')
                ->field(implode(',', [
                    'webpages.*',
                    'type.' . $typeTab->getNameField() . ' as type_name',
                ]))->where($whereType3)
                ->chunk(100, function($collections) use ($whereType3, $collectionTab, $itemTab, $webPageTab, $typeTab) {
                    foreach ($collections as $collection)
                    {
                        $params         = json_decode($collection[$webPageTab->getParamsField()], 1);
                        $token          = $params['token'];
                        $collectionInfo = $params['collection'];

                        $title = $collectionInfo[$collectionTab->getNameField()];

                        $medias = $itemTab->tableIns()->where([
                            [
                                $itemTab->getCollectionIdField(),
                                '=',
                                $collection[$webPageTab->getCollectionIdField()],
                            ],
                        ])->order($itemTab->getPkField(), 'asc')->select();

                        $prve_next_item = [];

                        $prve = $webPageTab->tableIns()->where($whereType3)->where([
                            [
                                $webPageTab->getCollectionTypeIdField(),
                                '=',
                                $collection[$webPageTab->getCollectionTypeIdField()],
                            ],
                            [
                                $webPageTab->getCollectionIdField(),
                                '<',
                                $collection[$webPageTab->getCollectionIdField()],
                            ],

                        ])->order($webPageTab->getCollectionIdField(), 'desc')->find();

                        if ($prve)
                        {
                            $prve_next_item[] = [
                                "href"    => $prve[$webPageTab->getUrlField()],
                                "caption" => "上一篇 -> " . $prve[$webPageTab->getTitleField()],
                            ];
                        }

                        $next = $webPageTab->tableIns()->where($whereType3)->where([
                            [
                                $webPageTab->getCollectionTypeIdField(),
                                '=',
                                $collection[$webPageTab->getCollectionTypeIdField()],
                            ],
                            [
                                $webPageTab->getCollectionIdField(),
                                '>',
                                $collection[$webPageTab->getCollectionIdField()],
                            ],

                        ])->order($webPageTab->getCollectionIdField(), 'asc')->find();
                        if ($next)
                        {
                            $prve_next_item[] = [
                                "href"    => $next[$webPageTab->getUrlField()],
                                "caption" => "下一篇 -> " . $next[$webPageTab->getTitleField()],
                            ];
                        }

                        $prve_next = E::AListWithCaption1($prve_next_item, false);

                        $imgs   = [];
                        $videos = [];
                        $texts  = [];

                        foreach ($medias as $k => $v)
                        {
                            if ($v[$itemTab->getTypeField()] == 1)
                            {
                                $imgs[] = $v[$itemTab->getUrlField()];
                            }
                            if ($v[$itemTab->getTypeField()] == 2)
                            {
                                $videos[] = $v[$itemTab->getUrlField()];
                            }
                            if ($v[$itemTab->getTypeField()] == 3)
                            {
                                $texts[] = $v[$itemTab->getUrlField()];
                            }
                        }

                        $this->style->updateDetailPage($collectionInfo, $collection, $prve_next, $imgs, $videos, $texts);

                        $mission = new TelegraphMission();
                        $mission->setTimeout($this->timeout);
                        $mission->token      = $token;
                        $mission->collection = $collection;

                        if (!is_null($this->proxy))
                        {
                            $mission->setProxy($this->proxy);
                        }

                        $mission->setAccessToken($token);
                        $mission->editPage($collection['path'], $this->makePageName($title), $this->style->toJson(), true);

                        $this->manager->logInfo('updateDetailPageToQueue: ' . $collection['id'] . ' - ' . $title);

                        $this->updateDetailPageQueue->addNewMission($mission);
                    }

                }, 'webpages.' . $webPageTab->getPkField());
        }

        public function updateIndex(): void
        {
            $webPageTab = $this->getWebPagesTable();

            $indexPageInfo = $this->getindexPageInfo();

            $token = $indexPageInfo[$webPageTab->getTokenField()];

            $this->baseQueue->setMissionProcessor(new GuzzleMissionProcessor());
            $this->baseQueue->setMaxTimes($this->maxTimes);

            $success = function(TelegraphMission $mission) use ($token) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, 1);

                if ($result['ok'])
                {
                    $this->manager->logInfo('更新成功...');
                }
                else
                {
                    $this->manager->logError($result['error']);
                }
            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->manager->logError($exception->getMessage());
            };

            $this->baseQueue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $this->style->updateIndexPage();

            $mission = new TelegraphMission();
            $mission->setTimeout($this->timeout);

            if (!is_null($this->proxy))
            {
                $mission->setProxy($this->proxy);
            }

            $mission->setAccessToken($token);
            $mission->editPage($indexPageInfo['path'], $this->brandTitle, $this->style->toJson(), true);

            $this->baseQueue->execMissionDirect($mission);
        }

        /*
         *
         * ------------------------------------------------------
         *
         * */

        public function listenCreateFirstTypePage(): void
        {
            $queue = $this->createFirstTypePageQueue;

            $queue->setExitOnfinish($this->exitOnfinish);
            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->delay);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->maxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $token    = $mission->token;

                $json   = $response->getBody()->getContents();
                $result = json_decode($json, 1);

                if ($result['ok'])
                {
                    $webPageTab = $this->getWebPagesTable();
                    $typeTab    = $this->getMediaSourceTypeTable();

                    $data = [
                        $webPageTab->getPathField()             => $result['result']['path'],
                        $webPageTab->getUrlField()              => $result['result']['url'],
                        $webPageTab->getTitleField()            => $result['result']['title'],
                        $webPageTab->getDescriptionField()      => $result['result']['description'],
                        $webPageTab->getContentField()          => json_encode($result['result']['content'], 256),
                        $webPageTab->getViewsField()            => $result['result']['views'],
                        $webPageTab->getCanEditField()          => (int)$result['result']['can_edit'],
                        $webPageTab->getTokenField()            => $token,
                        //1:首页，2:列表页，3:详情页
                        $webPageTab->getPageTypeField()         => 2,
                        $webPageTab->getIsFirstTypePageField()  => 1,
                        $webPageTab->getCollectionIdField()     => 0,
                        $webPageTab->getCollectionTypeIdField() => $mission->typeInfo[$typeTab->getPkField()],
                        $webPageTab->getPageNumField()          => 1,
                        $webPageTab->getPageNumListField()      => '',
                        $webPageTab->getUpdateTimeField()       => time(),
                        $webPageTab->getCreateTimeField()       => time(),
                        $webPageTab->getParamsField()           => json_encode([
                            "type" => $mission->typeInfo,
                        ], 256),
                    ];

                    if (!$webPageTab->isPkAutoInc())
                    {
                        $data[$webPageTab->getPkField()] = $webPageTab->calcPk();
                    }

                    $re = $webPageTab->tableIns()->insert($data);

                    if ($re)
                    {
                        $this->manager->logInfo('ok-' . $mission->typeInfo[$this->getMediaSourceTypeTable()->getNameField()]);
                    }
                    else
                    {
                        $this->manager->logError($json);
                    }
                }
                else
                {
                    $this->manager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->manager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));
            $queue->listen();
        }

        public function listenCreateDetailPage(): void
        {
            $queue = $this->createDetailPageQueue;

            $queue->setExitOnfinish($this->exitOnfinish);
            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->delay);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->maxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $token    = $mission->token;

                $json   = $response->getBody()->getContents();
                $result = json_decode($json, 1);

                if ($result['ok'])
                {
                    $webPageTab    = $this->getWebPagesTable();
                    $collectionTab = $this->getMediaSourceCollectionTable();

                    $data = [
                        $webPageTab->getPathField()             => $result['result']['path'],
                        $webPageTab->getUrlField()              => $result['result']['url'],
                        $webPageTab->getTitleField()            => $result['result']['title'],
                        $webPageTab->getDescriptionField()      => $result['result']['description'],
                        $webPageTab->getContentField()          => json_encode($result['result']['content'], 256),
                        $webPageTab->getViewsField()            => $result['result']['views'],
                        $webPageTab->getCanEditField()          => (int)$result['result']['can_edit'],
                        $webPageTab->getTokenField()            => $token,
                        //1:首页，2:列表页，3:详情页
                        $webPageTab->getPageTypeField()         => 3,
                        $webPageTab->getIsFirstTypePageField()  => 0,
                        $webPageTab->getCollectionTypeIdField() => $mission->collection[$collectionTab->getTypeIdField()],
                        $webPageTab->getCollectionIdField()     => $mission->collection[$collectionTab->getPkField()],
                        $webPageTab->getPageNumField()          => 0,
                        $webPageTab->getPageNumListField()      => '',
                        $webPageTab->getUpdateTimeField()       => time(),
                        $webPageTab->getCreateTimeField()       => time(),
                        $webPageTab->getParamsField()           => json_encode([
                            "collection" => $mission->collection,
                            "token"      => $mission->token,
                        ], 256),
                    ];

                    if (!$webPageTab->isPkAutoInc())
                    {
                        $data[$webPageTab->getPkField()] = $webPageTab->calcPk();
                    }

                    $re = $webPageTab->tableIns()->insert($data);

                    if ($re)
                    {
                        $this->manager->logInfo('ok-' . $mission->collection[$collectionTab->getNameField()]);
                    }
                    else
                    {
                        $this->manager->logError($json);
                    }
                }
                else
                {
                    $this->manager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->manager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));
            $queue->listen();
        }

        public function listenCreateTypeAllPage(): void
        {
            $queue = $this->createTypeAllPageQueue;

            $queue->setExitOnfinish($this->exitOnfinish);
            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->delay);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->maxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();

                $json   = $response->getBody()->getContents();
                $result = json_decode($json, 1);

                if ($result['ok'])
                {
                    $webPageTab = $this->getWebPagesTable();
                    $typeTab    = $this->getMediaSourceTypeTable();

                    $data = [
                        $webPageTab->getPathField()             => $result['result']['path'],
                        $webPageTab->getUrlField()              => $result['result']['url'],
                        $webPageTab->getTitleField()            => $result['result']['title'],
                        $webPageTab->getDescriptionField()      => $result['result']['description'],
                        $webPageTab->getContentField()          => json_encode($result['result']['content'], 256),
                        $webPageTab->getViewsField()            => $result['result']['views'],
                        $webPageTab->getCanEditField()          => (int)$result['result']['can_edit'],
                        $webPageTab->getTokenField()            => $mission->token,
                        //1:首页，2:列表页，3:详情页
                        $webPageTab->getPageTypeField()         => 2,
                        $webPageTab->getIsFirstTypePageField()  => 0,
                        $webPageTab->getCollectionTypeIdField() => $mission->typeInfo[$typeTab->getPkField()],
                        $webPageTab->getCollectionIdField()     => 0,
                        $webPageTab->getPageNumField()          => $mission->pageNow,
                        $webPageTab->getPageNumListField()      => implode(',', $mission->pagesNum),
                        $webPageTab->getUpdateTimeField()       => time(),
                        $webPageTab->getCreateTimeField()       => time(),
                        $webPageTab->getParamsField()           => json_encode([
                            "type" => $mission->typeInfo,
                        ], 256),
                    ];

                    if (!$webPageTab->isPkAutoInc())
                    {
                        $data[$webPageTab->getPkField()] = $webPageTab->calcPk();
                    }

                    $re = $webPageTab->tableIns()->insert($data);

                    if ($re)
                    {
                        $this->manager->logInfo('ok-' . $mission->typeInfo[$typeTab->getNameField()]);
                    }
                    else
                    {
                        $this->manager->logError($json);
                    }
                }
                else
                {
                    $this->manager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->manager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));
            $queue->listen();

        }

        public function listenUpdateTypeAllPage(): void
        {
            $queue = $this->updateTypeAllPageQueue;

            $queue->setExitOnfinish($this->exitOnfinish);
            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->delay);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->maxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();

                $json   = $response->getBody()->getContents();
                $result = json_decode($json, 1);

                if ($result['ok'])
                {
                    $this->manager->logInfo('更新成功...');
                }
                else
                {
                    $this->manager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->manager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));
            $queue->listen();
        }

        public function listenUpdateDetailPage(): void
        {
            $queue = $this->updateDetailPageQueue;

            $queue->setExitOnfinish($this->exitOnfinish);
            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->delay);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->maxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();

                $json   = $response->getBody()->getContents();
                $result = json_decode($json, 1);

                if ($result['ok'])
                {
                    $this->manager->logInfo('更新成功...');
                }
                else
                {
                    $this->manager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->manager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));
            $queue->listen();
        }


        /*
         *
         * ------------------------------------------------------
         *
         * */

        public function makePageName(string $name): string
        {
            return $this->brandTitle . '-' . $name;
        }

        public function getRandToken(): string
        {
            $tokens = $this->getCacheManager()->get('$tokens', function($item) {
                $item->expiresAfter(600);
                $tab = $this->getAccountTable();

                $tokens = $tab->tableIns()->column($tab->getAccessTokenField());

                return $tokens;
            });

            $token = $tokens[rand(0, count($tokens) - 1)];

            return $token;
        }

        public function getRandDetailPages($count = 10): array
        {
            $detailPages = $this->getCacheManager()->get('$randDetailPages', function($item) {
                $item->expiresAfter(600);

                $webPageTab = $this->getWebPagesTable();

                $items = $webPageTab->tableIns()->where([
                    [
                        $webPageTab->getPageTypeField(),
                        '=',
                        3,
                    ],
                ])->field(implode(',', [
                    $webPageTab->getUrlField(),
                    $webPageTab->getTitleField(),
                ]))->select();

                $pagesList = [];
                foreach ($items as $item)
                {
                    $pagesList[] = [
                        "href"    => $item[$webPageTab->getUrlField()],
                        "caption" => $item[$webPageTab->getTitleField()],
                    ];
                }

                return $pagesList;
            });

            $count = min($count, count($detailPages));

            // 获取随机键
            $randomKeys = array_rand($detailPages, $count);

            // 根据随机键提取元素
            $randomItems = [];
            foreach ($randomKeys as $key)
            {
                $randomItems[] = $detailPages[$key];
            }

            return $randomItems;
        }

        public function getindexPageInfo(): array
        {
            return $this->getCacheManager()->get('$indexPage', function($item) {
                $item->expiresAfter(60);

                $webPageTab = $this->getWebPagesTable();

                //index 页面信息
                return $webPageTab->tableIns()->where([
                    [
                        $webPageTab->getPageTypeField(),
                        '=',
                        1,
                    ],
                ])->find();
            });
        }

        public function getTypeFirstPage(): array
        {
            return $this->getCacheManager()->get('$typeFirstPage', function($item) {
                $item->expiresAfter(600);

                $webPageTab = $this->getWebPagesTable();
                $typeTab    = $this->getMediaSourceTypeTable();

                //获取所有分类第一页的记录
                $typeFirstPage = $webPageTab->tableIns()->where([
                    [
                        $webPageTab->getIsFirstTypePageField(),
                        '=',
                        1,
                    ],
                    [
                        $webPageTab->getPageTypeField(),
                        '=',
                        2,
                    ],
                ])->order($webPageTab->getPkField(), 'asc')->select()->toArray();

                $typeFirstPageArr = [];
                foreach ($typeFirstPage as $k => $v)
                {
                    $params = json_decode($v[$webPageTab->getParamsField()], 1);

                    $typeFirstPageArr[] = [
                        "href"    => $v[$webPageTab->getUrlField()],
                        "caption" => $params['type'][$typeTab->getNameField()],
                    ];
                }

                return $typeFirstPageArr;
            });
        }

        /*
         *
         * ------------------------------------------------------
         *
         * */

        protected function initCache(): static
        {
            $this->container->set('cacheManager', function(Container $container) {
                $marshaller   = new DeflateMarshaller(new DefaultMarshaller());
                $cacheManager = new RedisAdapter($container->get('redisClient'), 'tg_pages_', 0, $marshaller);

                return $cacheManager;
            });

            return $this;
        }

        protected function initQueue(): static
        {
            $this->baseQueue                = $this->manager->initQueue(static::BASE_QUEUE);
            $this->createFirstTypePageQueue = $this->manager->initQueue(static::CREATE_FIRST_TYPE_PAGE_QUEUE);
            $this->createDetailPageQueue    = $this->manager->initQueue(static::CREATE_DETAIL_PAGE_QUEUE);
            $this->createTypeAllPageQueue   = $this->manager->initQueue(static::CREATE_TYPE_ALL_PAGE_QUEUE);
            $this->updateTypeAllPageQueue   = $this->manager->initQueue(static::UPDATE_TYPE_ALL_PAGE_QUEUE);
            $this->updateDetailPageQueue    = $this->manager->initQueue(static::UPDATE_DETAIL_PAGE_QUEUE);

            return $this;
        }

    }