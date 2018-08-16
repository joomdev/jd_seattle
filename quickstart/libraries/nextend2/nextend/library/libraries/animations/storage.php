<?php

class N2AnimationsStorage {

    private static $sets = array();

    private static $animations = array();

    private static $animationsBySet = array();

    private static $animationsById = array();

    public static function init() {
        N2Pluggable::addAction('systemanimationset', 'N2AnimationsStorage::animationSet');
        N2Pluggable::addAction('systemanimation', 'N2AnimationsStorage::animations');
        N2Pluggable::addAction('animation', 'N2AnimationsStorage::animation');
    }

    private static function load() {
        static $loaded;
        if (!$loaded) {
            N2Pluggable::doAction('animationStorage', array(
                &self::$sets,
                &self::$animations
            ));

            for ($i = 0; $i < count(self::$animations); $i++) {
                if (!isset(self::$animationsBySet[self::$animations[$i]['referencekey']])) {
                    self::$animationsBySet[self::$animations[$i]['referencekey']] = array();
                }
                self::$animationsBySet[self::$animations[$i]['referencekey']][] = &self::$animations[$i];
                self::$animationsById[self::$animations[$i]['id']]              = &self::$animations[$i];
            }
            $loaded = true;
        }
    }

    public static function animationSet($referenceKey, &$sets) {
        self::load();

        for ($i = count(self::$sets) - 1; $i >= 0; $i--) {
            self::$sets[$i]['system']   = 1;
            self::$sets[$i]['editable'] = 0;
            array_unshift($sets, self::$sets[$i]);
        }

    }

    public static function animations($referenceKey, &$animations) {
        self::load();
        if (isset(self::$animationsBySet[$referenceKey])) {
            $_animations = &self::$animationsBySet[$referenceKey];
            for ($i = count($_animations) - 1; $i >= 0; $i--) {
                $_animations[$i]['system']   = 1;
                $_animations[$i]['editable'] = 0;
                array_unshift($animations, $_animations[$i]);
            }

        }
    }

    public static function animation($id, &$animation) {
        self::load();
        if (isset(self::$animationsById[$id])) {
            self::$animationsById[$id]['system']   = 1;
            self::$animationsById[$id]['editable'] = 0;
            $animation                             = self::$animationsById[$id];
        }
    }
}

N2AnimationsStorage::init();