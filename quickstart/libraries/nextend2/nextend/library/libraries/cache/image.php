<?php
N2Loader::import('libraries.cache.cache');

class N2CacheImage extends N2Cache {

    protected $_storageEngine = 'filesystem';

    protected function getScope() {
        return 'image';
    }

    public function makeCache($fileExtension, $callable, $parameters = array(), $hash = false) {

        if (!$hash) {
            $hash = $this->generateHash($fileExtension, $callable, $parameters);
        }
        $keepFileName = pathinfo($parameters[1], PATHINFO_FILENAME);
        $fileName     = $hash . (!empty($keepFileName) ? '/' . $keepFileName : '') . '.' . $fileExtension;

        if (!$this->exists($fileName)) {
            $this->set($fileName, call_user_func_array($callable, $parameters));
        }

        return $this->getPath($fileName);
    }

    private function generateHash($fileExtension, $callable, $parameters) {
        return md5(json_encode(array(
            $fileExtension,
            $callable,
            $parameters
        )));
    }
}

class N2StoreImage extends N2Cache {

    protected $_storageEngine = 'filesystem';

    protected function getScope() {
        return 'image';
    }

    public function makeCache($fileName, $content) {
        if (!$this->isImage($fileName)) {
            return false;
        }

        if (!$this->exists($fileName)) {
            $this->set($fileName, $content);
        }

        return $this->getPath($fileName);
    }

    private function isImage($fileName) {
        $supported_image = array(
            'gif',
            'jpg',
            'jpeg',
            'png',
            'mp4',
            'mp3'
        );

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($ext, $supported_image)) {
            return true;
        }

        return false;
    }
}