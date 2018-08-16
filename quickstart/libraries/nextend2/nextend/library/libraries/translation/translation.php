<?php

class N2TranslationAbstract {

    public static function _($text) {
        return $text;
    }

    public static function getCurrentLocale() {
        return '';
    }
}

N2Loader::import('libraries.translation.translation', 'platform');
if (!class_exists('N2Translation', false)) {
    class N2Translation extends N2TranslationAbstract {

    }
}