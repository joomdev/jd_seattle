<?php

class N2AssetsCacheCSS extends N2AssetsCache {

    public $outputFileType = "css";

    private $baseUrl = '', $basePath = '';

    public function getAssetFileFolder() {
        return N2Filesystem::getWebCachePath() . NDS . $this->group . NDS;
    }

    protected function parseFile($cache, $content, $originalFilePath) {

        $this->basePath = dirname($originalFilePath);
        $this->baseUrl  = N2Filesystem::pathToAbsoluteURL($this->basePath);

        return preg_replace_callback('#url\([\'"]?([^"\'\)]+)[\'"]?\)#', array(
            $this,
            'makeAbsoluteUrl'
        ), $content);
    }

    private function makeAbsoluteUrl($matches) {
        if (substr($matches[1], 0, 5) == 'data:') return $matches[0];
        if (substr($matches[1], 0, 4) == 'http') return $matches[0];
        if (substr($matches[1], 0, 2) == '//') return $matches[0];

        $exploded = explode('?', $matches[1]);

        $realPath = realpath($this->basePath . '/' . $exploded[0]);
        if ($realPath === false) {
            return 'url(' . str_replace(array(
                    'http://',
                    'https://'
                ), '//', $this->baseUrl) . '/' . $matches[1] . ')';
        }

        $realPath = N2Filesystem::fixPathSeparator($realPath);

        return 'url(' . N2Uri::pathToUri($realPath, false) . (isset($exploded[1]) ? '?' . $exploded[1] : '') . ')';
    }
}