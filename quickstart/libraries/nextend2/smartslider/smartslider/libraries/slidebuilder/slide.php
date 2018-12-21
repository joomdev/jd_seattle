<?php


class N2SmartSliderSlideBuilder extends N2SmartSliderSlideBuilderComponent {

    protected $data = array(
        'title'                  => '',
        'publishdates'           => '|*|',
        'published'              => 1,
        'first'                  => 0,
        'slide'                  => array(),
        'description'            => '',
        'thumbnail'              => '',
        'ordering'               => 0,
        'generator_id'           => 0,
        "static-slide"           => 0,
        "backgroundColor"        => "ffffff00",
        "backgroundImage"        => "",
        "backgroundImageOpacity" => 100,
        "backgroundAlt"          => "",
        "backgroundTitle"        => "",
        "backgroundMode"         => "default",
        "backgroundVideoMp4"     => "",
        "backgroundVideoOpacity" => 100,
        "backgroundVideoMuted"   => 1,
        "backgroundVideoLoop"    => 1,
        "backgroundVideoMode"    => "fill",
        "link"                   => "|*|_self",
        "slide-duration"         => 0
    );

    /** @var N2SmartSliderSlideBuilderComponent[] */
    private $layers = array();

    /** @var N2SmartSliderSlideBuilderContent */
    public $content;

    public function __construct($properties = array()) {
        foreach ($properties as $k => $v) {
            $this->data[$k] = $v;
        }

        $this->content = new N2SmartSliderSlideBuilderContent($this);
    }

    /**
     * @param $layer N2SmartSliderSlideBuilderComponent
     */
    public function add($layer) {
        array_unshift($this->layers, $layer);
    }

    public function getData() {
        $this->data['slide'] = array();
        foreach ($this->layers AS $layer) {
            $this->data['slide'][] = $layer->getData();
        }

        return parent::getData();
    }

    public function getSlideData() {
        $data                  = $this->getData();
        $data['published']     = '1';
        $data['publishdates']  = '|*|';
        $data['generator_id']  = '';
        $data['record-slides'] = 5;
        $data['slide']         = json_encode($data['slide']);
        $slidesModel           = new N2SmartsliderSlidesModel();

        $row       = $slidesModel->getRowFromPost(94, $data, false);
        $row['id'] = 279;

        return $row;
    }

    public function getLayersData() {
        $data = $this->getData();

        return $data['slide'];
    }
}