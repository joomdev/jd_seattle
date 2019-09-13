<?php

N2Loader::import('libraries.plugins.N2SliderWidgetAbstract', 'smartslider');

class N2SSPluginWidgetAutoplayImage extends N2SSPluginWidgetAbstract {

    private static $key = 'widget-autoplay-';

    protected $name = 'image';

    public function getDefaults() {
        return array(
            'widget-autoplay-responsive-desktop' => 1,
            'widget-autoplay-responsive-tablet'  => 0.7,
            'widget-autoplay-responsive-mobile'  => 0.5,
            'widget-autoplay-play-image'         => '',
            'widget-autoplay-play-color'         => 'ffffffcc',
            'widget-autoplay-play'               => '$ss$/plugins/widgetautoplay/image/image/play/small-light.svg',
            'widget-autoplay-style'              => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiMTB8KnwxMHwqfDEwfCp8MTB8KnxweCIsImJveHNoYWRvdyI6IjB8KnwwfCp8MHwqfDB8KnwwMDAwMDBmZiIsImJvcmRlciI6IjB8Knxzb2xpZHwqfDAwMDAwMGZmIiwiYm9yZGVycmFkaXVzIjoiMyIsImV4dHJhIjoiIn0seyJiYWNrZ3JvdW5kY29sb3IiOiIwMDAwMDBhYiJ9XX0=',
            'widget-autoplay-position-mode'      => 'simple',
            'widget-autoplay-position-area'      => 4,
            'widget-autoplay-position-offset'    => 15,
            'widget-autoplay-mirror'             => 1,
            'widget-autoplay-pause-image'        => '',
            'widget-autoplay-pause-color'        => 'ffffffcc',
            'widget-autoplay-pause'              => '$ss$/plugins/widgetautoplay/image/image/pause/small-light.svg'
        );
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'widget-autoplay');

        $play = new N2ElementGroup($settings, 'autoplay-play', n2_('Play'));
        new N2ElementImageListFromFolder($play, 'widget-autoplay-play', n2_('Shape'), '', array(
            'folder'     => N2Filesystem::translate($this->getPath() . 'play/'),
            'post'       => 'break',
            'isRequired' => true
        ));
        new N2ElementColor($play, 'widget-autoplay-play-color', n2_('Color'), '', array(
            'alpha' => true
        ));

        new N2ElementStyle($settings, 'widget-autoplay-style', n2_('Style'), '', array(
            'previewMode' => 'button',
            'set'         => 1900,
            'preview'     => '<div class="{styleClassName}" style="display: inline-block;"><img style="display: block;" src="{nextend.imageHelper.fixed($(\'#sliderwidget-autoplay-play-image\').val() || N2Color.colorizeSVG($(\'[data-image="\'+$(\'#sliderwidget-autoplay-play\').val()+\'"]\').attr(\'src\'), $(\'#sliderwidget-autoplay-play-color\').val()));}" /></div>'
        ));

