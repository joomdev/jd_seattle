<?php


class N2FontStorage {

    private static $sets = array();

    private static $fonts = array();

    private static $fontsBySet = array();

    private static $fontsById = array();

    public static function init() {
        N2Pluggable::addAction('systemfontset', 'N2FontStorage::fontSet');
        N2Pluggable::addAction('systemfont', 'N2FontStorage::fonts');
        N2Pluggable::addAction('font', 'N2FontStorage::font');
    }

    private static function load() {
        static $loaded;
        if (!$loaded) {
            N2Pluggable::doAction('fontStorage', array(
                &self::$sets,
                &self::$fonts
            ));

            for ($i = 0; $i < count(self::$fonts); $i++) {
                if (!isset(self::$fontsBySet[self::$fonts[$i]['referencekey']])) {
                    self::$fontsBySet[self::$fonts[$i]['referencekey']] = array();
                }
                self::$fontsBySet[self::$fonts[$i]['referencekey']][] = &self::$fonts[$i];
                self::$fontsById[self::$fonts[$i]['id']]              = &self::$fonts[$i];
            }
            $loaded = true;
        }
    }

    public static function fontSet($referenceKey, &$sets) {
        self::load();

        for ($i = count(self::$sets) - 1; $i >= 0; $i--) {
            self::$sets[$i]['system']   = 1;
            self::$sets[$i]['editable'] = 0;
            array_unshift($sets, self::$sets[$i]);
        }

    }

    public static function fonts($referenceKey, &$fonts) {
        self::load();
        if (isset(self::$fontsBySet[$referenceKey])) {
            $_fonts = &self::$fontsBySet[$referenceKey];
            for ($i = count($_fonts) - 1; $i >= 0; $i--) {
                $_fonts[$i]['system']   = 1;
                $_fonts[$i]['editable'] = 0;
                array_unshift($fonts, $_fonts[$i]);
            }

        }
    }

    public static function font($id, &$font) {
        self::load();
        if (isset(self::$fontsById[$id])) {
            self::$fontsById[$id]['system']   = 1;
            self::$fontsById[$id]['editable'] = 0;
            $font                             = self::$fontsById[$id];
        }
    }
}

N2FontStorage::init();