<?php

    require '../common.php';

    $path   = 'medias/2025-01/05/videos/1/10427025855086661.mp4';
    $cdnApi = 'https://cdn15333187.blazingcdn.net';

    $callback = function($path) use ($cdnApi) {
        return $cdnApi . '/' . $path;
    };
    $manager->cdnPrefetchToQueue($path, $callback);
