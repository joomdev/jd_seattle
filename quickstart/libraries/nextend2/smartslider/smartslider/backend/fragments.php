<?php

class N2SSBackendFragments {

    public static function tutorialFree() {
        $appType = N2Base::getApplication('smartslider')
                         ->getApplicationType('backend');
        include dirname(__FILE__) . '/fragments/tutorial-free.phtml';
    }

    public static function sliderManager($groupID = 0) {
        $appType = N2Base::getApplication('smartslider')
                         ->getApplicationType('backend');
        include dirname(__FILE__) . '/fragments/slidermanager.phtml';
    }

    /**
     * @param N2SmartSlider $sliderObj
     */
    public static function slideManager($sliderObj) {
        $appType = N2Base::getApplication('smartslider')
                         ->getApplicationType('backend');
        include dirname(__FILE__) . '/fragments/slidemanager.phtml';
    }

    /**
     * @param array $sliderRow
     */
    public static function slideManagerByRow($sliderRow) {
        $appType   = N2Base::getApplication('smartslider')
                           ->getApplicationType('backend');
        $sliderObj = new N2SmartSlider($sliderRow['id'], array());
        $sliderObj->loadSliderParams();
        include dirname(__FILE__) . '/fragments/slidemanager.phtml';
    }

    public static function embedSliderManager($groupID, $group, $mode) {
        $appType   = N2Base::getApplication('smartslider')
                           ->getApplicationType('backend');

        include dirname(__FILE__) . '/fragments/embedslidermanager.phtml';
    }
}