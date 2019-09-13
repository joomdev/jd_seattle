<?php

N2Loader::import('libraries.plugins.N2SliderWidgetAbstract', 'smartslider');

class N2SSPluginWidgetArrowImage extends N2SSPluginWidgetAbstract {

    private static $key = 'widget-arrow-';

    protected $name = 'image';

    public function getDefaults() {
        return array(
            'widget-arrow-responsive-desktop'       => 1,
            'widget-arrow-responsive-tablet'        => 0.7,
            'widget-arrow-responsive-mobile'        => 0.5,
            'widget-arrow-previous-image'           => '',
            'widget-arrow-previous'                 => '$ss$/plugins/widgetarrow/image/image/previous/normal.svg',
            'widget-arrow-previous-color'           => 'ffffffcc',
            'widget-arrow-previous-hover'           => 0,
            'widget-arrow-previous-hover-color'     => 'ffffffcc',
            'widget-arrow-style'                    => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiMjB8KnwxMHwqfDIwfCp8MTB8KnxweCIsImJveHNoYWRvdyI6IjB8KnwwfCp8MHwqfDB8KnwwMDAwMDBmZiIsImJvcmRlciI6IjB8Knxzb2xpZHwqfDAwMDAwMGZmIiwiYm9yZGVycmFkaXVzIjoiNSIsImV4dHJhIjoiIn0seyJiYWNrZ3JvdW5kY29sb3IiOiIwMDAwMDBjZiJ9XX0=',
            'widget-arrow-previous-position-mode'   => 'simple',
            'widget-arrow-previous-position-area'   => 6,
            'widget-arrow-previous-position-offset' => 15,
            'widget-arrow-next-position-mode'       => 'simple',
            'widget-arrow-next-position-area'       => 7,
            'widget-arrow-next-position-offset'     => 15,
            'widget-arrow-animation'                => 'fade',
            'widget-arrow-mirror'                   => 1,
            'widget-arrow-next-image'               => '',
            'widget-arrow-next'                     => '$ss$/plugins/widgetarrow/image/image/next/normal.svg',
            'widget-arrow-next-color'               => 'ffffffcc',
            'widget-arrow-next-hover'               => 0,
            'widget-arrow-next-hover-color'         => 'ffffffcc',
            'widget-arrow-previous-alt'             => 'previous arrow',
            'widget-arrow-next-alt'                 => 'next arrow',
            'widget-arrow-base64'                   => 1
        );
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'widget-arrow');

        $previous = new N2ElementGroup($settings, 'arrow-previous', n2_('Previous'));
        new N2ElementImageListFromFolder($previous, 'widget-arrow-previous', n2_('Shape'), '', array(
            'folder' => N2Filesystem::translate($this->getPath() . 'previous/'),
            'post'   => 'break'
        ));
        new N2ElementColor($previous, 'widget-arrow-previous-color', n2_('Color'), '', array(
            'alpha' => true
        ));
        new N2ElementOnOff($previous, 'widget-arrow-previous-hover', n2_('Hover'), 0, array(
            'relatedFields' => array(
                'sliderwidget-arrow-previous-hover-color'
            )
        ));
        new N2ElementColor($previous, 'widget-arrow-previous-hover-color', n2_('Hover color'), '', array(
            'alpha' => true
        ));

        new N2ElementStyle($settings, 'widget-arrow-style', n2_('Style'), '', array(
            'set'         => 1900,
            'previewMode' => 'button',
            'preview'     => '<div><div class="{styleClassName}" style="display: inline-block;"><img style="display: block;" src="{nextend.imageHelper.fixed($(\'#sliderwidget-arrow-previous-image\').val() || N2Color.colorizeSVG($(\'[data-image="\'+$(\'#sliderwidget-arrow-previous\').val()+\'"]\').attr(\'src\'), $(\'#sliderwidget-arrow-previous-color\').val()));}" /></div></div>'
        ));

        new N2ElementWidgetPosition($settings, 'widget-arrow-previous-position', n2_('Previous position'));
        new N2ElementWidgetPosition($settings, 'widget-arrow-next-position', n2_('Next position'));

