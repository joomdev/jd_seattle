<?php

class N2SSSlideComponentCol extends N2SSSlideComponent {

    protected $type = 'col';

    protected $colAttributes = array(
        'class' => 'n2-ss-layer-col n2-ss-layer-content',
        'style' => ''
    );

    protected $localStyle = array(
        array(
            "group"    => "normal",
            "selector" => '-inner',
            "css"      => array(
                'transition' => 'transition:all .3s;transition-property:border,background-image,background-color,border-radius,box-shadow;'
            )
        ),
        array(
            "group"    => "hover",
            "selector" => '-inner:HOVER',
            "css"      => array()
        ),
    );

    public function __construct($index, $owner, $group, $data, $placementType) {
        parent::__construct($index, $owner, $group, $data, $placementType);

        $this->container = new N2SSLayersContainer($owner, $this, $data['layers'], 'normal');
        $this->data->un_set('layers');

        $this->attributes['style'] = '';

        $this->colAttributes['data-verticalalign'] = $this->data->get('verticalalign', 'flex-start');

        $innerAlign = $this->data->get('desktopportraitinneralign', 'inherit');
        if (!empty($innerAlign)) {
            $this->attributes['data-csstextalign'] = $innerAlign;
        }

        $this->colAttributes['style'] .= 'padding:' . $this->spacingToEm($this->data->get('desktopportraitpadding', '10|*|10|*|10|*|10|*|px+')) . ';';

        $this->renderBackground();

        $borderRadius = intval($this->data->get('borderradius', '0'));
        $this->addLocalStyle('normal', 'borderradius', $this->getBorderRadiusCSS($borderRadius));

        $borderRadiusHover = intval($this->data->get('borderradius-hover'));
        if (!empty($borderRadiusHover) && $borderRadiusHover != $borderRadius) {
            $this->addLocalStyle('hover', 'borderradius', $this->getBorderRadiusCSS($borderRadiusHover));
        }


        $boxShadow = $this->data->get('boxshadow', '0|*|0|*|0|*|0|*|00000080');
        $this->addLocalStyle('normal', 'boxshadow', $this->getBoxShadowCSS($boxShadow));

        $boxShadowHover = $this->data->get('boxshadow-hover');
        if (!empty($boxShadowHover) && $boxShadowHover != $boxShadow) {
            $this->addLocalStyle('hover', 'boxshadow', $this->getBoxShadowCSS($boxShadowHover));
        }


        $borderWidth = $this->data->get('borderwidth', '1|*|1|*|1|*|1');
        $borderStyle = $this->data->get('borderstyle', 'none');
        $borderColor = $this->data->get('bordercolor', 'ffffffff');

        if ($borderStyle != 'none') {
            $this->addLocalStyle('normal', 'border', $this->getBorderCSS($borderWidth, $borderStyle, $borderColor));
        }

        $borderWidthHover = $this->data->get('borderwidth');
        $borderStyleHover = $this->data->get('borderstyle');
        $borderColorHover = $this->data->get('bordercolor');
        $isHoverDifferent = false;
        if (!empty($borderWidthHover) || $borderWidthHover != $borderWidth) {
            $isHoverDifferent = true;
        }
        if (!empty($borderStyleHover) || $borderStyleHover != $borderStyle) {
            $isHoverDifferent = true;
        }
        if (!empty($borderColorHover) || $borderColorHover != $borderColor) {
            $isHoverDifferent = true;
        }
        if ($isHoverDifferent) {
            if (empty($borderWidthHover)) $borderWidthHover = $borderWidth;
            if (empty($borderStyleHover)) $borderStyleHover = $borderStyle;
            if (empty($borderColorHover)) $borderColorHover = $borderColor;

            $this->addLocalStyle('hover', 'border', $this->getBorderCSS($borderWidthHover, $borderStyleHover, $borderColorHover));
        }

        $maxWidth = intval($this->data->get('desktopportraitmaxwidth', 0));
        if ($maxWidth > 0) {
            $this->attributes['style']             .= 'max-width: ' . $maxWidth . 'px;';
            $this->attributes['data-has-maxwidth'] = '1';
        } else {
            $this->attributes['data-has-maxwidth'] = '0';
        }
        $this->createDeviceProperty('maxwidth', '0');

        $this->placement->attributes($this->attributes);

        $this->createDeviceProperty('padding', '10|*|10|*|10|*|10|*|px+');
        $this->createDeviceProperty('inneralign', 'inherit');

        $this->createDeviceProperty('order');

        $width = explode('/', $this->data->get('colwidth', 1));
        if (count($width) == 2) {
            if ($width[0] == 0 || $width[1] == 0) {
                $width[0] = 1;
                $width[1] = 2;
                $this->data->set('colwidth', '1/2');
            }
            $width = round($width[0] / $width[1] * 100, 1);
        } else {
            $width = 100;
        }

        $this->attributes['data-colwidthpercent'] = $width;
        $this->attributes['style']                .= 'width: ' . $width . '%;';

        if (!N2SSSlideComponent::$isAdmin) {
            $this->makeLink();
        }
    }

    private function getBorderRadiusCSS($borderRadius) {
        if ($borderRadius > 0) {
            return 'border-radius:' . $borderRadius . 'px;';
        }

        return '';
    }

