<?php

class N2AssetsCache {

    public $outputFileType;

    protected $group, $files, $codes;

    public function getAssetFile($group, &$files = array(), &$codes = array()) {
        $this->group = $group;
        $this->files = $files;
        $this->codes = $codes;

        $cache = new N2CacheManifest($group, true, true);
        $hash  = $this->getHash();

        return $cache->makeCache($group . "." . $this->outputFileType, $hash, array(
            $this,
            'getCachedContent'
        ));
    }

    protected function getHash() {
        $hash = '';
        foreach ($this->files AS $file) {
            $hash .= $this->makeFileHash($file);
        }
        foreach ($this->codes AS $code) {
            $hash .= $code;
        }

        return md5($hash);
    }

    protected function getCacheFileName() {
        $hash = '';
        foreach ($this->files AS $file) {
            $hash .= $this->makeFileHash($file);
        }
        foreach ($this->codes AS $code) {
            $hash .= $code;
        }

        return md5($hash) . "." . $this->outputFileType;
    }

    /**
     * @param N2CacheManifest $cache
     *
     * @return string
     */
    public function getCachedContent($cache) {
        $fileContents = '';
        foreach ($this->files AS $file) {
            $fileContents .= $this->parseFile($cache, N2Filesystem::readFile($file), $file) . "\n";
        }

        foreach ($this->codes AS $code) {
            $fileContents .= $code . "\n";
        }

        return $fileContents;
    }

    protected function makeFileHash($file) {
        return $file . filemtime($file);
    }

    /**
     * @param N2CacheManifest $cache
     * @param                 $content
     * @param                 $originalFilePath
     *
     * @return mixed
     */
    protected function parseFile($cache, $content, $originalFilePath) {
        return $content;
    }

}
