<?php

    require '0.listen-common.php';

    /**
     * @var $launcher \Coco\commandRunner\PhpLauncher
     */
    foreach ($launchers as $k => $launcher)
    {
        $launcher->stop();

        echo $launcher->getLanuchCommand();
        echo PHP_EOL;
        $launcher->launch();
    }

