<?php

N2Loader::import('libraries.parse.parse');

abstract class N2SSPluginWidgetAbstract {

    public $ordering = 1;

    protected $name = '';

    public function onList(&$list) {
        $list[$this->name] = $this;
    }

    public function getLabel() {
        return '';
    }

    public function getName() {
        return $this->name;
    }

    public function getSubFormImagePath() {
        return $this->getPath() . $this->name . '.png';
    }

    /**
     * @param N2Form $form
     */
    public function renderFields($form) {

    }


    public function getDefaults() {
        return array();
    }

    public static function getDisplayAttributes(&$params, $key) {
        $class = 'n2-ss-widget ';

        if ($params->get($key . 'display-desktop', 1)) $class .= 'n2-ss-widget-display-desktop ';
        if ($params->get($key . 'display-tablet', 1)) $class .= 'n2-ss-widget-display-tablet ';
        if ($params->get($key . 'display-mobile', 1)) $class .= 'n2-ss-widget-display-mobile ';

        if ($params->get($key . 'display-hover', 0)) $class .= 'n2-ss-widget-display-hover ';

        $attributes = array();

        $excludeSlides = $params->get($key . 'exclude-slides', '');
        if (!empty($excludeSlides)) {
            $attributes['data-exclude-slides'] = $excludeSlides;
        }

        return array(
            $class,
            $attributes
        );
    }

    public function getPositions(&$params) {
        return array();
    }

    public static function getPosition(&$params, $key) {
        $mode = $params->get($key . 'position-mode', 'simple');
        if ($mode == 'above') {
            return array(
                'margin-bottom:' . $params->get($key . 'position-offset', 0) . 'px;',
                array(
                    'data-position' => 'above'
                )
            );
        } else if ($mode == 'below') {
            return array(
                'margin-top:' . $params->get($key . 'position-offset', 0) . 'px;',
                array(
                    'data-position' => 'below'
                )
            );
        }
        $attributes = array();
        $style      = 'position: absolute;';

        $side     = $params->get($key . 'position-horizontal', 'left');
        $position = $params->get($key . 'position-horizontal-position', 0);
        $unit     = $params->get($key . 'position-horizontal-unit', 'px');

        if (!is_numeric($position)) {
            $attributes['data-ss' . $side] = $position;
        } else {
            $style .= $side . ':' . $position . $unit . ';';
        }

        $side     = $params->get($key . 'position-vertical', 'top');
        $position = $params->get($key . 'position-vertical-position', 0);
        $unit     = $params->get($key . 'position-vertical-unit', 'px');

        if (!is_numeric($position)) {
            $attributes['data-ss' . $side] = $position;
        } else {
            $style .= $side . ':' . $position . $unit . ';';
        }

        return array(
            $style,
            $attributes
        );
    }

    public static function getOrientationByPosition($mode, $area, $set = 'auto', $default = 'horizontal') {
        if ($mode == 'advanced') {
            if ($set == 'auto') {
                return $default;
            }

            return $set;
        }
        if ($set != 'auto') {
            return $set;
        }
        switch ($area) {
            case '5':
            case '6':
            case '7':
            case '8':
                return 'vertical';
                break;
        }

        return 'horizontal';
    }

    public function prepareExport($export, $params) {
    }

    public function prepareImport($import, $params) {

    }

    /**
     * @param N2SmartSlider $slider
     * @param               $id
     * @param               $params
     */
    public function render($slider, $id, $params) {

    }

    protected static function isNormalFlow(&$params, $key) {

        $mode = $params->get($key . 'position-mode', 'simple');

        return ($mode == 'above' || $mode == 'below');
    }

}