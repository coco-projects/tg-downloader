<?php

    require '../common.php';

    $basePath = (realpath(__DIR__ . '/../../../') . '/data/media');

    $videoFileInfo = [
        'id'             => 1089184750893009580,
        'post_id'        => 1089184750893009477,
        'file_size'      => 4321221,
        'file_name'      => 'IMG_4742.MP4',

        //正常视频
//        'path'           => '2025-01/04/videos/D/1.mp4',
        //出错视频
        'path'           => '2025-01/04/videos/D/2.mp4',
        //长视频
//        'path'           => '2025-01/04/videos/D/3.mp4',
        'media_group_id' => 13941672612156469,
        'ext'            => 'mp4',
        'mime_type'      => 'video/mp4',
        'time'           => 1742709080,
    ];

    $callback = function($path) use ($basePath) {
        return $basePath . '/' . $path;
    };

    $manager->convertM3u8ToQueue($videoFileInfo, $callback);
