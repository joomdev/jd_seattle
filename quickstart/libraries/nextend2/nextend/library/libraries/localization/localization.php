<?php

require_once 'pomo/translations.php';
require_once 'pomo/mo.php';

class N2LocalizationAbstract {

    static $l10n = array();

    static $js = array();

    static function load_textdomain($domain, $mofile) {
        if (!is_readable($mofile)) return false;

        $mo = new MO();
        if (!$mo->import_from_file($mofile)) return false;

        if (isset(self::$l10n[$domain])) $mo->merge_with(self::$l10n[$domain]);
        self::$l10n[$domain] = &$mo;

        return true;
    }

    static function load_plugin_textdomain($path, $domain = 'nextend') {
        if (N2Settings::get('force-english-backend')) {
            $locale = 'en_EN';
        } else {
            $locale = N2Localization::getLocale();
        }
        $mofile = $locale . '.mo';
        if ($loaded = N2Localization::load_textdomain($domain, $path . '/languages/' . $mofile)) {
            return $loaded;
        }
    }

    static function addJS($texts) {
        foreach ((array)$texts AS $text) {
            self::$js[$text] = n2_($text);
        }
    }

    static function toJS() {
        if (count(self::$js)) {
            return 'window.nextend.localization = ' . json_encode(self::$js) . ';';
        }

        return '';
    }
}

N2Loader::import('libraries.localization.localization', 'platform');

N2Localization::load_plugin_textdomain(N2LIBRARY);

function n2_get_translations_for_domain($domain) {
    if (!isset(N2Localization::$l10n[$domain])) {
        N2Localization::$l10n[$domain] = new NOOP_Translations;
    }

    return N2Localization::$l10n[$domain];
}

function n2_($text, $domain = 'nextend') {
    $translations = n2_get_translations_for_domain($domain);

    return $translations->translate($text);
}

function n2_e($text, $domain = 'nextend') {
    echo n2_($text, $domain);
}

function n2_n($single, $plural, $number, $domain = 'nextend') {
    $translations = n2_get_translations_for_domain($domain);

    return $translations->translate_plural($single, $plural, $number);
}

function n2_en($single, $plural, $number, $domain = 'nextend') {
    echo n2_n($single, $plural, $number, $domain);
}

function n2_x($text, $context, $domain = 'nextend') {
    $translations = n2_get_translations_for_domain($domain);

    return $translations->translate($text, $context);
}

function n2_ex($text, $context, $domain = 'nextend') {
    echo n2_x($text, $context, $domain);
}