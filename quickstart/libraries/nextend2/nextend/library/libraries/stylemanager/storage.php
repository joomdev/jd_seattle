<?php

class N2StyleStorage {

    private static $sets = array();

    private static $styles = array();

    private static $stylesBySet = array();

    private static $stylesById = array();

    public static function init() {
        N2Pluggable::addAction('systemstyleset', 'N2StyleStorage::styleSet');
        N2Pluggable::addAction('systemstyle', 'N2StyleStorage::styles');
        N2Pluggable::addAction('style', 'N2StyleStorage::style');
    }

    private static function load() {
        static $loaded;
        if (!$loaded) {
            N2Pluggable::doAction('styleStorage', array(
                &self::$sets,
                &self::$styles
            ));

            for ($i = 0; $i < count(self::$styles); $i++) {
                if (!isset(self::$stylesBySet[self::$styles[$i]['referencekey']])) {
                    self::$stylesBySet[self::$styles[$i]['referencekey']] = array();
                }
                self::$stylesBySet[self::$styles[$i]['referencekey']][] = &self::$styles[$i];
                self::$stylesById[self::$styles[$i]['id']]              = &self::$styles[$i];
            }
            $loaded = true;
        }
    }

    public static function styleSet($referenceKey, &$sets) {
        self::load();

        for ($i = count(self::$sets) - 1; $i >= 0; $i--) {
            self::$sets[$i]['system']   = 1;
            self::$sets[$i]['editable'] = 0;
            array_unshift($sets, self::$sets[$i]);
        }

    }

    public static function styles($referenceKey, &$styles) {
        self::load();
        if (isset(self::$stylesBySet[$referenceKey])) {
            $_styles = &self::$stylesBySet[$referenceKey];
            for ($i = count($_styles) - 1; $i >= 0; $i--) {
                $_styles[$i]['system']   = 1;
                $_styles[$i]['editable'] = 0;
                array_unshift($styles, $_styles[$i]);
            }

        }
    }

    public static function style($id, &$style) {
        self::load();
        if (isset(self::$stylesById[$id])) {
            self::$stylesById[$id]['system']   = 1;
            self::$stylesById[$id]['editable'] = 0;
            $style                             = self::$stylesById[$id];
        }
    }
}

N2StyleStorage::init();