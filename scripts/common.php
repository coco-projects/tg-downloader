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

    $manager = new Manager(
        $config['botToken'],
        $config['apiId'],
        $config['apiHash'],
        __DIR__ . '/../data',
        'space1'
    );

    $manager->setDebug(true);
    $manager->setTelegramMediaMaxDownloading(6);
    $manager->setTelegramMediaDownloadDelayInSecond(2);
    $manager->setTelegramMediaMaxDownloadTimeout(100);
    $manager->setMediaOwner('www');
//    $manager->setTelegramMediaStorePath(__DIR__ . '/medias');
    $manager->setTelegramMediaMaxFileSize(3 * 1024 * 1024);

    $url = 'http://127.0.0.1:8101/tg/scripts/endpoint/type1.php';
    $manager->setTelegramWebHookUrl($url);
    $manager->setRedisConfig(db: 12);

    $manager->setMysqlConfig(db: 'ithinkphp_telegraph_test01');
//    $manager->setMysqlConfig('tg', '127.0.0.1', 'baseManager', 'jiojio00568');

    $manager->setLocalServerPort(8081);
    $manager->setStatisticsPort(8082);

    $manager->setEnableRedisLog(true);
    $manager->setEnableEchoLog(true);

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

    $manager->setTelegraphPageStyle($style);
    $manager->setTelegraphProxy('192.168.0.111:1080');
    $manager->setTelegraphPageBrandTitle('汪汪');
    $manager->setTelegraphPageRow(5);
    $manager->setTelegraphQueueMaxTimes(50);
    $manager->setTelegraphQueueDelayMs(0);
    $manager->setTelegraphTimeout(50);

    $manager->initServer();

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


