<?php

class N2SSSlideComponentMain extends N2SSSlideComponent {

    protected $type = 'main';

    /**
     * @param                    $index
     * @param N2SmartSliderSlide $owner Slide
     * @param                    $group
     * @param array              $data  Layers data
     * @param string             $placementType
     */
    public function __construct($index, $owner, $group, $data, $placementType = 'absolute') {
        $this->data = new N2Data(array());

        if (!$owner->underEdit) {
            $data = N2SSSlideComponent::translateUniqueIdentifier($data, false);
        }

        $this->container = new N2SSLayersContainer($owner, $this, $data, 'absolute');

        $this->container->addContentLayer($owner, $this);
    }

    public function render($isAdmin = false) {
        return $this->renderContainer($isAdmin);
    }
}