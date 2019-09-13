<?php

class N2SSSlideComponentContent extends N2SSSlideComponent {

    protected $type = 'content';

    protected $name = 'Content';

    protected $colAttributes = array(
        'class' => 'n2-ss-section-main-content n2-ss-layer-content n2-ow',
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
        parent::__construct($index, $owner, $group, $data, 'content');
        $this->container = new N2SSLayersContainer($owner, $this, $data['layers'], 'normal');
        $this->data->un_set('layers');

        $this->attributes['style'] = '';

        $innerAlign = $this->data->get('desktopportraitinneralign', 'inherit');
        if (!empty($innerAlign)) {
            $this->attributes['data-csstextalign'] = $innerAlign;
        }

        $this->colAttributes['data-verticalalign'] = $this->data->get('verticalalign', 'center');

        $this->colAttributes['style'] .= 'padding:' . $this->spacingToEm($this->data->get('desktopportraitpadding', '10|*|10|*|10|*|10|*|px+')) . ';';

        $this->renderBackground();

        $maxWidth = intval($this->data->get('desktopportraitmaxwidth', 0));
        if ($maxWidth > 0) {
            $this->attributes['style'] .= 'max-width: ' . $maxWidth . 'px;';

            $this->attributes['data-has-maxwidth'] = '1';
        } else {
            $this->attributes['data-has-maxwidth'] = '0';
        }
        $this->createDeviceProperty('maxwidth', '0');

        $this->attributes['data-cssselfalign'] = $this->data->get('desktopportraitselfalign', 'inherit');

        $this->createDeviceProperty('selfalign', 'inherit');


        $this->placement->attributes($this->attributes);

        $this->createDeviceProperty('padding', '10|*|10|*|10|*|10|*|px+');
        $this->createDeviceProperty('inneralign', 'inherit');

    }

    protected function pluginFontSize() {
        $this->attributes['data-adaptivefont'] = $this->data->get('adaptivefont', 1);

        $this->createDeviceProperty('fontsize', 100);
    }

    public function updateRowSpecificProperties($gutter, $width, $isLast) {
        $this->attributes['style'] .= 'width: ' . $width . '%;';

        if (!$isLast) {
            $this->attributes['style'] .= 'margin-right: ' . $gutter . 'px;margin-bottom: ' . $gutter . 'px;';
        }

    }

    public function render($isAdmin) {
        if ($this->isRenderAllowed()) {
            if ($isAdmin || $this->hasBackground || count($this->container->getLayers())) {

                $this->serveLocalStyle();
                if ($isAdmin) {
                    $this->admin();
                }

                $this->prepareHTML();

                $this->attributes['data-hasbackground'] = $this->hasBackground ? '1' : '0';

                $html = N2Html::tag('div', $this->colAttributes, parent::renderContainer($isAdmin));
                $html = $this->renderPlugins($html);

                return N2Html::tag('div', $this->attributes, $html);
            }
        }

        return '';
    }

    protected function addUniqueClass($class) {
        $this->attributes['class']    .= ' ' . $class;
        $this->colAttributes['class'] .= ' ' . $class . '-inner';
    }

    protected function admin() {

        $this->createProperty('verticalalign', 'center');

        $this->createProperty('bgimage', '');
        $this->createProperty('bgimagex', 50);
        $this->createProperty('bgimagey', 50);

        $this->createColorProperty('bgcolor', '00000000');
        $this->createProperty('bgcolorgradient', 'off');
        $this->createColorProperty('bgcolorgradientend', '00000000');
        $this->createColorProperty('bgcolor-hover');
        $this->createProperty('bgcolorgradient-hover');
        $this->createColorProperty('bgcolorgradientend-hover');

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