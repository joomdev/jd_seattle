<?php

N2Loader::import('libraries.renderable.layers.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryVimeo extends N2SSPluginItemFactoryAbstract {

    protected $type = 'vimeo';

    protected $priority = 20;

    protected $layerProperties = array(
        "desktopportraitwidth"  => 300,
        "desktopportraitheight" => 180
    );

    protected $class = 'N2SSItemVimeo';

    public function __construct() {
        $this->title = n2_x('Vimeo', 'Slide item');
        $this->group = n2_x('Media', 'Layer group');
    }

    function getValues() {
        return array(
            'vimeourl'     => '75251217',
            'image'        => '$system$/images/placeholder/video.png',
            'autoplay'     => 0,
            'title'        => 1,
            'byline'       => 1,
            'portrait'     => 0,
            'color'        => '00adef',
            'loop'         => 0,
            'start'        => 0,
            'scroll-pause' => 'partly-visible',
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('image', $slide->fill($data->get('image', '')));
        $data->set('vimeourl', $slide->fill($data->get('vimeourl', '')));

        return $data;
    }

    public function prepareExport($export, $data) {
        $export->addImage($data->get('image'));
    }

    public function prepareImport($import, $data) {
        $data->set('image', $import->fixImage($data->get('image')));

        return $data;
    }

    public function prepareSample($data) {
        $data->set('image', N2ImageHelper::fixed($data->get('image')));

        return $data;
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'item-vimeo');

        new N2ElementText($settings, 'vimeourl', n2_('Vimeo url or Video ID'), '', array(
            'style' => 'width:290px;'
        ));

        new N2ElementImage($settings, 'image', n2_('Cover image'), '', array(
            'fixed' => true,
            'style' => 'width:236px;'
        ));

        new N2ElementList($settings, 'scroll-pause', n2_('Pause on scroll'), 'partly-visible', array(
            'options' => array(
                ''               => n2_('Never'),
                'partly-visible' => n2_('When partly visible'),
                'not-visible'    => n2_('When not visible'),
            )
        ));

        $misc = new N2ElementGroup($settings, 'item-vimeo-misc');
        new N2ElementColor($misc, 'color', n2_('Color'));
        new N2ElementList($misc, 'volume', n2_('Volume'), 1, array(
            'options' => array(
                '0'    => n2_('Mute'),
                '0.25' => '25%',
                '0.5'  => '50%',
                '0.75' => '75%',
                '1'    => '100%',
                '-1'   => n2_('Default')
            )
        ));
        new N2ElementOnOff($misc, 'autoplay', n2_('Autoplay'), 0);
        new N2ElementOnOff($misc, 'title', n2_('Title'), 1);
        new N2ElementOnOff($misc, 'byline', n2_('Users byline'), 1);
        new N2ElementOnOff($misc, 'portrait', n2_('Portrait'), 1);

        new N2ElementList($misc, 'quality', n2_('Quality'), '-1', array(
            'options' => array(
                '270p'  => '270p',
                '360p'  => '360p',
                '720p'  => '720p',
                '1080p' => '1080p',
                '-1'    => n2_('Default')
            )
        ));

        new N2ElementNumber($misc, 'start', n2_('Start time'), 0, array(
            'min'  => 0,
            'unit' => 'sec',
            'wide' => 5
        ));
    }

}

N2SmartSliderItemsFactory::addItem(new N2SSPluginItemFactoryVimeo);