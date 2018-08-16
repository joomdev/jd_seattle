<?php

class N2GoogleFonts {

    public static $enabled = false;

    public static function addSubset($subset = 'latin') {
        N2AssetsManager::$googleFonts->addSubset($subset);
    }

    public static function addFont($family, $style = '400') {
        N2AssetsManager::$googleFonts->addFont($family, $style);
    }

    public static function build() {
        if (self::$enabled) {
            N2AssetsManager::$googleFonts->loadFonts();
        }
    }
}