<?php

    require '../common.php';
    
    exec('whoami', $output);
echo '当前执行的用户是: ' . $output[0];
    
print(get_current_user());exit();
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