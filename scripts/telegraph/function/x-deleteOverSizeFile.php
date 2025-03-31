<?php

    require '../common.php';

    $manager->deleteOverSizeFile(1024 * 1024 * 8, function(string $file) use ($manager) {
        $file = $manager->getTelegramMediaStorePath() . $file;
        echo $file;
        echo PHP_EOL;

        return $file;
    });