        $alt_group = new N2ElementGroup($settings, 'widget-arrow-alt', n2_('Alt tags'));
        new N2ElementText($alt_group, 'widget-arrow-previous-alt', n2_('Previous arrow'), 'previous arrow');
        new N2ElementText($alt_group, 'widget-arrow-next-alt', n2_('Next arrow'), 'next arrow');
        new N2ElementOnoff($settings, 'widget-arrow-base64', n2_('Base64 encoding'), 1, array(
            'tip'           => n2_('Base64 encoded arrow images are loading faster and they are colorable. But optimization plugins often have errors in their codes related to them, so if your arrow won\'t load, turn this option off.'),
            'relatedFields' => array(
                'sliderwidget-arrow-next-color',
                'sliderwidget-arrow-next-hover',
                'sliderwidget-arrow-previous-color',
                'sliderwidget-arrow-previous-hover'
            )
        ));
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR;
    }

    public function getPositions(&$params) {
        $positions = array();

        if (self::isRenderable('previous', $params)) {
            $positions['previous-position'] = array(
                self::$key . 'previous-position-',
                'previous'
            );
        }

        if (self::isRenderable('next', $params)) {
            $positions['next-position'] = array(
                self::$key . 'next-position-',
                'next'
            );
        }

        return $positions;
    }

    private static function isRenderable($side, &$params) {
        $arrow = $params->get(self::$key . $side . '-image');
        if (empty($arrow)) {
            $arrow = $params->get(self::$key . $side);
            if ($arrow == -1) {
                $arrow = null;
            }
        }

        return !!$arrow;
    }

    public function render($slider, $id, $params) {
        if (count($slider->slides) <= 1) {
            return '';
        }
        $return = array();

        $previous           = $params->get(self::$key . 'previous-image');
        $previousColor      = $params->get(self::$key . 'previous-color');
        $previousHover      = $params->get(self::$key . 'previous-hover');
        $previousHoverColor = $params->get(self::$key . 'previous-hover-color');
        if (empty($previous)) {
            $previous = $params->get(self::$key . 'previous');

            if ($previous == -1) {
                $previous = null;
            } else if ($previous[0] != '$') {
                $previous = N2Uri::pathToUri(dirname(__FILE__) . '/image/previous/' . $previous);
            }
        }

        if ($params->get(self::$key . 'mirror')) {
            $next           = str_replace('image/previous/', 'image/next/', $previous);
            $nextColor      = $previousColor;
            $nextHover      = $previousHover;
            $nextHoverColor = $previousHoverColor;
        } else {
            $next           = $params->get(self::$key . 'next-image');
            $nextColor      = $params->get(self::$key . 'next-color');
            $nextHover      = $params->get(self::$key . 'next-hover');
            $nextHoverColor = $params->get(self::$key . 'next-hover-color');
            if (empty($next)) {
                $next = $params->get(self::$key . 'next');
                if ($next == -1) {
                    $next = null;
                } else if ($next[0] != '$') {
                    $next = N2Uri::pathToUri(dirname(__FILE__) . '/image/next/' . $next);
                }
            }
        }
        if ($previous || $next) {

            $slider->addLess(N2Filesystem::translate(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . 'style.n2less'), array(
                "sliderid" => $slider->elementId
            ));
            $slider->features->addInitCallback(N2Filesystem::readFile(N2Filesystem::translate(dirname(__FILE__) . '/image/arrow.min.js')));
        

            list($displayClass, $displayAttributes) = self::getDisplayAttributes($params, self::$key);

            $animation = $params->get(self::$key . 'animation');

            if ($animation == 'none' || $animation == 'fade') {
                $styleClass = $slider->addStyle($params->get(self::$key . 'style'), 'heading');
            } else {
                $styleClass = $slider->addStyle($params->get(self::$key . 'style'), 'heading-active');
            }

            if ($previous) {
                $return['previous'] = self::getHTML($id, $params, $animation, 'previous', $previous, $displayClass, $displayAttributes, $styleClass, $previousColor, $previousHover, $previousHoverColor);
            }

            if ($next) {
                $return['next'] = self::getHTML($id, $params, $animation, 'next', $next, $displayClass, $displayAttributes, $styleClass, $nextColor, $nextHover, $nextHoverColor);
            }

            $slider->features->addInitCallback('new N2Classes.SmartSliderWidgetArrowImage(this, ' . n2_floatval($params->get(self::$key . 'responsive-desktop')) . ', ' . n2_floatval($params->get(self::$key . 'responsive-tablet')) . ', ' . n2_floatval($params->get(self::$key . 'responsive-mobile')) . ');');
        }

        return $return;
    }

    private static function getHTML($id, &$params, $animation, $side, $image, $displayClass, $displayAttributes, $styleClass, $color = 'ffffffcc', $hover = 0, $hoverColor = 'ffffffcc') {

        list($style, $attributes) = self::getPosition($params, self::$key . $side . '-');

        $imageHover = null;

        $ext = pathinfo($image, PATHINFO_EXTENSION);
        if (substr($image, 0, 1) == '$' && $ext == 'svg') {
            if ($params->get(self::$key . 'base64', 1)) {
                list($color, $opacity) = N2Color::colorToSVG($color);
                $content = N2Filesystem::readFile(N2ImageHelper::fixed($image, true));
                $image   = 'data:image/svg+xml;base64,' . n2_base64_encode(str_replace(array(
                        'fill="#FFF"',
                        'opacity="1"'
                    ), array(
                        'fill="#' . $color . '"',
                        'opacity="' . $opacity . '"'
                    ), $content));
            } else {
                $image = N2ImageHelper::fixed($image);
            }

            if ($hover) {
                if ($params->get(self::$key . 'base64', 1)) {
                    list($color, $opacity) = N2Color::colorToSVG($hoverColor);
                    $imageHover = 'data:image/svg+xml;base64,' . n2_base64_encode(str_replace(array(
                            'fill="#FFF"',
                            'opacity="1"'
                        ), array(
                            'fill="#' . $color . '"',
                            'opacity="' . $opacity . '"'
                        ), $content));
                } else {
                    $imageHover = N2ImageHelper::fixed($imageHover);
                }
            }
        } else {
            $image = N2ImageHelper::fixed($image);
        }

        $alt = $params->get(self::$key . $side . '-alt', $side . ' arrow');

        if ($imageHover === null) {
            $image = N2Html::image($image, $alt, N2HTML::addExcludeLazyLoadAttributes(array(
                'class' => 'n2-ow'
            )));
        } else {
            $image = N2Html::image($image, $alt, N2HTML::addExcludeLazyLoadAttributes(array(
                    'class' => 'n2-arrow-normal-img n2-ow'
                ))) . N2Html::image($imageHover, $alt, N2HTML::addExcludeLazyLoadAttributes(array(
                    'class' => 'n2-arrow-hover-img n2-ow'
                )));
        }

        $isNormalFlow = self::isNormalFlow($params, self::$key . $side . '-');

        if ($animation == 'none' || $animation == 'fade') {
            return N2Html::tag('div', $displayAttributes + $attributes + array(
                    'id'         => $id . '-arrow-' . $side,
                    'class'      => $displayClass . $styleClass . 'nextend-arrow n2-ow nextend-arrow-' . $side . '  nextend-arrow-animated-' . $animation . ($isNormalFlow ? '' : ' n2-ib'),
                    'style'      => $style,
                    'role'       => 'button',
                    'aria-label' => $alt,
                    'tabindex'   => '0'
                ), $image);
        }


        return N2Html::tag('div', $displayAttributes + $attributes + array(
                'id'         => $id . '-arrow-' . $side,
                'class'      => $displayClass . 'nextend-arrow nextend-arrow-animated n2-ow nextend-arrow-animated-' . $animation . ' nextend-arrow-' . $side . ($isNormalFlow ? '' : ' n2-ib'),
                'style'      => $style,
                'role'       => 'button',
                'aria-label' => $alt,
                'tabindex'   => '0'
            ), N2Html::tag('div', array(
                'class' => $styleClass . ' n2-resize'
            ), $image) . N2Html::tag('div', array(
                'class' => $styleClass . ' n2-active' . ' n2-resize'
            ), $image));
    }

    public function prepareExport($export, $params) {
        $export->addImage($params->get(self::$key . 'previous-image', ''));
        $export->addImage($params->get(self::$key . 'next-image', ''));

        $export->addVisual($params->get(self::$key . 'style'));
    }

    public function prepareImport($import, $params) {

        $params->set(self::$key . 'previous-image', $import->fixImage($params->get(self::$key . 'previous-image', '')));
        $params->set(self::$key . 'next-image', $import->fixImage($params->get(self::$key . 'next-image', '')));

        $params->set(self::$key . 'style', $import->fixSection($params->get(self::$key . 'style', '')));
    }
}