    private function getBoxShadowCSS($boxShadow) {
        $boxShadowArray = explode('|*|', $boxShadow);
        if (count($boxShadowArray) == 5 && ($boxShadowArray[0] != 0 || $boxShadowArray[1] != 0 || $boxShadowArray[2] != 0 || $boxShadowArray[3] != 0) && N2Color::hex2alpha($boxShadowArray[4]) != 0) {
            return 'box-shadow:' . $boxShadowArray[0] . 'px ' . $boxShadowArray[1] . 'px ' . $boxShadowArray[2] . 'px ' . $boxShadowArray[3] . 'px ' . N2Color::colorToRGBA($boxShadowArray[4]) . ';';
        }

        return '';
    }

    private function getBorderCSS($width, $style, $color) {
        if ($style != 'none') {

            $values    = explode('|*|', $width);
            $unit      = 'px';
            $values[4] = '';
            $css       = 'border-width:' . implode($unit . ' ', $values) . ';';

            $css .= 'border-style:' . $style . ';';
            $css .= 'border-color:' . N2Color::colorToRGBA($color) . ';';

            return $css;
        }

        return '';
    }

    public function updateRowSpecificProperties($gutter) {

        $this->attributes['style'] .= 'margin-right: ' . $gutter . 'px;margin-top: ' . $gutter . 'px;';

    }

    private function makeLink() {

        N2Loader::import('libraries.link.link');

        list($link, $target) = array_pad((array)N2Parse::parse($this->data->get('link', '#|*|')), 2, '');

        if (($link != '#' && !empty($link))) {

            $link                          = N2LinkParser::parse($this->owner->fill($link), $this->attributes);
            $this->attributes['data-href'] = $link;

            if (!isset($this->attributes['onclick'])) {
                if (empty($target) || $target == '_self') {
                    $this->attributes['onclick'] = "n2const.setLocation(this.getAttribute('data-href'))";
                } else {
                    $this->attributes['onclick'] = 'var w=window.open();w.opener=null;w.location=this.getAttribute("data-href");';
                }
            }
            $this->attributes['style'] .= 'cursor:pointer;';

        }
    }

    public function render($isAdmin) {
        if ($this->isRenderAllowed()) {

            $this->serveLocalStyle();
            if ($isAdmin) {
                $this->admin();
            }
            $this->prepareHTML();

            $html = N2Html::tag('div', $this->colAttributes, parent::renderContainer($isAdmin));
            $html = $this->renderPlugins($html);

            return N2Html::tag('div', $this->attributes, $html);
        }

        return '';
    }

    protected function addUniqueClass($class) {
        $this->attributes['class']    .= ' ' . $class;
        $this->colAttributes['class'] .= ' ' . $class . '-inner';
    }

    protected function admin() {

        $this->createProperty('colwidth');
        $this->createProperty('link', '#|*|_self');

        $this->createProperty('verticalalign', 'flex-start');

        $this->createProperty('bgimage', '');
        $this->createProperty('bgimagex', '50');
        $this->createProperty('bgimagey', '50');
        $this->createProperty('bgimageparallax', '0');
        $this->createColorProperty('bgcolor', '00000000');
        $this->createProperty('bgcolorgradient', 'off');
        $this->createColorProperty('bgcolorgradientend', '00000000');
        $this->createColorProperty('bgcolor-hover');
        $this->createProperty('bgcolorgradient-hover');
        $this->createColorProperty('bgcolorgradientend-hover');

        $this->createProperty('borderradius', '0');
        $this->createProperty('borderradius-hover');

        $this->createProperty('boxshadow', '0|*|0|*|0|*|0|*|00000080');
        $this->createProperty('boxshadow-hover');

        $this->createProperty('borderwidth', '1');
        $this->createProperty('borderstyle', 'none');
        $this->createProperty('bordercolor', 'FFFFFFFF');
        $this->createProperty('borderwidth-hover');
        $this->createProperty('borderstyle-hover');
        $this->createProperty('bordercolor-hover');

        $this->createProperty('opened', 1);

        parent::admin();
    }


    /**
     * @param N2SmartSliderExport $export
     * @param array               $layer
     */
    public static function prepareExport($export, $layer) {
        if (!empty($layer['bgimage'])) {
            $export->addImage($layer['bgimage']);
        }

        N2SmartSliderExport::prepareExportLayer($export, $layer['layers']);
    }

    public static function prepareImport($import, &$layer) {
        if (!empty($layer['bgimage'])) {
            $layer['bgimage'] = $import->fixImage($layer['bgimage']);
        }

        N2SmartSliderImport::prepareImportLayer($import, $layer['layers']);
    }

    public static function prepareSample(&$layer) {
        if (!empty($layer['bgimage'])) {
            $layer['bgimage'] = N2ImageHelper::fixed($layer['bgimage']);
        }

        N2SmartsliderSlidesModel::prepareSample($layer['layers']);
    }

    /**
     * @param N2SmartSliderSlide $slide
     * @param array              $layer
     */
    public static function getFilled($slide, &$layer) {
        N2SSSlideComponent::getFilled($slide, $layer);

        if (!empty($layer['bgimage'])) {
            $layer['bgimage'] = $slide->fill($layer['bgimage']);
        }

        $slide->fillLayers($layer['layers']);
    }
}