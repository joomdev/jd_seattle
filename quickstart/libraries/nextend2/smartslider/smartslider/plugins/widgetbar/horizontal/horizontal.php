<?php

N2Loader::import('libraries.plugins.N2SliderWidgetAbstract', 'smartslider');
N2Loader::import('libraries.image.color');

class N2SSPluginWidgetBarHorizontal extends N2SSPluginWidgetAbstract {

    private static $key = 'widget-bar-';

    protected $name = 'horizontal';

    public function getDefaults() {
        return array(
            'widget-bar-position-mode'    => 'simple',
            'widget-bar-position-area'    => 10,
            'widget-bar-position-offset'  => 30,
            'widget-bar-style'            => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiNXwqfDIwfCp8NXwqfDIwfCp8cHgiLCJib3hzaGFkb3ciOiIwfCp8MHwqfDB8KnwwfCp8MDAwMDAwZmYiLCJib3JkZXIiOiIwfCp8c29saWR8KnwwMDAwMDBmZiIsImJvcmRlcnJhZGl1cyI6IjQwIiwiZXh0cmEiOiIifV19',
            'widget-bar-show-title'       => 1,
            'widget-bar-font-title'       => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siY29sb3IiOiJmZmZmZmZmZiIsInNpemUiOiIxNHx8cHgiLCJ0c2hhZG93IjoiMHwqfDB8KnwwfCp8MDAwMDAwYzciLCJhZm9udCI6Ik1vbnRzZXJyYXQiLCJsaW5laGVpZ2h0IjoiMS4zIiwiYm9sZCI6MCwiaXRhbGljIjowLCJ1bmRlcmxpbmUiOjAsImFsaWduIjoibGVmdCIsImV4dHJhIjoidmVydGljYWwtYWxpZ246IG1pZGRsZTsifSx7ImNvbG9yIjoiZmMyODI4ZmYiLCJhZm9udCI6Imdvb2dsZShAaW1wb3J0IHVybChodHRwOi8vZm9udHMuZ29vZ2xlYXBpcy5jb20vY3NzP2ZhbWlseT1SYWxld2F5KTspLEFyaWFsIiwic2l6ZSI6IjI1fHxweCJ9LHt9XX0=',
            'widget-bar-show-description' => 1,
            'widget-bar-font-description' => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siY29sb3IiOiJmZmZmZmZmZiIsInNpemUiOiIxNHx8cHgiLCJ0c2hhZG93IjoiMHwqfDB8KnwwfCp8MDAwMDAwYzciLCJhZm9udCI6Ik1vbnRzZXJyYXQiLCJsaW5laGVpZ2h0IjoiMS4zIiwiYm9sZCI6MCwiaXRhbGljIjoxLCJ1bmRlcmxpbmUiOjAsImFsaWduIjoibGVmdCIsImV4dHJhIjoidmVydGljYWwtYWxpZ246IG1pZGRsZTsifSx7ImNvbG9yIjoiZmMyODI4ZmYiLCJhZm9udCI6Imdvb2dsZShAaW1wb3J0IHVybChodHRwOi8vZm9udHMuZ29vZ2xlYXBpcy5jb20vY3NzP2ZhbWlseT1SYWxld2F5KTspLEFyaWFsIiwic2l6ZSI6IjI1fHxweCJ9LHt9XX0=',
            'widget-bar-width'            => '100%',
            'widget-bar-full-width'       => 0,
            'widget-bar-overlay'          => 0,
            'widget-bar-separator'        => ' - ',
            'widget-bar-align'            => 'center',
            'widget-bar-animate'          => 0
        );
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'bar-horizontal');

        new N2ElementWidgetPosition($settings, 'widget-bar-position', n2_('Position'));

        new N2ElementOnOff($settings, 'widget-bar-animate', n2_('Animate'));

        new N2ElementStyle($settings, 'widget-bar-style', n2_('Style'), '', array(
            'previewMode' => 'simple',
            'font'        => 'sliderwidget-bar-font-title',
            'font2'       => 'sliderwidget-bar-font-description',
            'set'         => 1900,
            'preview'     => '<div style="width:100%;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;" class="{styleClassName}"><span href="#" class="{fontClassName}">Slide title</span><span class="{fontClassName2}">{$(\'#sliderwidget-bar-separator\').val()}Slide description which is longer than the title</span></div>'
        ));

        $title = new N2ElementGroup($settings, 'horizontal-bar-title', n2_('Title'));
        new N2ElementOnOff($title, 'widget-bar-show-title', n2_('Enable'), 0, array(
            'relatedFields' => array(
                'widget-bar-font-title'
            )
        ));
        new N2ElementFont($title, 'widget-bar-font-title', n2_('Font'), '', array(
            'previewMode' => 'simple',
            'set'         => 1100,
            'style'       => 'sliderwidget-bar-style',
            'preview'     => '<div style="width:100%;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;" class="{styleClassName}"><span href="#" class="{fontClassName}">Slide title</span></div>'
        ));


        $description = new N2ElementGroup($settings, 'horizontal-bar-description', n2_('Description'));
        new N2ElementOnOff($description, 'widget-bar-show-description', n2_('Enable'), 0, array(
            'relatedFields' => array(
                'widget-bar-font-description'
            )
        ));
        new N2ElementFont($description, 'widget-bar-font-description', n2_('Font'), '', array(
            'previewMode' => 'simple',
            'set'         => 1100,
            'style'       => 'sliderwidget-bar-style',
            'preview'     => '<div style="width:100%;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;" class="{styleClassName}"><span href="#" class="{fontClassName}">Slide description which is longer than the title</span></div>'
        ));

