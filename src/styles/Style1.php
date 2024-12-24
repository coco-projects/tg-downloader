<?php

    namespace Coco\tgDownloader\styles;

    use Coco\telegraph\dom\E;

    class Style1 extends StyleAbstract
    {
        public array $advArray = [];

        public function setAdvArray(array $advArray): void
        {
            $this->advArray = $advArray;
        }

        protected function addAdv(): void
        {
            $this->importPageElements($this->advArray);
        }

        protected function addMainNav(): void
        {
//            $this->addPageElements(E::h4('导航'));
            $this->addPageElements(E::AListWithCaption3($this->navList, '《__CAPTION__》', true, ' '));
            $this->addPageElements(E::br());
//            $this->addPageElements(E::splitLine());
        }

        protected function addTypeNav(): void
        {
//            $this->addPageElements(E::h4('栏目'));
            $this->addPageElements($this->getFirstTypeList());
            $this->addPageElements(E::br());
//            $this->addPageElements(E::splitLine());
        }

        protected function addRandDetailPages(): void
        {
            $this->addPageElements(E::br());
//            $this->addPageElements(E::splitLine());
            $this->addPageElements(E::h4('随机看'));
            $this->addPageElements(E::AListWithCaption1($this->manager->getRandDetailPages(20), true));
        }

        /*
         * ------------------------------------------------------------------
         * ------------------------------------------------------------------
         */
        public function updateIndexPage(): static
        {
            $this->initNavList();

            $this->addMainNav();
            $this->addTypeNav();
            $this->addAdv();

            $eles = [
                //                E::splitLine('-'),
            ];

            $this->importPageElements($eles);
            $this->addRandDetailPages();

            return $this;
        }

        public function updateTypePage($typeInfo, $pageButtons, $contentsList): static
        {
            $this->initNavList();

            $this->addMainNav();
            $this->addTypeNav();
            $this->addAdv();

            $title = $typeInfo[$this->manager->getTypeTable()->getNameField()];

            $eles = [
                E::h4($title),
                E::strong('页码: '),

                E::AListWithCaption3($pageButtons, '[__CAPTION__]', true, ' '),
                E::br(),

                E::AListWithCaption1($contentsList, true),

                E::strong('页码: '),
                E::AListWithCaption3($pageButtons, '[__CAPTION__]', true, ' '),
            ];

            $this->importPageElements($eles);
            $this->addRandDetailPages();

            return $this;
        }

        public function updateDetailPage($postInfo, $page, $prve_next, $files): static
        {
//          $indexPageInfo = $this->manager->getindexPageInfo();

            $msgTable  = $this->manager->getMessageTable();
            $postTable = $this->manager->getPostTable();
            $typeTable = $this->manager->getTypeTable();
            $fileTable = $this->manager->getFileTable();

            $this->initNavList();

            $this->addMainNav();
            $this->addTypeNav();
            $this->addAdv();

            $contents = $postInfo[$postTable->getContentsField()];

            $title = '无标题';
            if ($contents)
            {
                $title = $this->manager::truncateUtf8String($contents, 50);
            }

            $title = preg_replace('#[\r\n]+#iu', ' ', $title);

            $typeName = $page['type_name'];
            $this->addPageElements($prve_next);
            $this->addPageElements(E::splitLine());

            $this->addPageElements(E::strong($typeName));
            $this->addPageElements(E::strong(E::span(' > ' . $title)));
            $this->addPageElements(E::br());

            $videos    = [];
            $imgs      = [];
            $documents = [];

            foreach ($files as $k => $file)
            {
                $uri = '/' . ltrim($file[$fileTable->getPathField()], '/');
                if (!is_null($this->mediaUrl) && str_starts_with($this->mediaUrl, 'http'))
                {
                    $uri = rtrim($this->mediaUrl, '/') . $uri;
                }

                $file_type = static::getMediaType($file[$fileTable->getMimeTypeField()]);
                switch ($file_type)
                {
                    case static::FILE_IMAGE :
                        $imgs[] = $uri;
                        break;

                    case static::FILE_VIDEO :
                        $videos[] = $uri;
                        break;

                    case static::FILE_DOCUMENT :
                        $documents[] = [
                            "href"    => $uri,
                            "caption" => $file[$fileTable->getFileNameField()],
                        ];
                        break;

                    default :
                        #...
                        break;
                }
            }

            if ($contents)
            {
                $lines = preg_split('#[\r\n]+#iu', $contents, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($lines as $k => $line)
                {
                    $line = trim($line);
                    $this->addPageElements(E::p($line));
                }

                //youtube
                preg_match_all('%https://www\.youtube\.com/watch\?v=[\da-z\-]+%sim', $contents, $result, PREG_PATTERN_ORDER);
                if (isset($result[0]) && count($result[0]))
                {
                    $youtubes = $result[0];
                    foreach ($youtubes as $url)
                    {
                        $this->addPageElements(E::youtube($url, $url));
                    }
                }

                //vimeo
                preg_match_all('%https://vimeo\.com/\d+%sim', $contents, $result, PREG_PATTERN_ORDER);
                if (isset($result[0]) && count($result[0]))
                {
                    $vimeos = $result[0];
                    foreach ($vimeos as $url)
                    {
                        $this->addPageElements(E::vimeo($url, $url));
                    }
                }

                //twitter
                preg_match_all('%https://twitter.com/[a-z\d\-_]+?/status/\d+%sim', $contents, $result, PREG_PATTERN_ORDER);
                if (isset($result[0]) && count($result[0]))
                {
                    $twitters = $result[0];
                    foreach ($twitters as $url)
                    {
                        $this->addPageElements(E::twitter($url, $url));
                    }
                }
            }

            if (count($imgs))
            {
                $this->addPageElements(E::pictureList($imgs));
            }

            if (count($videos))
            {
                foreach ($videos as $k => $v)
                {
                    $this->addPageElements(E::video($v));
                }
            }

            if (count($documents))
            {
                $this->addPageElements(E::strong('附件下载:'));
                $this->addPageElements(E::br());
                $this->addPageElements(E::AListWithCaption1($documents));
            }

            $this->addPageElements(E::splitLine());
            $this->addPageElements($prve_next);
            $this->addRandDetailPages();

            return $this;
        }

    }