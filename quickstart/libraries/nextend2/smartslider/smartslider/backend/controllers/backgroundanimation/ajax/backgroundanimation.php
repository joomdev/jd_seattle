<?php
N2Base::getApplication('system')
      ->getApplicationType('backend');

class N2SmartSliderBackendBackgroundAnimationControllerAjax extends N2SystemBackendVisualManagerControllerAjax {

    protected $type = 'backgroundanimation';

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.' . $this->type
        ), 'smartslider');
    }

    public function getModel() {
        return new N2SmartSliderBackgroundAnimationModel();
    }
}