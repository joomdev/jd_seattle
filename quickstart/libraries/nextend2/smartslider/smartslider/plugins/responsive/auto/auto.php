<?php

class N2SSPluginResponsiveAuto extends N2SSPluginSliderResponsive {

    protected $name = 'auto';

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function getLabel() {
        return n2_x('Auto', 'Slider responsive mode');
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'smartslider-responsive-auto');

        $mode = new N2ElementGroup($settings, 'responsive-scale-mode', n2_('Mode'));
        new N2ElementOnoff($mode, 'responsiveScaleDown', n2_('Down scale'), 1);
        new N2ElementOnoff($mode, 'responsiveScaleUp', n2_('Up scale'), 1);


        $sliderHeightLimitation = new N2ElementGroup($settings, 'slider-height-limit', n2_('Slider height'));
        new N2ElementNumber($sliderHeightLimitation, 'responsiveSliderHeightMin', n2_('Min'), 0, array(
            'style' => 'width:40px;',
            'unit'  => 'px'
        ));
        new N2ElementNumber($sliderHeightLimitation, 'responsiveSliderHeightMax', n2_('Max'), 3000, array(
            'style' => 'width:40px;',
            'unit'  => 'px'
        ));


        new N2ElementNumberAutocomplete($settings, 'responsiveSlideWidthMax', n2_('Maximum slide width'), 3000, array(
            'style'  => 'width:40px;',
            'unit'   => 'px',
            'values' => array(
                3000,
                980
            )
        ));

    }

    public function parse($params, $responsive, $features) {
        $responsive->scaleDown = intval($params->get('responsiveScaleDown', 1));
        $responsive->scaleUp   = intval($params->get('responsiveScaleUp', 1));
        if ($responsive->scaleUp) {
            $features->align->align = 'normal';
        }


        $responsive->minimumHeight = intval($params->get('responsiveSliderHeightMin', 0));
        $responsive->maximumHeight = intval($params->get('responsiveSliderHeightMax', 3000));

        $responsive->maximumSlideWidth = intval($params->get('responsiveSlideWidthMax', 3000));
    }
}

N2SSPluginSliderResponsive::addType(new N2SSPluginResponsiveAuto);