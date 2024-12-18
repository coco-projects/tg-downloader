<?php

    namespace Coco\tgDownloader\missions;

    use Coco\queue\missions\HttpMission;

    class TelegraphUploadMission extends HttpMission
    {
        public string $uploadApi = 'https://telegra.ph/upload/';
        public string $filePath;

        public function __construct()
        {
            parent::__construct();
        }

        protected function integration(): void
        {
            $this->setUrl($this->uploadApi);
            $this->setMethod('post');
            $this->addUploadFile($this->filePath, 'file', '', [
                'Accept'           => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Referer'          => $this->uploadApi,
            ]);

            parent::integration();
        }

        public function setFilePath(string $filePath): void
        {
            $this->filePath = $filePath;
        }
    }
