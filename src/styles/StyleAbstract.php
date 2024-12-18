<?php

    namespace Coco\tgDownloader\styles;

    use Coco\telegraph\dom\DomNode;
    use Coco\telegraph\dom\E;
    use Coco\tgDownloader\Manager;

    abstract class StyleAbstract
    {
        protected Manager $manager;

        const FILE_IMAGE    = 1;
        const FILE_VIDEO    = 2;
        const FILE_DOCUMENT = 3;

        public array       $pageElements = [];
        public array       $navList      = [];
        public bool        $inited       = false;
        public string|null $mediaUrl     = null;

        public function __construct()
        {
        }

        /*---------------------------------------------------------------------------------*/
        public function setManager(Manager $manager): static
        {
            $this->manager = $manager;

            return $this;
        }

        public function setMediaUrl(?string $mediaUrl): void
        {
            $this->mediaUrl = $mediaUrl;
        }

        public function addNav(string $text, string $url, bool $prepend = false): static
        {
            if ($prepend)
            {
                array_unshift($this->navList, [
                    "href"    => $url,
                    "caption" => $text,
                ]);
            }
            else
            {
                $this->navList[] = [
                    "href"    => $url,
                    "caption" => $text,
                ];
            }

            return $this;
        }

        public function addPageElements(string|DomNode $pageElements): static
        {
            $this->pageElements[] = $pageElements;

            return $this;
        }

        public function importPageElements(array $elements): static
        {
            foreach ($elements as $k => $v)
            {
                $this->addPageElements($v);
            }

            return $this;
        }

        public function resetPageElements(): static
        {
            $this->pageElements = [];

            return $this;
        }

        public function toJson(): string
        {
            $json = static::domArrayToJson($this->pageElements);
            $this->resetPageElements();

            return $json;
        }

        public function placeHolder(string|DomNode $text): bool|string
        {
            return static::domArrayToJson([E::h3($text)]);
        }

        public function getFirstTypeList(): DomNode
        {
            return E::AListWithCaption3($this->manager->getTypeFirstPage(), '《__CAPTION__》', true, '|');
        }

        protected function initNavList(): void
        {
            if (!$this->inited)
            {
                $indexPageInfo = $this->manager->getIndexPageInfo();
                $this->addNav('首页', $indexPageInfo['url'], true);
            }
            $this->inited = true;
        }

        public static function domArrayToJson(array $doms): bool|string
        {
            $contents = E::NodeRenderToApi(E::container($doms));

            return json_encode($contents, 256);
        }

        protected static function getMediaType(string $meta): int
        {
            if (str_starts_with($meta, 'image'))
            {
                return static::FILE_IMAGE;
            }
            elseif (preg_match('#(?:mp4|webm|ogg|avi|mov|mkv|flv|3gp|wmv)$#', strtolower($meta)))
            {
                return static::FILE_VIDEO;
            }
            else
            {
                return static::FILE_DOCUMENT;
            }
        }

        /*---------------------------------------------------------------------------------*/

        abstract public function updateIndexPage(): static;

        abstract public function updateTypePage($typeInfo, $pageButtons, $contentsList): static;

        abstract public function updateDetailPage($postInfo, $page, $prve_next, $files): static;

    }