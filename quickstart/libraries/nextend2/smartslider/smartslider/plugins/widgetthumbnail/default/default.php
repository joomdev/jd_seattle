<?php

N2Loader::import('libraries.plugins.N2SliderWidgetAbstract', 'smartslider');
N2Loader::import('libraries.image.color');

class N2SSPluginWidgetThumbnailDefault extends N2SSPluginWidgetAbstract {

    protected $name = 'default';

    private static $key = 'widget-thumbnail-';

    public function getDefaults() {
        return array(
            'widget-thumbnail-minimum-thumbnail-count' => 1,
            'widget-thumbnail-position-mode'           => 'simple',
            'widget-thumbnail-position-area'           => 12,
            'widget-thumbnail-action'                  => 'click',
            'widget-thumbnail-style-bar'               => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMjQyNDI0ZmYiLCJwYWRkaW5nIjoiM3wqfDN8KnwzfCp8M3wqfHB4IiwiYm94c2hhZG93IjoiMHwqfDB8KnwwfCp8MHwqfDAwMDAwMGZmIiwiYm9yZGVyIjoiMHwqfHNvbGlkfCp8MDAwMDAwZmYiLCJib3JkZXJyYWRpdXMiOiIwIiwiZXh0cmEiOiIifV19',
            'widget-thumbnail-style-slides'            => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwMDAiLCJwYWRkaW5nIjoiMHwqfDB8KnwwfCp8MHwqfHB4IiwiYm94c2hhZG93IjoiMHwqfDB8KnwwfCp8MHwqfDAwMDAwMGZmIiwiYm9yZGVyIjoiMHwqfHNvbGlkfCp8ZmZmZmZmMDAiLCJib3JkZXJyYWRpdXMiOiIwIiwiZXh0cmEiOiJvcGFjaXR5OiAwLjQ7XG5tYXJnaW46IDNweDtcbnRyYW5zaXRpb246IGFsbCAwLjRzO1xuYmFja2dyb3VuZC1zaXplOiBjb3ZlcjsifSx7ImJvcmRlciI6IjB8Knxzb2xpZHwqfGZmZmZmZmNjIiwiZXh0cmEiOiJvcGFjaXR5OiAxOyJ9XX0=',
            'widget-thumbnail-arrow'                   => 1,
            'widget-thumbnail-title-style'             => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiM3wqfDEwfCp8M3wqfDEwfCp8cHgiLCJib3hzaGFkb3ciOiIwfCp8MHwqfDB8KnwwfCp8MDAwMDAwZmYiLCJib3JkZXIiOiIwfCp8c29saWR8KnwwMDAwMDBmZiIsImJvcmRlcnJhZGl1cyI6IjAiLCJleHRyYSI6ImJvdHRvbTogMDtcbmxlZnQ6IDA7In1dfQ==',
            'widget-thumbnail-title'                   => 0,
            'widget-thumbnail-title-font'              => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siY29sb3IiOiJmZmZmZmZmZiIsInNpemUiOiIxMnx8cHgiLCJ0c2hhZG93IjoiMHwqfDB8KnwwfCp8MDAwMDAwYWIiLCJhZm9udCI6Ik1vbnRzZXJyYXQiLCJsaW5laGVpZ2h0IjoiMS4yIiwiYm9sZCI6MCwiaXRhbGljIjowLCJ1bmRlcmxpbmUiOjAsImFsaWduIjoibGVmdCJ9LHsiY29sb3IiOiJmYzI4MjhmZiIsImFmb250IjoiZ29vZ2xlKEBpbXBvcnQgdXJsKGh0dHA6Ly9mb250cy5nb29nbGVhcGlzLmNvbS9jc3M/ZmFtaWx5PVJhbGV3YXkpOyksQXJpYWwiLCJzaXplIjoiMjV8fHB4In0se31dfQ==',
            'widget-thumbnail-description'             => 0,
            'widget-thumbnail-description-font'        => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siY29sb3IiOiJmZmZmZmZmZiIsInNpemUiOiIxMnx8cHgiLCJ0c2hhZG93IjoiMHwqfDB8KnwwfCp8MDAwMDAwYWIiLCJhZm9udCI6Ik1vbnRzZXJyYXQiLCJsaW5laGVpZ2h0IjoiMS4zIiwiYm9sZCI6MCwiaXRhbGljIjowLCJ1bmRlcmxpbmUiOjAsImFsaWduIjoibGVmdCJ9LHsiY29sb3IiOiJmYzI4MjhmZiIsImFmb250IjoiZ29vZ2xlKEBpbXBvcnQgdXJsKGh0dHA6Ly9mb250cy5nb29nbGVhcGlzLmNvbS9jc3M/ZmFtaWx5PVJhbGV3YXkpOyksQXJpYWwiLCJzaXplIjoiMjV8fHB4In0se31dfQ==',
            'widget-thumbnail-caption-placement'       => 'overlay',
            'widget-thumbnail-caption-size'            => 100,
            'widget-thumbnail-group'                   => 1,
            'widget-thumbnail-orientation'             => 'auto',
            'widget-thumbnail-size'                    => '100%',
            'widget-thumbnail-overlay'                 => 0,
            'widget-thumbnail-show-image'              => 1,
            'widget-thumbnail-width'                   => 100,
            'widget-thumbnail-height'                  => 60,
            'widget-thumbnail-align-content'           => 'start',
            'widget-thumbnail-invert-group-direction'  => 0
        );
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'widget-thumbnail');

