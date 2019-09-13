<?php

class N2SmartSliderFeatureFocus {

    /**
     * @var N2SmartSlider
     */
    private $slider;

    private $focusOffsetTop = '';

    private $focusOffsetBottom = '';


    public function __construct($slider) {

        $this->slider = $slider;
        $responsiveHeightOffsetValue = '';
    

        $this->focusOffsetTop    = N2SmartSliderSettings::get('responsive-focus-top', $responsiveHeightOffsetValue);
        $this->focusOffsetBottom = N2SmartSliderSettings::get('responsive-focus-bottom', '');
    }

    public function makeJavaScriptProperties(&$properties) {
        $properties['responsive']['focus'] = array(
            'offsetTop'    => $this->focusOffsetTop,
            'offsetBottom' => $this->focusOffsetBottom
        );

        $params = $this->slider->params;

        if ($params->get('responsive-mode') == 'fullpage') {
            if (!$params->has('responsive-focus') && $params->has('responsiveHeightOffset')) {
                $old = $params->get('responsiveHeightOffset');

                $oldDefault = '';

                if ($old !== $oldDefault) {
                    $params->set('responsive-focus', 1);
                    $params->set('responsive-focus-top', $old);
                }
            }

            if ($params->get('responsive-focus', 0)) {
                $properties['responsive']['focus'] = array(
                    'offsetTop'    => $params->get('responsive-focus-top', ''),
                    'offsetBottom' => $params->get('responsive-focus-bottom', '')
                );
            }
        }
    }
}


