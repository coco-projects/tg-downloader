<?php

    require '../common.php';

    $cdnApi = 'https://cdn15333187.blazingcdn.net';

    $videoFileInfo = [
        'id'             => 1089184750893009580,
        'post_id'        => 1089184750893009477,
        'file_size'      => 4321221,
        'file_name'      => 'IMG_4742.MP4',
        'path'           => 'medias/2025-01/05/videos/1/10427025855086661.mp4',
        'media_group_id' => 13941672612156469,
        'ext'            => 'mp4',
        'mime_type'      => 'video/mp4',
        'time'           => 1742709080,
    ];


    $callback = function($path) use ($cdnApi) {
        return $cdnApi . '/' . $path;
    };
    $manager->cdnPrefetchToQueue($videoFileInfo, $callback);
