<?php

    use Coco\tgDownloader\Manager;
    use Coco\tgDownloader\tables\Message;
    use Coco\tgDownloader\tables\Post;
    use Coco\telegraph\dom\E;

    require __DIR__ . '/../vendor/autoload.php';

    /*
     * 基础配置
     * -------------------------------------------------------------------------------
     * */

    $config = include __DIR__ . '/config.php';

    $bootToken = $config['botToken'];

    $manager = new Manager(bootToken: $bootToken, basePath: __DIR__ . '/../data');
    $manager->setDebug(true);
    $manager->setMaxDownloading(6);
    $manager->setDownloadDelayInSecond(2);
    $manager->setMaxDownloadTimeout(3600);
    $manager->setMediaOwner('www');
    //$manager->setMediaStorePath(__DIR__ . '/medias');

    $url = 'http://127.0.0.1:8101/tg/scripts/endpoint/type1.php';
    $manager->setWebHookUrl($url);

    /*
     * 初始化扫描器
     * -------------------------------------------------------------------------------
     * */

    $manager->initTheDownloadMediaScanner();
    $manager->initTheFileMoveScanner();
    $manager->initTheMigrationScanner();

    /*
     * 初始化其他组件
     * -------------------------------------------------------------------------------
     * */
    $manager->initRedis(db: 2);

    $manager->initMysql(db: 'ithinkphp_telegraph_test01');
//    $manager->initMysql('tg', '127.0.0.1', 'baseManager', 'jiojio00568');

    $manager->initTelegramBotApi(apiId: $config['apiId'], apiHash: $config['apiHash']);

    $manager->initTelegramApiGuzzle();

    $manager->enableRedisHandler(db: 2, logName: 'te-download-log');
    $manager->enableEchoHandler();

    /*
     * telegraph 相关配置
     * -------------------------------------------------------------------------------
     * */

    $style = new \Coco\tgDownloader\styles\Style1();

    $style->setAdvArray([
        (E::span('自定义广告区域1111')),
        (E::br()),
        (E::span('自定义广告区域2222')),
        (E::br()),
        (E::splitLine('-')),
    ]);

    $style->addNav('自定义链接-百度', 'https://baidu.com');
    $style->addNav('自定义链接-google', 'https://google.com');
    $style->setMediaUrl('https://ex.72da.com/medias/');

    $manager->setStyle($style);
    $manager->setTelegraphProxy('192.168.0.111:1080');
    $manager->setBrandTitle('汪汪');
    $manager->setPageRow(5);
    $manager->setMaxTimes(50);
    $manager->setQueueDelayMs(0);
    $manager->setTelegraphTimeout(50);

    /*
     * 初始化公用表
     * -------------------------------------------------------------------------------
     * */

    $manager->initMessageTable('te_message', function(Message $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(false);
        $table->setPkValueCallable($registry::snowflakePKCallback());
    });

    $manager->initPostTable('te_post', function(Post $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(false);
        $table->setPkValueCallable($registry::snowflakePKCallback());
    });

    $manager->initFileTable('te_file', function(\Coco\tgDownloader\tables\File $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(false);
        $table->setPkValueCallable($registry::snowflakePKCallback());
    });

    $manager->initTypeTable('te_type', function(\Coco\tgDownloader\tables\Type $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(true);
    });

    $manager->initAccountTable('te_account', function(\Coco\tgDownloader\tables\Account $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(true);
    });

    $manager->initPagesTable('te_pages', function(\Coco\tgDownloader\tables\Pages $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(false);
        $table->setPkValueCallable($registry::snowflakePKCallback());
    });

    $manager->initCommonProperty();


