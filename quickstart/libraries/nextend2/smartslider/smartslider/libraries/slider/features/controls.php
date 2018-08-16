<?php

class N2SmartSliderFeatureControls
{

    private $slider;

    public $scroll = 0;

    public $drag = 0;

    public $touch = 1;

    public $keyboard = 0;

    public $tilt = 0;

    public function __construct($slider) {

        $this->slider = $slider;

        $this->scroll   = intval($slider->params->get('controlsScroll', 0));
        $this->drag     = intval($slider->params->get('controlsDrag', 1));
        $this->touch    = $slider->params->get('controlsTouch', 'horizontal');
        $this->keyboard = intval($slider->params->get('controlsKeyboard', 1));
        $this->tilt     = intval($slider->params->get('controlsTilt', 0));
    }

    public function makeJavaScriptProperties(&$properties) {
        $properties['controls'] = array(
            'scroll'   => $this->scroll,
            'drag'     => count($this->slider->slides) > 1 ? $this->drag : 0,
            'touch'    => $this->touch,
            'keyboard' => $this->keyboard,
            'tilt'     => $this->tilt
        );
    }
}