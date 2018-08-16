<?php

class N2SmartSliderFeatureBlockRightClick
{

    private $slider;

    public $isEnabled = 0;

    public function __construct($slider) {

        $this->slider = $slider;

        $this->isEnabled = intval($slider->params->get('blockrightclick', 0));
    }

    public function makeJavaScriptProperties(&$properties) {

        $properties['blockrightclick'] = $this->isEnabled;
    }
}