        new N2ElementNumber($settings, 'widget-thumbnail-minimum-thumbnail-count', n2_('Minimum thumbnail count'), '', array(
            'unit'  => n2_('Slides'),
            'style' => 'width:30px;'
        ));

        new N2ElementWidgetPosition($settings, 'widget-thumbnail-position', n2_('Position'));

        new N2ElementRadio($settings, 'widget-thumbnail-align-content', n2_('Align thumbnails'), '', array(
            'options' => array(
                'start'         => n2_('Start'),
                'center'        => n2_('Center'),
                'end'           => n2_('End'),
                'space-between' => n2_('Space between'),
                'space-around'  => n2_('Space around')
            )
        ));

        $style = new N2ElementGroup($settings, 'widget-thumbnail-style', n2_('Style'));

        new N2ElementStyle($style, 'widget-thumbnail-style-bar', n2_('Bar'), '', array(
            'previewMode' => 'simple',
            'set'         => 1900,
            'style2'      => 'sliderwidget-thumbnail-style-slides',
            'preview'     => '
            <div class="{styleClassName}" style="overflow: hidden; width:{$(\'#sliderwidget-thumbnail-width\').val()*2.5}px;">
                <div style="width:200%">
                    <div class="{styleClassName2}" style="display: inline-block; vertical-align:top; width:{$(\'#sliderwidget-thumbnail-width\').val()}px; height: {$(\'#sliderwidget-thumbnail-height\').val()}px; background: url(\'$system$/images/placeholder/imageback.png\');"></div>
                    <div class="{styleClassName2} n2-active" style="display: inline-block; vertical-align:top; width:{$(\'#sliderwidget-thumbnail-width\').val()}px; height: {$(\'#sliderwidget-thumbnail-height\').val()}px; background: url(\'$system$/images/placeholder/image.png\');"></div>
                    <div class="{styleClassName2}" style="display: inline-block; vertical-align:top; width:{$(\'#sliderwidget-thumbnail-width\').val()}px; height: {$(\'#sliderwidget-thumbnail-height\').val()}px; background: url(\'$system$/images/placeholder/imagefront.png\');"></div>
                </div>
            </div>'
        ));

        new N2ElementStyle($style, 'widget-thumbnail-style-slides', n2_('Thumbnail'), '', array(
            'rowClass'    => 'n2-expert',
            'previewMode' => 'dot',
            'set'         => 1900,
            'style2'      => 'sliderwidget-thumbnail-style-bar',
            'preview'     => '
            <div class="{styleClassName2}" style="overflow: hidden;width: 480px;">
                <div style="width:200%">
                <div class="{styleClassName}" style="display: inline-block; vertical-align:top; width:{' . '$(\'#sliderwidget-thumbnail-width\').val()}px; height: ' . '{$(\'#sliderwidget-thumbnail-height\').val()}px; background: url(\'$system$/images/placeholder/imageback.png\');"></div>
                <div class="{styleClassName} n2-active" style="display: inline-block; vertical-align:top; width:' . '{$(\'#sliderwidget-thumbnail-width\').val()}px; height: {' . '$(\'#sliderwidget-thumbnail-height\').val()}px; background: url(\'$system$/images/placeholder/image.png\');"></div>
                <div class="{styleClassName}" style="display: inline-block; vertical-align:top; width:' . '{$(\'#sliderwidget-thumbnail-width\').val()}px; height: {' . '$(\'#sliderwidget-thumbnail-height\').val()}px; background: url(\'$system$/images/placeholder/imagefront.png\');"></div>
                </div>
            </div>'
        ));

