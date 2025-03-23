<?php

    require '../common.php';

    $basePath = (realpath(__DIR__ . '/../../../') . '/data/media');

    //正常视频
    $path = '2025-01/04/videos/D/1.mp4';

    //出错视频
//    $path = '2025-01/04/videos/D/2.mp4';

    $callback = function($path) use ($basePath) {
        return $basePath . '/' . $path;
    };


    $manager->makeVideoCoverToQueue('123','456',$path, $callback);
