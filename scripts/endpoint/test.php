<?php

    require '../common.php';

    $raw = file_get_contents(__DIR__ . '/update.json');

    $lines = preg_split('#[\r\n]+#', $raw, -1, PREG_SPLIT_NO_EMPTY);

    foreach ($lines as $k => $v)
    {
        try
        {
            $manager->webHookEndPoint($v);
            echo 'success';
            echo PHP_EOL;
        }
        catch (\TypeError $e)
        {
            echo $e->getMessage();
            echo PHP_EOL;
        }
    }

