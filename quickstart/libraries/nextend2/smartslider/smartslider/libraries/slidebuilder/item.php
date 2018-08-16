<?php

class N2SmartSliderSlideBuilderItem extends N2SmartSliderSlideBuilderComponent {

    /**
     * @var N2SSPluginItemFactoryAbstract
     */
    protected $item;

    /**
     * N2SmartSliderSlideBuilderItem constructor.
     *
     * @param N2SmartSliderSlideBuilderComponent $container
     * @param string                             $type
     */
    public function __construct($container, $type) {
        $this->item        = N2SmartSliderItemsFactory::getItem($type);
        $this->defaultData = array_merge($this->defaultData, $this->item->getValues());

        $container->add($this);
    }

    public function getData() {
        return array(
            'type'   => $this->item->getType(),
            'values' => parent::getData()
        );
    }

    public function getLabel() {
        return $this->item->getTitle();
    }

    public function getLayerProperties() {
        return $this->item->getLayerProperties();
    }
}
