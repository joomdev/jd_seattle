<?php
N2Loader::import('libraries.link.link');

class N2LinkNextSlide {

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            $attributes['onclick'] = "n2ss.applyActionWithClick(event, 'next');";
        }

        return '#';
    }
}

class N2LinkPreviousSlide {

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            $attributes['onclick'] = "n2ss.applyActionWithClick(event, 'previous');";
        }

        return '#';
    }
}

class N2LinkGoToSlide {

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            $attributes['onclick'] = "n2ss.applyActionWithClick(event, 'slide', " . intval($argument) . ");";
        }

        return '#';
    }
}

class N2LinkToSlide {

    public static function parse($argument, &$attributes, $isEditor = false) {


        if (!$isEditor) {
            preg_match('/([0-9]+)(,([0-1]))?/', $argument, $matches);
            if (!isset($matches[3])) {
                $attributes['onclick'] = "n2ss.applyActionWithClick(event, 'slide', " . (intval($matches[1]) - 1) . ");";
            } else {
                $attributes['onclick'] = "n2ss.applyActionWithClick(event, 'slide', " . (intval($matches[1]) - 1) . ", " . intval($matches[3]) . ");";
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
                $attributes['onclick'] = "n2ss.applyActionWithClick(event, 'slideToID', " . intval($matches[1]) . ");";
            } else {
                $attributes['onclick'] = "n2ss.applyActionWithClick(event, 'slideToID', " . intval($matches[1]) . ", " . intval($matches[3]) . ");";
            }
        }

        return '#';
    }
}

class N2LinkSlideEvent {

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            $attributes['onclick'] = "n2ss.trigger(this, '" . $argument . "', event);";
        }

        return '#';
    }
}