<?php

    use Coco\commandRunner\PhpLauncher;

    require '../../vendor/autoload.php';

    $queues = [
        __DIR__ . '/../daemon/1-scanAndDownload.php',
        __DIR__ . '/../daemon/2-scanAndMoveFile.php',
        __DIR__ . '/../daemon/3-scanAndMirgrateMediaToDb.php',
    ];

    $launchers = [];
    foreach ($queues as $k => $script)
    {
        $launchers[$script] = new PhpLauncher($script);
    }