class N2SSPluginWidgetArrowImageSmallRectangle extends N2SSPluginWidgetArrowImage {

    protected $name = 'imageSmallRectangle';

    public function getDefaults() {
        return array_merge(parent::getDefaults(), array(
            'widget-arrow-responsive-desktop' => 0.8,
            'widget-arrow-previous'           => '$ss$/plugins/widgetarrow/image/image/previous/full.svg',
            'widget-arrow-next'               => '$ss$/plugins/widgetarrow/image/image/next/full.svg',
            'widget-arrow-style'              => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiMnwqfDJ8KnwyfCp8MnwqfHB4IiwiYm94c2hhZG93IjoiMHwqfDB8KnwwfCp8MHwqfDAwMDAwMGZmIiwiYm9yZGVyIjoiMHwqfHNvbGlkfCp8MDAwMDAwZmYiLCJib3JkZXJyYWRpdXMiOiIzIiwiZXh0cmEiOiIifSx7ImJhY2tncm91bmRjb2xvciI6IjAxYWRkM2Q5In1dfQ=='
        ));
    }
}

N2SmartSliderWidgets::addWidget('arrow', new N2SSPluginWidgetArrowImageSmallRectangle);


class N2SSPluginWidgetArrowImageEmpty extends N2SSPluginWidgetArrowImage {

    protected $name = 'imageEmpty';

    public function getDefaults() {
        return array_merge(parent::getDefaults(), array(
            'widget-arrow-previous' => '$ss$/plugins/widgetarrow/image/image/previous/thin-horizontal.svg',
            'widget-arrow-next'     => '$ss$/plugins/widgetarrow/image/image/next/thin-horizontal.svg',
            'widget-arrow-style'    => ''
        ));
    }
}

N2SmartSliderWidgets::addWidget('arrow', new N2SSPluginWidgetArrowImageEmpty);