        new N2ElementOnOff($settings, 'widget-thumbnail-arrow', n2_('Show arrow'));

        $caption = new N2ElementGroup($settings, 'widget-thumbnail-caption', n2_('Caption'));

        new N2ElementStyle($caption, 'widget-thumbnail-title-style', n2_('Style'), '', array(
            'previewMode' => 'simple',
            'set'         => 1900,
            'post'        => 'break',
            'font'        => 'sliderwidget-thumbnail-title-font',
            'preview'     => '<span class="{styleClassName} {fontClassName}">Slide title</span>'
        ));

        $title = new N2ElementGroup($caption, 'widget-thumbnail-caption-title', '', array(
            'post' => 'break'
        ));
        new N2ElementOnOff($title, 'widget-thumbnail-title', n2_('Title'), '', array(
            'relatedFields' => array(
                'widget-thumbnail-title-font'
            )
        ));
        new N2ElementFont($title, 'widget-thumbnail-title-font', n2_('Font'), '', array(
            'previewMode' => 'simple',
            'style'       => 'sliderwidget-thumbnail-title-style',
            'set'         => 1000,
            'preview'     => '<span class="{styleClassName} {fontClassName}">Slide title</span>'
        ));

        $description = new N2ElementGroup($caption, 'widget-thumbnail-caption-description', '', array(
            'post' => 'break'
        ));
        new N2ElementOnOff($description, 'widget-thumbnail-description', n2_('Description'), '', array(
            'relatedFields' => array(
                'widget-thumbnail-description-font'
            )
        ));
        new N2ElementFont($description, 'widget-thumbnail-description-font', n2_('Font'), '', array(
            'previewMode' => 'simple',
            'style'       => 'sliderwidget-thumbnail-title-style',
            'set'         => 1000,
            'preview'     => '<span class="{styleClassName} {fontClassName}">Slide description with long long text...</span>'
        ));


        $captionSettings = new N2ElementGroup($caption, 'widget-thumbnail-caption-settings');
        new N2ElementRadio($captionSettings, 'widget-thumbnail-caption-placement', n2_('Placement'), '', array(
            'options' => array(
                'before'  => n2_('Before'),
                'overlay' => n2_('Overlay'),
                'after'   => n2_('After')
            )
        ));

