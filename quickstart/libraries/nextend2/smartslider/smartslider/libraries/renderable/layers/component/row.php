<?php

class N2SSSlideComponentRow extends N2SSSlideComponent {

    protected $type = 'row';

    protected $rowAttributes = array(
        'class' => 'n2-ss-layer-row ',
        'style' => ''
    );

    protected $rowAttributesInner = array(
        'class' => 'n2-ss-layer-row-inner '
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

    protected $html = '';

    public function __construct($index, $owner, $group, $data, $placementType) {
        parent::__construct($index, $owner, $group, $data, $placementType);
        $this->container = new N2SSLayersContainer($owner, $this, $data['cols'], 'default');
        $this->data->un_set('cols');
        $this->data->un_set('inneralign');

        $columns = $this->container->getLayers();

        $columnCount = count($columns);

        for ($i = 0; $i < $columnCount; $i++) {
            /** @var N2SSSlideComponentCol $col */
            $col = $columns[$i];
            $col->updateRowSpecificProperties($this->data->get('desktopportraitgutter', 20));
        }

        $this->rowAttributes['style'] .= 'padding:' . $this->spacingToEm($this->data->get('desktopportraitpadding', '10|*|10|*|10|*|10|*|px+')) . ';';

        $this->renderBackground();


        $fullWidth = $this->data->get('fullwidth', 1);
        if (!$fullWidth) {
            $this->attributes['data-frontend-fullwidth'] = '0';
        } else {
            $this->attributes['data-frontend-fullwidth'] = '1';
        }

        $stretch = $this->data->get('stretch', 0);
        if ($stretch) {
            $this->attributes['class'] .= ' n2-ss-stretch-layer';
        }

        $borderRadius = intval($this->data->get('borderradius', 0));
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

        $this->placement->attributes($this->attributes);
        $innerAlign = $this->data->get('desktopportraitinneralign', 'inherit');
        if (!empty($innerAlign)) {
            $this->attributes['data-csstextalign'] = $innerAlign;
        }

        $this->createDeviceProperty('padding', '10|*|10|*|10|*|10|*|px+');
        $this->createDeviceProperty('gutter', 20);
        $this->createDeviceProperty('wrapafter', 0);
        $this->createDeviceProperty('inneralign', 'inherit');


        if (!N2SSSlideComponent::$isAdmin) {
            $this->makeLink();
        }
    }

    public function render($isAdmin) {
        if ($this->isRenderAllowed()) {

            $this->serveLocalStyle();
            if ($isAdmin) {
                $this->admin();
            }
            $this->prepareHTML();

            $html = N2Html::tag('div', $this->rowAttributes, N2Html::tag('div', $this->rowAttributesInner, parent::renderContainer($isAdmin)));
            $html = $this->renderPlugins($html);

            return N2Html::tag('div', $this->attributes, $html);
        }

        return '';
    }

    protected function addUniqueClass($class) {
        $this->attributes['class']    .= ' ' . $class;
        $this->rowAttributes['class'] .= ' ' . $class . '-inner';
    }

    private function makeLink() {

        N2Loader::import('libraries.link.link');

        $linkV1 = $this->data->get('link', '');
        if (!empty($linkV1)) {
            list($link, $target) = array_pad((array)N2Parse::parse($linkV1), 2, '');
            $this->data->un_set('link');
            $this->data->set('href', $link);
            $this->data->set('href-target', $target);
        }

        $link = $this->data->get('href');

        if (($link != '#' && !empty($link))) {
            $target = $this->data->get('href-target');

            $link                          = N2LinkParser::parse($this->owner->fill($link), $this->attributes);
            $this->attributes['data-href'] = $link;

            if (!isset($this->attributes['onclick']) && !isset($this->attributes['n2-lightbox'])) {
                if (!empty($target) && $target != '_self') {
                    $this->attributes['data-target'] = $target;
                }
                $this->attributes['onclick'] = "n2ss.openUrl(event);";
            }
            $this->attributes['style'] .= 'cursor:pointer;';

        }
    }

    protected function admin() {

        $linkV1 = $this->data->get('link', '');
        if (!empty($linkV1)) {
            list($link, $target) = array_pad((array)N2Parse::parse($linkV1), 2, '');
            $this->data->un_set('link');
            $this->data->set('href', $link);
            $this->data->set('href-target', $target);
        }

        $this->createProperty('href', '');
        $this->createProperty('href-target', '_self');

        $this->createProperty('bgimage', '');
        $this->createProperty('bgimagex', 50);
        $this->createProperty('bgimagey', 50);

        $this->createColorProperty('bgcolor', '00000000');
        $this->createProperty('bgcolorgradient', 'off');
        $this->createColorProperty('bgcolorgradientend', '00000000');
        $this->createColorProperty('bgcolor-hover');
        $this->createProperty('bgcolorgradient-hover');
        $this->createColorProperty('bgcolorgradientend-hover');

        $this->createProperty('borderradius', 0);
        $this->createProperty('borderradius-hover');

        $this->createProperty('boxshadow', '0|*|0|*|0|*|0|*|00000080');
        $this->createProperty('boxshadow-hover');

        $this->createProperty('fullwidth', '1');
        $this->createProperty('stretch', '0');

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

        N2SmartSliderExport::prepareExportLayer($export, $layer['cols']);
    }

    public static function prepareImport($import, &$layer) {
        if (!empty($layer['bgimage'])) {
            $layer['bgimage'] = $import->fixImage($layer['bgimage']);
        }

        N2SmartSliderImport::prepareImportLayer($import, $layer['cols']);
    }

    public static function prepareSample(&$layer) {
        if (!empty($layer['bgimage'])) {
            $layer['bgimage'] = N2ImageHelper::fixed($layer['bgimage']);
        }

        N2SmartsliderSlidesModel::prepareSample($layer['cols']);
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

        $slide->fillLayers($layer['cols']);
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

}