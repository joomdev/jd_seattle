<?php


class N2SmartsliderBackendPreviewView extends N2ViewBase
{

    public function _renderSlider($sliderId, $extendSlider = array()) {
        $slider = new N2SmartSliderManager($sliderId, false, array(
            'disableResponsive'     => true,
            'extend'                => $extendSlider,
            'addDummySlidesIfEmpty' => true
        ));
        return $slider->render();
    }

} 