        new N2ElementNumber($captionSettings, 'widget-thumbnail-caption-size', n2_('Height (horizontal) or Width (vertical)'), '', array(
            'style' => 'width:40px;',
            'unit'  => 'px'
        ));
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR;
    }

    public function getPositions(&$params) {
        $positions                       = array();
        $positions['thumbnail-position'] = array(
            self::$key . 'position-',
            'thumbnail'
        );

        return $positions;
    }

    static function getStringBetween($string, $start, $end) {
        $string = ' ' . $string;
        $ini    = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    /**
     * @param $slider N2SmartSliderAbstract
     * @param $id
     * @param $params
     *
     * @return string
     */
    public function render($slider, $id, $params) {
        $showImage       = intval($params->get(self::$key . 'show-image'));
        $showTitle       = intval($params->get(self::$key . 'title'));
        $showDescription = intval($params->get(self::$key . 'description'));

        if (!$showImage && !$showTitle && !$showDescription) {
            // Nothing to show
            return '';
        }
        $slider->features->addInitCallback(N2Filesystem::readFile(N2Filesystem::translate(dirname(__FILE__) . '/default/thumbnail.min.js')));
    

        $slider->addLess(N2Filesystem::translate(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'style.n2less'), array(
            "sliderid" => $slider->elementId
        ));

        list($displayClass, $displayAttributes) = self::getDisplayAttributes($params, self::$key);
        list($style, $attributes) = self::getPosition($params, self::$key);
        $attributes['data-offset'] = $params->get(self::$key . 'position-offset', 0);

        $barStyle   = $slider->addStyle($params->get(self::$key . 'style-bar'), 'simple');
        $slideStyle = $slider->addStyle($params->get(self::$key . 'style-slides'), 'dot');

        $width  = intval($slider->params->get(self::$key . 'width', 160));
        $height = intval($slider->params->get(self::$key . 'height', 100));


        $captionPlacement = $slider->params->get(self::$key . 'caption-placement', 'overlay');
        if (!$showImage) {
            $captionPlacement = 'before';
        }

        if (!$showTitle && !$showDescription) {
            $captionPlacement = 'overlay';
        }

        $captionSize = intval($slider->params->get(self::$key . 'caption-size', 100));


        $showCaption = $showTitle || $showDescription;

        if ($showCaption) {
            $captionStyle = $slider->addStyle($params->get(self::$key . 'title-style'), 'simple');

            if ($showTitle) {
                $titleFont = $slider->addFont($params->get(self::$key . 'title-font'), 'simple');
            }
            if ($showDescription) {
                $descriptionFont = $slider->addFont($params->get(self::$key . 'description-font'), 'simple');
            }
        }

        $group = max(1, intval($params->get(self::$key . 'group')));

        $orientation = $params->get(self::$key . 'orientation');
        $orientation = self::getOrientationByPosition($params->get(self::$key . 'position-mode'), $params->get(self::$key . 'position-area'), $orientation, 'vertical');

        $captionClass      = 'n2-caption-' . $captionPlacement;
        $captionExtraStyle = '';
        switch ($captionPlacement) {
            case 'before':
            case 'after':
                switch ($orientation) {
                    case 'vertical':
                        if (!$showImage) {
                            $width = 0;
                        }
                        $containerStyle    = "width: " . ($width + $captionSize) . "px; height: {$height}px;";
                        $captionExtraStyle .= "width: {$captionSize}px";
                        break;
                    default:
                        if (!$showImage) {
                            $height = 0;
                        }
                        $containerStyle    = "width: {$width}px; height: " . ($height + $captionSize) . "px;";
                        $captionExtraStyle .= "height: {$captionSize}px";
                }
                break;
            default:
                $containerStyle = "width: {$width}px; height: {$height}px;";
                $captionSize    = 0;
        }

        $image = '';
        $rows  = array();
        $i     = 0;

        $thumbnailCSS   = array(
            'background-size',
            'background-repeat',
            'background-position'
        );
        $thumbnailStyle = json_decode(n2_base64_decode($params->get('widget-thumbnail-style-slides')));
        if (!empty($thumbnailStyle) && !empty($thumbnailStyle->data[0]->extra)) {
            $extraCSS      = $thumbnailStyle->data[0]->extra;
            $thumbnailCode = '';
            foreach ($thumbnailCSS AS $css) {
                $currentCode = self::getStringBetween($extraCSS, $css . ':', ';');
                if (!empty($currentCode)) {
                    $thumbnailCode .= $css . ':' . $currentCode . ';';
                }
            }
        } else {
            $thumbnailCode = '';
        }

        $slides = '';
        foreach ($slider->slides AS $slide) {
            $active = '';
            if ($slider->firstSlideIndex == $i) {
                $active = 'n2-active ';
            }
            if ($orientation == 'horizontal') {
                $row = $i % $group;
            } else {
                $row = intval($i / $group);
            }
            if (!isset($rows[$row])) {
                $rows[$row] = array();
            }

            if ($showImage) {
                $imgStyle = "width: {$width}px; height: {$height}px;";
                $image    = N2Html::tag('div', array(
                    'class' => 'n2-ss-thumb-image n2-ow',
                    'style' => "background-image: URL('" . $slider->features->optimize->optimizeThumbnail($slide->getThumbnail()) . "');" . $imgStyle . $thumbnailCode
                ), $slide->getThumbnailTypeHTML());
            }

            $inner = '';

            if ($showCaption) {
                $html = '';
                if ($showTitle) {
                    $html .= N2Html::tag('div', array(
                        'class' => $titleFont . ' n2-ow'
                    ), $slide->getTitle());
                }
                $description = $slide->getDescription();
                if ($showDescription && !empty($description)) {
                    $html .= N2Html::tag('div', array(
                        'class' => $descriptionFont . ' n2-ow'
                    ), N2SmartSlider::addCMSFunctions(N2Translation::_($description)));
                }

                $inner = N2Html::tag('div', array(
                    'class' => $captionStyle . 'n2-ss-caption ' . $captionClass . ' n2-ow',
                    'style' => $captionExtraStyle
                ), $html);
            }
            switch ($captionPlacement) {
                case 'before':
                    $inner .= $image;
                    break;
                case 'after':
                default:
                    $inner = $image . $inner;
            }

            $slides .= N2Html::tag('div', array(
                'class'    => $slideStyle . $active . ' n2-ow',
                'style'    => $containerStyle,
                'tabindex' => '0'
            ), $inner);
            $i++;
        }

        $parameters = array(
            'overlay'               => $params->get(self::$key . 'position-mode') != 'simple' || $params->get(self::$key . 'overlay'),
            'area'                  => intval($params->get(self::$key . 'position-area')),
            'orientation'           => $orientation,
            'group'                 => $group,
            'action'                => $params->get(self::$key . 'action'),
            'captionSize'           => intval($captionSize),
            'minimumThumbnailCount' => max(1, intval($params->get(self::$key . 'minimum-thumbnail-count'))) + 0.5,
            'invertGroupDirection'  => intval($params->get('widget-thumbnail-invert-group-direction', 0))
        );

        $slider->features->addInitCallback('new N2Classes.SmartSliderWidgetThumbnailDefault(this, ' . json_encode($parameters) . ');');

        $size = $params->get(self::$key . 'size');
        if ($orientation == 'horizontal') {
            if (is_numeric($size) || substr($size, -1) == '%' || substr($size, -2) == 'px') {
                $style .= 'width:' . $size . ';';
            } else {
                $attributes['data-sswidth'] = $size;
            }
        } else {
            if (is_numeric($size) || substr($size, -1) == '%' || substr($size, -2) == 'px') {
                $style .= 'height:' . $size . ';';
            } else {
                $attributes['data-ssheight'] = $size;
            }
        }

        $previous  = $next = '';
        $showArrow = intval($slider->params->get(self::$key . 'arrow', 1));
        if ($showArrow) {
            $previous = N2Html::image('data:image/svg+xml;base64,' . n2_base64_encode(N2Filesystem::readFile(N2ImageHelper::fixed('$ss$/plugins/widgetthumbnail/default/default/thumbnail-up-arrow.svg', true))), 'Arrow', array(
                'class' => 'nextend-thumbnail-button nextend-thumbnail-previous n2-ow'
            ));
            $next     = N2Html::image('data:image/svg+xml;base64,' . n2_base64_encode(N2Filesystem::readFile(N2ImageHelper::fixed('$ss$/plugins/widgetthumbnail/default/default/thumbnail-down-arrow.svg', true))), 'Arrow', array(
                'class' => 'nextend-thumbnail-button nextend-thumbnail-next n2-ow n2-active'
            ));
        }

        if ($params->get(self::$key . 'position-mode') == 'simple' && $orientation == 'vertical') {
            $area = $params->get(self::$key . 'position-area');
            switch ($area) {
                case '5':
                case '6':
                case '7':
                case '8':
                    $attributes['data-sstop'] = '0';
                    break;
            }
        }


        return N2Html::tag('div', $displayAttributes + $attributes + array(
                'class' => $displayClass . 'nextend-thumbnail nextend-thumbnail-default n2-ow nextend-thumbnail-' . $orientation,
                'style' => $style
            ), $previous . $next . N2Html::tag('div', array(
                'class' => 'nextend-thumbnail-inner n2-ow'
            ), N2Html::tag('div', array(
                'class' => $barStyle . 'nextend-thumbnail-scroller n2-ow n2-align-content-' . $params->get('widget-thumbnail-align-content') . ($params->get('widget-thumbnail-invert-group-direction', 0) ? ' nextend-thumbnail-invert-group-dir' : ''),
            ), $slides)));
    }

    public function prepareExport($export, $params) {

        $export->addVisual($params->get(self::$key . 'style-bar'));
        $export->addVisual($params->get(self::$key . 'style-slides'));
        $export->addVisual($params->get(self::$key . 'title-style'));

        $export->addVisual($params->get(self::$key . 'title-font'));
        $export->addVisual($params->get(self::$key . 'description-font'));
    }

    public function prepareImport($import, $params) {

        $params->set(self::$key . 'style-bar', $import->fixSection($params->get(self::$key . 'style-bar', '')));
        $params->set(self::$key . 'style-slides', $import->fixSection($params->get(self::$key . 'style-slides', '')));
        $params->set(self::$key . 'title-style', $import->fixSection($params->get(self::$key . 'title-style', '')));

        $params->set(self::$key . 'title-font', $import->fixSection($params->get(self::$key . 'title-font', '')));
        $params->set(self::$key . 'description-font', $import->fixSection($params->get(self::$key . 'description-font', '')));
    }
}

N2SmartSliderWidgets::addWidget('thumbnail', new N2SSPluginWidgetThumbnailDefault);