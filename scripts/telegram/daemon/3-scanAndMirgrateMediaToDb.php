<?php

    require '../common.php';

    //扫描下载成功的文件，读取json，移动文件
    //更新数据库中文件的路径
    $manager->scanAndMirgrateMediaToDb();
