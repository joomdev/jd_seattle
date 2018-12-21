<?php

N2Loader::import('libraries.plugins.N2SliderWidgetAbstract', 'smartslider');
N2Loader::import('libraries.image.color');

class N2SSPluginWidgetBulletTransition extends N2SSPluginWidgetAbstract {

    protected $name = 'transition';

    private static $key = 'widget-bullet-';

    public function getDefaults() {
        return array(
            'widget-bullet-position-mode'        => 'simple',
            'widget-bullet-position-area'        => 10,
            'widget-bullet-position-offset'      => 10,
            'widget-bullet-action'               => 'click',
            'widget-bullet-style'                => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiNXwqfDV8Knw1fCp8NXwqfHB4IiwiYm94c2hhZG93IjoiMHwqfDB8KnwwfCp8MHwqfDAwMDAwMGZmIiwiYm9yZGVyIjoiMHwqfHNvbGlkfCp8MDAwMDAwZmYiLCJib3JkZXJyYWRpdXMiOiI1MCIsImV4dHJhIjoibWFyZ2luOiA0cHg7In0seyJiYWNrZ3JvdW5kY29sb3IiOiIwMGMxYzRmZiJ9XX0=',
            'widget-bullet-bar'                  => '',
            'widget-bullet-align'                => 'center',
            'widget-bullet-orientation'          => 'auto',
            'widget-bullet-bar-full-size'        => 0,
            'widget-bullet-overlay'              => 0,
            'widget-bullet-thumbnail-show-image' => 0,
            'widget-bullet-thumbnail-width'      => 100,
            'widget-bullet-thumbnail-width'      => 60,
            'widget-bullet-thumbnail-style'      => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwODAiLCJwYWRkaW5nIjoiM3wqfDN8KnwzfCp8M3wqfHB4IiwiYm94c2hhZG93IjoiMHwqfDB8KnwwfCp8MHwqfDAwMDAwMGZmIiwiYm9yZGVyIjoiMHwqfHNvbGlkfCp8MDAwMDAwZmYiLCJib3JkZXJyYWRpdXMiOiIzIiwiZXh0cmEiOiJtYXJnaW46IDVweDsifV19',
            'widget-bullet-thumbnail-side'       => 'before'
        );
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'bullet-transition');

        new N2ElementWidgetPosition($settings, 'widget-bullet-position', n2_('Position'));

        new N2ElementStyle($settings, 'widget-bullet-style', n2_('Dot style'), '', array(
            'previewMode' => 'dot',
            'style2'      => 'sliderwidget-bullet-bar',
            'set'         => 1900,
            'preview'     => '<div class="{styleClassName2}" style="display:inline-block;"><div class="{styleClassName}" style="display: inline-block; vertical-align:top;"></div><div class="{styleClassName} n2-active" style="display: inline-block; vertical-align:top;"></div><div class="{styleClassName}" style="display: inline-block; vertical-align:top;"></div></div>'
        ));

        new N2ElementStyle($settings, 'widget-bullet-bar', n2_('Bar style'), '', array(
            'previewMode' => 'simple',
            'style2'      => 'sliderwidget-bullet-style',
            'set'         => 1900,
            'preview'     => '<div class="{styleClassName}" style="display:inline-block;"><div class="{styleClassName2}" style="display: inline-block; vertical-align:top;"></div><div class="{styleClassName2} n2-active" style="display: inline-block; vertical-align:top;"></div><div class="{styleClassName2}" style="display: inline-block; vertical-align:top;"></div></div>'
        ));
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'transition' . DIRECTORY_SEPARATOR;
    }

    public function getPositions(&$params) {
        $positions                    = array();
        $positions['bullet-position'] = array(
            self::$key . 'position-',
            'bullet'
        );

        return $positions;
    }

    /**
     * @param $slider N2SmartSliderAbstract
     * @param $id
     * @param $params
     *
     * @return string
     */
    public function render($slider, $id, $params) {
        if (count($slider->slides) <= 1) {
            return '';
        }

        $slider->addLess(N2Filesystem::translate(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'transition' . DIRECTORY_SEPARATOR . 'style.n2less'), array(
            "sliderid" => $slider->elementId
        ));
        $slider->features->addInitCallback(N2Filesystem::readFile(N2Filesystem::translate(dirname(__FILE__) . '/transition/bullet.min.js')));
    

        list($displayClass, $displayAttributes) = self::getDisplayAttributes($params, self::$key);

        $bulletStyle = $slider->addStyle($params->get(self::$key . 'style'), 'dot');
        $barStyle    = $slider->addStyle($params->get(self::$key . 'bar'), 'simple');

        list($style, $attributes) = self::getPosition($params, self::$key);
        $attributes['data-offset'] = $params->get(self::$key . 'position-offset', 0);

        $orientation = self::getOrientationByPosition($params->get(self::$key . 'position-mode'), $params->get(self::$key . 'position-area'), $params->get(self::$key . 'orientation'), 'horizontal');


        $parameters = array(
            'overlay'    => ($params->get(self::$key . 'position-mode') != 'simple' || $params->get(self::$key . 'overlay')) ? 1 : 0,
            'area'       => intval($params->get(self::$key . 'position-area')),
            'dotClasses' => $bulletStyle,
            'mode'       => '',
            'action'     => $params->get(self::$key . 'action')
        );

        if ($params->get(self::$key . 'thumbnail-show-image')) {

            $slider->exposeSlideData['thumbnail'] = true;

            $parameters['thumbnail']       = 1;
            $parameters['thumbnailWidth']  = intval($params->get(self::$key . 'thumbnail-width'));
            $parameters['thumbnailHeight'] = intval($params->get(self::$key . 'thumbnail-height'));
            $parameters['thumbnailStyle']  = $slider->addStyle($params->get(self::$key . 'thumbnail-style'), 'simple', '');
            $side                          = $params->get(self::$key . 'thumbnail-side');


            if ($side == 'before') {
                if ($orientation == 'vertical') {
                    $position = 'left';
                } else {
                    $position = 'top';
                }
            } else {
                if ($orientation == 'vertical') {
                    $position = 'right';
                } else {
                    $position = 'bottom';
                }
            }
            $parameters['thumbnailPosition'] = $position;
        }

        $slider->features->addInitCallback('new N2Classes.SmartSliderWidgetBulletTransition(this, ' . json_encode($parameters) . ');');

        $fullSize = intval($params->get(self::$key . 'bar-full-size'));

        return N2Html::tag("div", $displayAttributes + $attributes + array(
                "class" => $displayClass . ' n2-flex n2-ss-control-bullet n2-ss-control-bullet-' . $orientation . ($fullSize ? ' n2-ss-control-bullet-fullsize' : ''),
                "style" => $style
            ), N2HTML::tag("div", array(
            "class" => $barStyle . " nextend-bullet-bar n2-ow n2-bar-justify-content-" . $params->get(self::$key . 'align')
        ), ''));
    }

    public function prepareExport($export, $params) {
        $export->addVisual($params->get(self::$key . 'style'));
        $export->addVisual($params->get(self::$key . 'bar'));
    }

    public function prepareImport($import, $params) {

        $params->set(self::$key . 'style', $import->fixSection($params->get(self::$key . 'style')));
        $params->set(self::$key . 'bar', $import->fixSection($params->get(self::$key . 'bar')));
    }

}

N2SmartSliderWidgets::addWidget('bullet', new N2SSPluginWidgetBulletTransition);