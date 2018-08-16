<?php
N2Loader::import('libraries.link.link');

class N2LinkNextSlide {

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            $attributes['onclick'] = "n2ss.applyActionWithClick(this, 'next'); return false";
        }

        return '#';
    }
}

class N2LinkPreviousSlide {

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            $attributes['onclick'] = "n2ss.applyActionWithClick(this, 'previous'); return false";
        }

        return '#';
    }
}

class N2LinkGoToSlide {

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            $attributes['onclick'] = "n2ss.applyActionWithClick(this, 'slide', " . intval($argument) . "); return false";
        }

        return '#';
    }
}

class N2LinkToSlide {

    public static function parse($argument, &$attributes, $isEditor = false) {


        if (!$isEditor) {
            preg_match('/([0-9]+)(,([0-1]))?/', $argument, $matches);
            if (!isset($matches[3])) {
                $attributes['onclick'] = "n2ss.applyActionWithClick(this, 'slide', " . (intval($matches[1]) - 1) . "); return false";
            } else {
                $attributes['onclick'] = "n2ss.applyActionWithClick(this, 'slide', " . (intval($matches[1]) - 1) . ", " . intval($matches[3]) . "); return false";
            }
        }

        return '#';
    }
}

class N2LinkToSlideID {

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            preg_match('/([0-9]+)(,([0-1]))?/', $argument, $matches);
            if (!isset($matches[3])) {
                $attributes['onclick'] = "n2ss.applyActionWithClick(this, 'slideToID', " . intval($matches[1]) . "); return false";
            } else {
                $attributes['onclick'] = "n2ss.applyActionWithClick(this, 'slideToID', " . intval($matches[1]) . ", " . intval($matches[3]) . "); return false";
            }
        }

        return '#';
    }
}

class N2LinkSlideEvent {

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            $attributes['onclick'] = "n2ss.trigger(this, '" . $argument . "'); return false";
        }

        return '#';
    }
}