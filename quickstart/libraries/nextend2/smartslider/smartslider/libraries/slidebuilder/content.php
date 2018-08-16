<?php

class N2SmartSliderSlideBuilderContent extends N2SmartSliderSlideBuilderComponent {

    protected $defaultData = array(
        "type" => 'content',
        "name" => 'Content'
    );

    /** @var N2SmartSliderSlideBuilderComponent[] */
    private $layers = array();

    /**
     * N2SmartSliderSlideBuilderLayer constructor.
     *
     * @param N2SmartSliderSlideBuilderComponent $container
     */
    public function __construct($container) {

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