<?php
    abstract class EmailController {
        protected string $senderName = '주식회사 디어그린';

        function renewAccessToken($refreshToken){
            return null;
        }

        public function sendEmail($receiver, $title, $contents, $contentsType){
            $isSent = $this->send($receiver, $title, $contents, $contentsType, null);
            return $isSent;
        }

        abstract function send($receiver, $title, $contents, $contentsType, $accessToken);
    }
?>