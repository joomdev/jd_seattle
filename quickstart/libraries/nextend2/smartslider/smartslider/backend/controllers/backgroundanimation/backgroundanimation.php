<?php

class N2SmartSliderBackendBackgroundAnimationController extends N2SystemBackendVisualManagerController {

    protected $type = 'backgroundanimation';

    public function __construct($path, $appType, $defaultParams) {
        $this->logoText = n2_('Background animation');
        parent::__construct($path, $appType, $defaultParams);
    }

    protected function loadModel() {

        N2Loader::import(array(
            'models.' . $this->type
        ), 'smartslider');
    }

    public function getModel() {
        return new N2SmartSliderBackgroundAnimationModel();
    }

}