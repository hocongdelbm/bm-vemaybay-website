<?php
class HomeViewList extends ViewList
{
    public function ActivitiesViewList()
    {
        parent::__construct();
    }

    public function display()
    {
        $this->processMaxPostErrors();
        include('modules/Home/index.php');
    }

    public function processMaxPostErrors()
    {
        if ($this->checkPostMaxSizeError()) {
            $this->errors[] = $GLOBALS['app_strings']['UPLOAD_ERROR_HOME_TEXT'];
            $contentLength = $_SERVER['CONTENT_LENGTH'];

            $maxPostSize = ini_get('post_max_size');
            if (stripos($maxPostSize, "k")) {
                $maxPostSize = (int) $maxPostSize * pow(2, 10);
            } elseif (stripos($maxPostSize, "m")) {
                $maxPostSize = (int) $maxPostSize * pow(2, 20);
            }

            $maxUploadSize = ini_get('upload_max_filesize');
            if (stripos($maxUploadSize, "k")) {
                $maxUploadSize = (int) $maxUploadSize * pow(2, 10);
            } elseif (stripos($maxUploadSize, "m")) {
                $maxUploadSize = (int) $maxUploadSize * pow(2, 20);
            }

            $max_size = min($maxPostSize, $maxUploadSize);
            if ($contentLength > $max_size) {
                $errMessage = string_format($GLOBALS['app_strings']['UPLOAD_MAXIMUM_EXCEEDED'], array($contentLength,  $max_size));
            } else {
                $errMessage =$GLOBALS['app_strings']['UPLOAD_REQUEST_ERROR'];
            }

            $this->errors[] = '* '.$errMessage;
            $this->displayErrors();
        }
    }
}
