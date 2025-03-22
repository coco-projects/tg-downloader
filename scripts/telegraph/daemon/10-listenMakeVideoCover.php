<?php

    require '../common.php';

    $manager->makeVideoCoverQueue->setExitOnfinish(false);
    $manager->listenMakeVideoCover();
