<?php

    use Coco\tgDownloader\Manager;
    use Coco\tgDownloader\tables\Message;
    use Coco\tgDownloader\tables\Post;

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

    $manager->setTypeMap([
        -1001989362140 => 1,
    ]);

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
    $manager->initRedis();

    // $manager->initMysql(db: 'ithinkphp_telegraph_test01');
    $manager->initMysql('tg', '127.0.0.1', 'baseManager', 'jiojio00568');

    $manager->initTelegramBotApi(apiId: $config['apiId'], apiHash: $config['apiHash']);

    $manager->initTelegramApiGuzzle();

    $manager->enableRedisHandler(db: 2, logName: 'te-download-log');
    $manager->enableEchoHandler();

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
        $table->setIsPkAutoInc(false);
        $table->setPkValueCallable($registry::snowflakePKCallback());
    });

    $manager->initAccountTable('te_account', function(\Coco\tgDownloader\tables\Account $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(false);
        $table->setPkValueCallable($registry::snowflakePKCallback());
    });

    /*
     * 初始化业务页面表
     * 共同数据要生成多套系统时，修改表名即可
     * -------------------------------------------------------------------------------
     * */

    $manager->initPagesTable('te_pages', function(\Coco\tgDownloader\tables\Pages $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(false);
        $table->setPkValueCallable($registry::snowflakePKCallback());
    });






