<?php

    namespace Coco\tgDownloader\missions;

    use Coco\queue\missions\HttpMission;

    class TelegraphMission extends HttpMission
    {
        private string $telegraphMethod;
        private string $accessToken;
        public string  $token;
        public int     $index;
        public array   $_postData       = [];
        public ?array   $typeInfo       = [];
        public array   $collection      = [];
        private string $telegraphApiUrl = 'https://api.telegra.ph';

        public function __construct($accessToken = '')
        {
            $this->accessToken = $accessToken;
            parent::__construct();
        }

        protected function integration(): void
        {
            $url = $this->telegraphApiUrl . $this->telegraphMethod;

            $this->setUrl($url);
            $this->setMethod('post');
            $this->addPostData($this->_postData);
            parent::integration();
        }

        public function createAccount(string $shortName = null, string $authorName = null, string $authorUrl = null): void
        {
            $this->telegraphMethod = "/createAccount";
            $shortName and $this->postData ['short_name'] = $shortName;
            $authorName and $this->postData ['author_name'] = $authorName;
            $authorUrl and $this->postData ['author_url'] = $authorUrl;
        }

        public function editAccount(string $shortName = null, string $authorName = null, string $authorUrl = null): void
        {
            $this->auth();
            $this->telegraphMethod = "/editAccountInfo";
            $shortName and $this->postData ['short_name'] = $shortName;
            $authorName and $this->postData ['author_name'] = $authorName;
            $authorUrl and $this->postData ['author_url'] = $authorUrl;
        }

        public function getAccountInfo(): void
        {
            $this->auth();
            $this->telegraphMethod = "/getAccountInfo";
        }

        public function revokeAccessToken(): void
        {
            $this->auth();
            $this->telegraphMethod = "/revokeAccessToken";
        }

        public function createPage(string $title, string $jsonString, bool $returnContent = false): void
        {
            $this->auth();
            $this->telegraphMethod             = "/createPage";
            $this->postData ['title']          = $title;
            $this->postData ['content']        = $jsonString;
            $this->postData ['return_content'] = $returnContent;
        }

        public function editPage(string $path, string $title, string $jsonString, bool $returnContent = false): void
        {
            $this->auth();
            $this->telegraphMethod             = "/editPage";
            $this->postData ['path']           = $path;
            $this->postData ['title']          = $title;
            $this->postData ['content']        = $jsonString;
            $this->postData ['return_content'] = $returnContent;
        }

        public function getPage(string $path, bool $returnHtml = true): void
        {
            $this->auth();
            $this->telegraphMethod          = "/getPage";
            $this->postData ['path']        = $path;
            $this->postData ['return_html'] = $returnHtml;
        }

        public function getPageList(int $page = 0, int $pageSize = 50): void
        {
            $this->auth();
            $this->telegraphMethod     = "/getPageList";
            $this->postData ['offset'] = ($page - 1) * $pageSize;
            $this->postData ['limit']  = $pageSize;
        }

        public function getViews(string $path, int $year = null, int $month = null, int $day = null): void
        {
            $this->auth();
            $this->telegraphMethod   = "/getViews";
            $this->postData ['path'] = $path;
            $year and $this->postData ['year'] = $year;
            $month and $this->postData ['month'] = $month;
            $day and $this->postData ['day'] = $day;
        }

        public function auth(): void
        {
            if (!$this->hasAccessToken())
            {
                throw new \Exception("Method {" . __FUNCTION__ . "} requires access token");
            }
            $this->_postData['access_token'] = $this->accessToken;
        }

        public function hasAccessToken(): bool
        {
            return !!$this->accessToken;
        }

        public function setAccessToken(string $accessToken): static
        {
            $this->accessToken = $accessToken;

            return $this;
        }

        public function getAccessToken(): string
        {
            return $this->accessToken;
        }
    }
