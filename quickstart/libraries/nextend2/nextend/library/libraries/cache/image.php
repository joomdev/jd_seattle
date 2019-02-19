<?php
N2Loader::import('libraries.cache.cache');

class N2CacheImage extends N2Cache {

    protected $_storageEngine = 'filesystem';

    protected $lazy = false;

    protected function getScope() {
        return 'image';
    }

    public function setLazy($lazy) {
        $this->lazy = $lazy;
    }

    public function makeCache($fileExtension, $callable, $parameters = array(), $hash = false) {

        if (!$hash) {
            $hash = $this->generateHash($fileExtension, $callable, $parameters);
        }

        if (strpos($parameters[1], '?') !== false) {
            $fileNameParts = explode('?', $parameters[1]);
            $keepFileName  = pathinfo($fileNameParts[0], PATHINFO_FILENAME);
        } else {
            $keepFileName = pathinfo($parameters[1], PATHINFO_FILENAME);
        }

        $fileName              = $hash . (!empty($keepFileName) ? '/' . $keepFileName : '');
        $fileNameWithExtension = $fileName . '.' . $fileExtension;

        $isCached = $this->exists($fileNameWithExtension);
        if ($isCached) {
            if (!$this->testManifestFile($fileName, $parameters[1])) {
                $isCached = false;
            }
        }

        if (!$isCached) {
            if ($this->lazy) {
                return $parameters[1];
            }

            array_unshift($parameters, $this->getPath($fileNameWithExtension));
            call_user_func_array($callable, $parameters);

            $this->createManifestFile($fileName, $parameters[2]);
        }

        return $this->getPath($fileNameWithExtension);
    }

    private function generateHash($fileExtension, $callable, $parameters) {
        return md5(json_encode(array(
            $fileExtension,
            $callable,
            $parameters
        )));
    }

    protected function testManifestFile($fileName, $originalFile) {
        $manifestKey = $this->getManifestKey($fileName);
        if ($this->exists($manifestKey)) {

            $manifestData = json_decode($this->get($manifestKey), true);

            $newManifestData = $this->getManifestData($originalFile);
            if ($manifestData['mtime'] == $newManifestData['mtime']) {
                return true;
            }
        } else {
            // Backward compatibility
            $this->createManifestFile($fileName, $originalFile);

            return true;
        }

        return false;
    }

    protected function createManifestFile($fileName, $originalFile) {

        $this->set($this->getManifestKey($fileName), json_encode($this->getManifestData($originalFile)));
    }

    private function getManifestData($originalFile) {
        $manifestData = array();
        if (strpos($originalFile, '//') !== false) {
            $manifestData['mtime'] = $this->getRemoteMTime($originalFile);
        } else {
            $manifestData['mtime'] = filemtime($originalFile);
        }

        return $manifestData;
    }

    private function getRemoteMTime($url) {
        $h = get_headers($url, 1);
        if (!$h || strpos($h[0], '200') !== false) {
            foreach ($h as $k => $v) {
                if (strtolower(trim($k)) == "last-modified") {
                    return (new DateTime($v))->getTimestamp();
                }
            }
        }

        return 0;
    }

    protected function getManifestKey($fileName) {
        return $fileName . '.manifest';
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
            'mp3',
            'webp',
            'svg'
        );

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($ext, $supported_image)) {
            return true;
        }

        return false;
    }
}