<?php

class N2SmartSliderSlideBuilderColumn extends N2SmartSliderSlideBuilderComponent {

    protected $defaultData = array(
        "type"     => 'col',
        "name"     => 'Col',
        "colwidth" => '1/1',
        "layers"   => array()
    );

    /** @var N2SmartSliderSlideBuilderComponent[] */
    private $layers = array();

    /**
     * N2SmartSliderSlideBuilderLayer constructor.
     *
     * @param N2SmartSliderSlideBuilderRow       $container
     * @param                                    $width
     */
    public function __construct($container, $width = '1/1') {

        $this->defaultData['colwidth'] = $width;

        $container->add($this);
    }

    /**
     * @param $layer N2SmartSliderSlideBuilderComponent
     */
    public function add($layer) {
        $this->layers[] = $layer;
    }

    public function getData() {
        $this->data['layers'] = array();
        foreach ($this->layers AS $layer) {
            $this->data['layers'][] = $layer->getData();
        }

        return parent::getData();
    }
}