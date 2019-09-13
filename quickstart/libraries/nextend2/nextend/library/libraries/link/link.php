<?php

class N2LinkParser {

    public static function parse($url, &$attributes, $isEditor = false) {
        if ($url == '#' || $isEditor) {
            $attributes['onclick'] = "return false;";
        }

        preg_match('/^([a-zA-Z]+)\[(.*)]$/', $url, $matches);
        if (!empty($matches)) {
            $class = 'N2Link' . $matches[1];
            if (class_exists($class, false)) {
                $url = call_user_func_array(array(
                    $class,
                    'parse'
                ), array(
                    $matches[2],
                    &$attributes,
                    $isEditor
                ));
            }
        } else {
            $url = N2ImageHelper::fixed($url);
        }

        return $url;
    }
}


class N2LinkScrollToAlias {

    public static function parse($argument, &$attributes, $isEditor = false) {

        return N2LinkScrollTo::parse('[data-alias=\"' . $argument . '\"]', $attributes, $isEditor);
    }
}

class N2LinkScrollTo {

    private static function init() {
        static $inited = false;
        if (!$inited) {
            N2JS::addInline('window.n2ScrollSpeed=' . json_encode(intval(N2SmartSliderSettings::get('smooth-scroll-speed', 400))) . ';');
            $inited = true;
        }
    }

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            self::init();
            switch ($argument) {
                case 'top':
                    $onclick = 'n2ss.scroll(event, "top");';
                    break;
                case 'bottom':
                    $onclick = 'n2ss.scroll(event, "bottom");';
                    break;
                case 'beforeSlider':
                    $onclick = 'n2ss.scroll(event, "before", N2Classes.$(this).closest(".n2-ss-slider").addBack());';
                    break;
                case 'afterSlider':
                    $onclick = 'n2ss.scroll(event, "after", N2Classes.$(this).closest(".n2-ss-slider").addBack());';
                    break;
                case 'nextSlider':
                    $onclick = 'n2ss.scroll(event, "next", this, ".n2-ss-slider");';
                    break;
                case 'previousSlider':
                    $onclick = 'n2ss.scroll(event, "previous", this, ".n2-ss-slider");';
                    break;
                default:
                    if (is_numeric($argument)) {
                        $onclick = 'n2ss.scroll(event, "element", "#n2-ss-' . $argument . '");';
                    } else {
                        $onclick = 'n2ss.scroll(event, "element", "' . $argument . '");';
                    }
                    break;
            }
            $attributes['onclick'] = $onclick;
        }

        return '#';
    }
}