        $size = new N2ElementGroup($settings, 'horizontal-bar-size', n2_('Size'));
        new N2ElementOnOff($size, 'widget-bar-full-width', n2_('Full width'));


        new N2ElementText($settings, 'widget-bar-separator', n2_('Separator'));

        new N2ElementRadio($settings, 'widget-bar-align', n2_('Align'), '', array(
            'options' => array(
                'left'   => n2_('Left'),
                'center' => n2_('Center'),
                'right'  => n2_('Right')
            )
        ));
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'horizontal' . DIRECTORY_SEPARATOR;
    }

    public function getPositions(&$params) {
        $positions = array();

        $positions['bar-position'] = array(
            self::$key . 'position-',
            'bar'
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

        $slider->addLess(N2Filesystem::translate(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'horizontal' . DIRECTORY_SEPARATOR . 'style.n2less'), array(
            "sliderid" => $slider->elementId
        ));
        $slider->features->addInitCallback(N2Filesystem::readFile(N2Filesystem::translate(dirname(__FILE__) . '/horizontal/bar.min.js')));
    

        list($displayClass, $displayAttributes) = self::getDisplayAttributes($params, self::$key);

        $styleClass = $slider->addStyle($params->get(self::$key . 'style'), 'simple');

        $fontTitle       = $slider->addFont($params->get(self::$key . 'font-title'), 'simple');
        $fontDescription = $slider->addFont($params->get(self::$key . 'font-description'), 'simple');

        list($style, $attributes) = self::getPosition($params, self::$key);
        $attributes['data-offset'] = $params->get(self::$key . 'position-offset');

        $style .= 'text-align: ' . $params->get(self::$key . 'align') . ';';

        $width = $params->get(self::$key . 'width');
        if (is_numeric($width) || substr($width, -1) == '%' || substr($width, -2) == 'px') {
            $style .= 'width:' . $width . ';';
        } else {
            $attributes['data-sswidth'] = $width;
        }

        $innerStyle = '';
        if (!$params->get(self::$key . 'full-width')) {
            $innerStyle = 'display: inline-block;';
        }

        $separator       = $params->get(self::$key . 'separator');
        $showTitle       = intval($params->get(self::$key . 'show-title'));
        $showDescription = intval($params->get(self::$key . 'show-description'));
        $slides          = array();
        for ($i = 0; $i < count($slider->slides); $i++) {

            $html = '';
            if ($showTitle) {
                $title = N2Translation::_($slider->slides[$i]->getTitle());
                if (!empty($title)) {
                    $html .= N2Html::tag('span', array(
                        'class' => $fontTitle . ' n2-ow'
                    ), $title);
                }
            }

            $description = $slider->slides[$i]->getDescription();
            if ($showDescription && !empty($description)) {
                $html .= N2Html::tag('span', array('class' => $fontDescription . ' n2-ow'), (!empty($html) ? $separator : '') . N2SmartSlider::addCMSFunctions(N2Translation::_($description)));
            }

            $slides[$i] = array(
                'html'    => $html,
                'hasLink' => $slider->slides[$i]->hasLink
            );
        }

        $parameters = array(
            'overlay' => $params->get(self::$key . 'position-mode') != 'simple' || $params->get(self::$key . 'overlay'),
            'area'    => intval($params->get(self::$key . 'position-area')),
            'animate' => intval($params->get(self::$key . 'animate'))
        );

        $slider->features->addInitCallback('new N2Classes.SmartSliderWidgetBarHorizontal(this, ' . json_encode($slides) . ', ' . json_encode($parameters) . ');');

        return N2Html::tag("div", $displayAttributes + $attributes + array(
                "class" => $displayClass . "nextend-bar nextend-bar-horizontal n2-ow",
                "style" => $style
            ), N2Html::tag("div", array(
            "class" => $styleClass . ' n2-ow',
            "style" => $innerStyle . ($slides[$slider->firstSlideIndex]['hasLink'] ? 'cursor:pointer;' : '')
        ), $slides[$slider->firstSlideIndex]['html']));
    }

    public function prepareExport($export, $params) {
        $export->addVisual($params->get(self::$key . 'style'));
        $export->addVisual($params->get(self::$key . 'font-title'));
        $export->addVisual($params->get(self::$key . 'font-description'));
    }

    public function prepareImport($import, $params) {

        $params->set(self::$key . 'style', $import->fixSection($params->get(self::$key . 'style', '')));
        $params->set(self::$key . 'font-title', $import->fixSection($params->get(self::$key . 'font-title', '')));
        $params->set(self::$key . 'font-description', $import->fixSection($params->get(self::$key . 'font-description', '')));
    }
}

class N2SSPluginWidgetBarHorizontalFull extends N2SSPluginWidgetBarHorizontal {

    protected $name = 'horizontalFull';

    public function getDefaults() {
        return array_merge(parent::getDefaults(), array(
            'widget-bar-position-offset' => 0,
            'widget-bar-style'           => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiMjB8KnwyMHwqfDIwfCp8MjB8KnxweCIsImJveHNoYWRvdyI6IjB8KnwwfCp8MHwqfDB8KnwwMDAwMDBmZiIsImJvcmRlciI6IjB8Knxzb2xpZHwqfDAwMDAwMGZmIiwiYm9yZGVycmFkaXVzIjoiMCIsImV4dHJhIjoiIn1dfQ==',
            'widget-bar-full-width'      => 1,
            'widget-bar-align'           => 'left'
        ));
    }
}

N2SmartSliderWidgets::addWidget('bar', new N2SSPluginWidgetBarHorizontalFull);