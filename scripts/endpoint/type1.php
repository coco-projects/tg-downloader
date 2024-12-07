<?php

    require '../common.php';

    $isTest = !true;

    $raw = file_get_contents('php://input');

    if (!$raw)
    {
        exit('wrong');
    }

    $raw = json_decode($raw, 1);
    $raw = json_encode($raw, 256);

    $basePath = 'raw_data1/';

    is_dir($basePath) or mkdir($basePath);

    if (!$isTest)
    {
        try
        {
            $manager->webHookEndPoint($raw);
        }
        catch (\Exception $e)
        {
            $msg = [
                "update" => json_decode($raw, 1),
                "error"  => $e->getMessage(),
            ];

            file_put_contents(__DIR__ . '/error.txt', json_encode($msg, 256) . PHP_EOL, FILE_APPEND);
        }
    }
    else
    {

//    file_put_contents($basePath . '$_POST.json', json_encode($_POST, 256) . PHP_EOL, 8);
//    file_put_contents($basePath . '$_GET.json', json_encode($_GET, 256) . PHP_EOL, 8);
//    file_put_contents($basePath . '$_FILES.json', json_encode($_FILES, 256) . PHP_EOL, 8);
//    file_put_contents($basePath . '$_SERVER.json', json_encode($_SERVER, 256) . PHP_EOL, 8);
        file_put_contents($basePath . '$_input.json', $raw . PHP_EOL, 8);

    }