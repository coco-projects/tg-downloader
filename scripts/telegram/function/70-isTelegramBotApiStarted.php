<?php

    require '../common.php';

    $info = $manager->isTelegramBotApiStarted();
    var_dump(
        $info);
