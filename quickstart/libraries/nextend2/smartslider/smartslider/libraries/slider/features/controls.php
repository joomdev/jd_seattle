<?php

class N2SmartSliderFeatureControls {

    private $slider;

    private $mousewheel = 0;

    public $drag = 0;

    public $touch = 1;

    public $keyboard = 0;

    public $blockCarouselInteraction = 1;

    public function __construct($slider) {

        $this->slider = $slider;

        $this->mousewheel = intval($slider->params->get('controlsScroll', 0));
        $this->touch      = $slider->params->get('controlsTouch', 'horizontal');
        $this->keyboard   = intval($slider->params->get('controlsKeyboard', 1));
    }

    public function makeJavaScriptProperties(&$properties) {
        $properties['controls'] = array(
            'mousewheel'               => $this->mousewheel,
            'touch'                    => $this->touch,
            'keyboard'                 => $this->keyboard,
            'blockCarouselInteraction' => $this->blockCarouselInteraction
        );
    }
}