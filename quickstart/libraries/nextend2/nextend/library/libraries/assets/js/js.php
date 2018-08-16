<?php

class N2JS {

    public static function addFile($pathToFile, $group) {
        N2AssetsManager::$js->addFile($pathToFile, $group);
    }

    public static function addFiles($path, $files, $group) {
        N2AssetsManager::$js->addFiles($path, $files, $group);
    }

    public static function addStaticGroup($file, $group) {
        N2AssetsManager::$js->addStaticGroup($file, $group);
    }

    public static function addCode($code, $group) {
        N2AssetsManager::$js->addCode($code, $group);
    }

    public static function addUrl($url) {
        N2AssetsManager::$js->addUrl($url);
    }

    public static function addFirstCode($code, $unshift = false) {
        N2AssetsManager::$js->addFirstCode($code, $unshift);
    }

    public static function addInline($code, $unshift = false) {
        N2AssetsManager::$js->addInline($code, $unshift);
    }

    public static function addGlobalInline($code, $unshift = false) {
        N2AssetsManager::$js->addGlobalInline($code, $unshift);
    }

    public static function addInlineFile($path, $unshift = false) {
        static $loaded = array();
        if (!isset($loaded[$path])) {
            N2AssetsManager::$js->addInline(N2Filesystem::readFile($path), $unshift);
            $loaded[$path] = 1;
        }
    }

    public static function addGlobalInlineFile($path, $unshift = false) {
        static $loaded = array();
        if (!isset($loaded[$path])) {
            N2AssetsManager::$js->addGlobalInline(N2Filesystem::readFile($path), $unshift);
            $loaded[$path] = 1;
        }
    }

    public static function jQuery($force = false, $overrideJQuerySetting = false) {
        if ($force) {
            if ($overrideJQuerySetting || N2Settings::get('jquery')) {
                N2JS::addStaticGroup(N2LIBRARYASSETS . "/dist/n2-j.min.js", 'n2');
            } else {
                N2JS::addStaticGroup(N2LIBRARYASSETS . "/dist/n2.min.js", 'n2');
            }
        } else if ($overrideJQuerySetting || N2Settings::get('jquery') || N2Platform::$isAdmin) {
            N2JS::addStaticGroup(N2LIBRARYASSETS . "/dist/n2-j.min.js", 'n2');
        } else {
            N2JS::addStaticGroup(N2LIBRARYASSETS . "/dist/n2.min.js", 'n2');
        }
    
    }

    public static function modernizr() {
        self::addFile(N2LIBRARYASSETS . '/js/modernizr/modernizr.js', "nextend-frontend");
    }

} 