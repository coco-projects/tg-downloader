<?php

    require '../common.php';

    $manager->makeVideoCoverQueue->setExitOnfinish(false);

    $manager->listenConvertM3u8();
