<?php

N2Loader::import('libraries.renderable.layers.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryYouTube extends N2SSPluginItemFactoryAbstract {

    protected $type = 'youtube';

    protected $priority = 20;

    protected $layerProperties = array(
        "desktopportraitwidth"  => 300,
        "desktopportraitheight" => 180
    );

    protected $class = 'N2SSItemYouTube';

    public function __construct() {
        $this->title = n2_x('YouTube', 'Slide item');
        $this->group = n2_x('Media', 'Layer group');
    }

    function getValues() {
        return array(
            'code'           => 'qesNtYIBDfs',
            'youtubeurl'     => 'https://www.youtube.com/watch?v=lsq09izc1H4',
            'image'          => '$system$/images/placeholder/video.png',
            'autoplay'       => 0,
            'controls'       => 1,
            'defaultimage'   => 'maxresdefault',
            'related'        => '0',
            'vq'             => 'default',
            'center'         => 0,
            'loop'           => 0,
            'showinfo'       => 1,
            'modestbranding' => 1,
            'reset'          => 0,
            'start'          => '0',
            'playbutton'     => 1,
            'scroll-pause'   => 'partly-visible',
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('image', $slide->fill($data->get('image', '')));
        $data->set('youtubeurl', $slide->fill($data->get('youtubeurl', '')));

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
        $settings = new N2Tab($form, 'item-youtube');

        new N2ElementText($settings, 'youtubeurl', n2_('YouTube url or Video ID'), '', array(
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

        new N2ElementNumber($misc, 'start', n2_('Start time'), 0, array(
            'min'  => 0,
            'unit' => 'sec',
            'wide' => 5
        ));
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

        new N2ElementList($misc, 'theme', n2_('Theme'), '', array(
            'options' => array(
                'light' => n2_('Light'),
                'dark'  => n2_('Dark')
            )
        ));

        new N2ElementList($misc, 'vq', n2_('Quality'), 'default', array(
            'options' => array(
                'small'   => '240p',
                'medium'  => '360p',
                'large'   => '480p',
                'hd720'   => '720p',
                'hd1080'  => '1080p',
                'highres' => 'High res',
                'default' => 'Default'
            )
        ));

        new N2ElementOnOff($misc, 'autoplay', n2_('Autoplay'), 0);
        new N2ElementOnOff($misc, 'controls', n2_('Controls'), 1);
        new N2ElementOnOff($misc, 'center', n2_('Centered'), 0);
        new N2ElementOnOff($misc, 'loop', n2_('Loop'), 0);
        new N2ElementOnOff($misc, 'related', n2_('Related'), 0);


        $playButton = new N2ElementGroup($settings, 'item-vimeo-playbutton', '', array(
            'rowClass' => 'n2-expert'
        ));
        new N2ElementOnOff($playButton, 'playbutton', n2_('Play button'), 1);
        new N2ElementNumber($playButton, 'playbuttonwidth', n2_('Width'), 48, array(
            'unit' => 'px',
            'wide' => 4
        ));
        new N2ElementNumber($playButton, 'playbuttonheight', n2_('Height'), 48, array(
            'unit' => 'px',
            'wide' => 4
        ));

        new N2ElementImage($playButton, 'playbuttonimage', n2_('Image'), '', array(
            'fixed' => true,
            'style' => 'width:236px;'
        ));
    }

}

N2SmartSliderItemsFactory::addItem(new N2SSPluginItemFactoryYouTube);