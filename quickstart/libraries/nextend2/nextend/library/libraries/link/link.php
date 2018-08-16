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

class N2LinkScrollTo {

    private static function init() {
        static $inited = false;
        if (!$inited) {
            N2JS::addInline('
            window.n2Scroll = {
                to: function(top){
                    $("html, body").animate({ scrollTop: top }, 400);
                },
                top: function(){
                    n2Scroll.to(0);
                },
                bottom: function(){
                    n2Scroll.to($(document).height() - $(window).height());
                },
                before: function(el){
                    n2Scroll.to(el.offset().top - $(window).height());
                },
                after: function(el){
                    n2Scroll.to(el.offset().top + el.height());
                },
                next: function(el, selector){
                    var els = $(selector),
                        nextI = -1;
                    els.each(function(i, slider){
                        if($(el).is(slider) || $.contains(slider, el)){
                            nextI = i + 1;
                            return false;
                        }
                    });
                    if(nextI != -1 && nextI <= els.length){
                        n2Scroll.element(els.eq(nextI));
                    }
                },
                previous: function(el, selector){
                    var els = $(selector),
                        prevI = -1;
                    els.each(function(i, slider){
                        if($(el).is(slider) || $.contains(slider, el)){
                            prevI = i - 1;
                            return false;
                        }
                    });
                    if(prevI >= 0){
                        n2Scroll.element(els.eq(prevI));
                    }
                },
                element: function(selector){
                    var offsetTop = 0;
                    if(typeof n2ScrollOffsetSelector !== "undefined"){
                        offsetTop = $(n2ScrollOffsetSelector).outerHeight();
                    }
                    n2Scroll.to($(selector).offset().top - offsetTop);
                }
            };');
            $inited = true;
        }
    }

    public static function parse($argument, &$attributes, $isEditor = false) {
        if (!$isEditor) {
            self::init();
            switch ($argument) {
                case 'top':
                case 'bottom':
                    $onclick = 'n2Scroll.' . $argument . '();';
                    break;
                case 'beforeSlider':
                    $onclick = 'n2Scroll.before(N2Classes.$(this).closest(".n2-ss-slider").addBack());';
                    break;
                case 'afterSlider':
                    $onclick = 'n2Scroll.after(N2Classes.$(this).closest(".n2-ss-slider").addBack());';
                    break;
                case 'nextSlider':
                    $onclick = 'n2Scroll.next(this, ".n2-ss-slider");';
                    break;
                case 'previousSlider':
                    $onclick = 'n2Scroll.previous(this, ".n2-ss-slider");';
                    break;
                default:
                    $onclick = 'n2Scroll.element("' . $argument . '");';
                    break;
            }
            $attributes['onclick'] = $onclick . "return false;";
        }

        return '#';
    }
}