        new N2ElementWidgetPosition($settings, 'widget-autoplay-position', n2_('Position'));
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR;
    }

    public function getPositions(&$params) {
        $positions = array();

        $positions['autoplay-position'] = array(
            self::$key . 'position-',
            'autoplay'
        );

        return $positions;
    }

    public function render($slider, $id, $params) {
        $html = '';

        $play      = $params->get(self::$key . 'play-image');
        $playColor = $params->get(self::$key . 'play-color');
        if (empty($play)) {
            $play = $params->get(self::$key . 'play');
            if ($play == -1) {
                $play = null;
            } else if ($play[0] != '$') {
                $play = N2Uri::pathToUri(dirname(__FILE__) . '/image/play/' . $play);
            }
        }

        if ($params->get(self::$key . 'mirror')) {
            $pause      = str_replace('image/play/', 'image/pause/', $play);
            $pauseColor = $playColor;
        } else {
            $pause      = $params->get(self::$key . 'pause-image');
            $pauseColor = $params->get(self::$key . 'pause-color');
            if (empty($pause)) {
                $pause = $params->get(self::$key . 'pause');
                if ($pause == -1) {
                    $pause = null;
                } else if ($pause[0] != '$') {
                    $pause = N2Uri::pathToUri(dirname(__FILE__) . '/image/pause/' . $pause);
                }
            }
        }

        $ext = pathinfo($play, PATHINFO_EXTENSION);
        if (substr($play, 0, 1) == '$' && $ext == 'svg') {
            list($color, $opacity) = N2Color::colorToSVG($playColor);
            $play = 'data:image/svg+xml;base64,' . n2_base64_encode(str_replace(array(
                    'fill="#FFF"',
                    'opacity="1"'
                ), array(
                    'fill="#' . $color . '"',
                    'opacity="' . $opacity . '"'
                ), N2Filesystem::readFile(N2ImageHelper::fixed($play, true))));
        } else {
            $play = N2ImageHelper::fixed($play);
        }

        $ext = pathinfo($pause, PATHINFO_EXTENSION);
        if (substr($pause, 0, 1) == '$' && $ext == 'svg') {
            list($color, $opacity) = N2Color::colorToSVG($pauseColor);
            $pause = 'data:image/svg+xml;base64,' . n2_base64_encode(str_replace(array(
                    'fill="#FFF"',
                    'opacity="1"'
                ), array(
                    'fill="#' . $color . '"',
                    'opacity="' . $opacity . '"'
                ), N2Filesystem::readFile(N2ImageHelper::fixed($pause, true))));
        } else {
            $pause = N2ImageHelper::fixed($pause);
        }

        if ($play && $pause) {

            $slider->addLess(N2Filesystem::translate(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . 'style.n2less'), array(
                "sliderid" => $slider->elementId
            ));
            $slider->features->addInitCallback(N2Filesystem::readFile(N2Filesystem::translate(dirname(__FILE__) . '/image/autoplay.min.js')));
        

            list($displayClass, $displayAttributes) = self::getDisplayAttributes($params, self::$key);

            $styleClass = $slider->addStyle($params->get(self::$key . 'style'), 'heading');


            $isNormalFlow = self::isNormalFlow($params, self::$key);
            list($style, $attributes) = self::getPosition($params, self::$key);


            $slider->features->addInitCallback('new N2Classes.SmartSliderWidgetAutoplayImage(this, ' . n2_floatval($params->get(self::$key . 'responsive-desktop')) . ', ' . n2_floatval($params->get(self::$key . 'responsive-tablet')) . ', ' . n2_floatval($params->get(self::$key . 'responsive-mobile')) . ');');

            $html = N2Html::tag('div', $displayAttributes + $attributes + array(
                    'class'      => $displayClass . $styleClass . 'nextend-autoplay n2-ow nextend-autoplay-image' . ($isNormalFlow ? '' : ' n2-ib'),
                    'style'      => $style,
                    'role'       => 'button',
                    'aria-label' => n2_('Pause autoplay'),
                    'tabindex'   => '0'
                ), N2Html::image($play, 'Play', N2HTML::addExcludeLazyLoadAttributes(array(
                    'class' => 'nextend-autoplay-play n2-ow'
                ))) . N2Html::image($pause, 'Pause', N2HTML::addExcludeLazyLoadAttributes(array(
                    'class' => 'nextend-autoplay-pause n2-ow'
                ))));
        }

        return $html;
    }

    public function prepareExport($export, $params) {
        $export->addImage($params->get(self::$key . 'play-image', ''));
        $export->addImage($params->get(self::$key . 'pause-image', ''));

        $export->addVisual($params->get(self::$key . 'style'));
    }

    public function prepareImport($import, $params) {

        $params->set(self::$key . 'play-image', $import->fixImage($params->get(self::$key . 'play-image', '')));
        $params->set(self::$key . 'pause-image', $import->fixImage($params->get(self::$key . 'pause-image', '')));

        $params->set(self::$key . 'style', $import->fixSection($params->get(self::$key . 'style', '')));
    }

}

N2SmartSliderWidgets::addWidget('autoplay', new N2SSPluginWidgetAutoplayImage);