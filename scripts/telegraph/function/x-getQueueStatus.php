<?php

    require '../common.php';

    $res = $manager->getQueueStatus();
    print_r($res);
