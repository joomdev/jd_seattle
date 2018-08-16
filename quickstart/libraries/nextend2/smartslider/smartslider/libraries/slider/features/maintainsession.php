<?php

class N2SmartSliderFeatureMaintainSession
{

    private $slider;

    public $isEnabled = 0;

    public function __construct($slider) {

        $this->slider = $slider;

        $this->isEnabled = intval($slider->params->get('maintain-session', 0));
    }

    public function makeJavaScriptProperties(&$properties) {

        $properties['maintainSession'] = $this->isEnabled;
    }
}