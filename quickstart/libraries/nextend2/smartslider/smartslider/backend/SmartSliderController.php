<?php

class N2SmartSliderController extends N2BackendController {

    public function initialize() {
        parent::initialize();

        N2JS::addFirstCode('window.ss2lang = {};');

        N2Loader::import(array(
            'models.License',
            'models.Update'
        ), 'smartslider');
    }

    public function loadSliderManager() {

        N2JS::addInline("new N2Classes.ManageSliders('" . N2Request::getInt('sliderid', 0) . "', '" . $this->appType->router->createUrl(array('slider/create')) . "', " . json_encode(N2SS3::shouldSkipLicenseModal()) . ");");


        N2Localization::addJS(array(
            'Create Slider',
            'Slider name',
            'Slider',
            'Width',
            'Height',
            'Create',
            'Preset',
            'Default',
            'Full width',
            'Full page',
            'Block',
            'Thumbnail - horizontal',
            'Thumbnail - vertical',
            'Bar',
            'Horizontal accordion',
            'Vertical accordion',
            'Showcase',
            'Saved slide',
            'Carousel'
        ));
    }

    public function redirectToSliders() {
        $this->redirect(array("sliders/index"));
    }

}

class N2SmartSliderControllerAjax extends N2BackendControllerAjax {

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.License'
        ), 'smartslider');
    }
}