<?php

    require '../common.php';

    //设置webhook
    $info = $manager->updateWebHook();

    print_r($info);
