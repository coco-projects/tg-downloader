<?php

    require '../common.php';

    echo '重启api服务器';
    echo PHP_EOL;
    $manager->restartTelegramBotApi();

    sleep(1);

    echo '删除webhook';
    echo PHP_EOL;

    $info = $manager->deleteWebHook();
    print_r($info);

    sleep(1);

    echo '重新设置webhook';
    echo PHP_EOL;
    $info = $manager->updateWebHook();
    print_r($info);