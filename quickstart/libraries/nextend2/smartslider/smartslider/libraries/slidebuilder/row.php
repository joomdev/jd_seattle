<?php

class N2SmartSliderSlideBuilderRow extends N2SmartSliderSlideBuilderComponent {

    protected $defaultData = array(
        "type" => 'row',
        "name" => 'Row',
        "cols" => array()
    );

    /** @var N2SmartSliderSlideBuilderColumn[] */
    private $cols = array();

    /**
     * N2SmartSliderSlideBuilderLayer constructor.
     *
     * @param N2SmartSliderSlideBuilderComponent $container
     */
    public function __construct($container) {

        $container->add($this);
    }

    /**
     * @param $layer N2SmartSliderSlideBuilderColumn
     */
    public function add($layer) {
        $this->cols[] = $layer;
    }

    public function getData() {
        $this->data['cols'] = array();
        foreach ($this->cols AS $layer) {
            $this->data['cols'][] = $layer->getData();
        }

        return parent::getData();
    }
}