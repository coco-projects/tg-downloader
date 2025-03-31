<?php

    require '../common.php';

    $manager->updatePostContents(function(array $post, \Coco\tgDownloader\tables\Post $postTable) {
        return 'test-' . $post[$postTable->getContentsField()];
    });
