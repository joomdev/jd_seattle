<?php

class N2SSPluginResponsiveFullWidth extends N2SSPluginSliderResponsive {

    protected $name = 'fullwidth';

    public $ordering = 2;

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function getLabel() {
        return n2_x('Fullwidth', 'Slider responsive mode');
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'smartslider-responsive-full-width');

        $heightGroup = new N2ElementGroup($settings, 'slider-height-limit', n2_('Slider height'));
        new N2ElementNumber($heightGroup, 'responsiveSliderHeightMin', n2_('Min'), 0, array(
            'unit'  => 'px',
            'style' => 'width:40px;'
        ));
        new N2ElementNumber($heightGroup, 'responsiveSliderHeightMax', n2_('Max'), 3000, array(
            'unit'  => 'px',
            'style' => 'width:40px;'
        ));


        $forceFullGroup = new N2ElementGroup($settings, 'responsive-force-full-width', n2_('Force full width'), array(
            'tip' => n2_('The slider tries to fill the full width of the browser.')
        ));
        new N2ElementOnoff($forceFullGroup, 'responsiveForceFull', n2_('Enable'), 1);
        new N2ElementRadio($forceFullGroup, 'responsiveForceFullOverflowX', n2_('Horizontal mask'), 'body', array(
            'options' => array(
                'body' => 'body',
                'html' => 'html',
                'none' => n2_('None')
            )
        ));


        new N2ElementText($settings, 'responsiveForceFullHorizontalSelector', n2_('Adjust slider width to parent selector'), 'body', array(
            'tip' => n2_('When the jQuery selector of one of the slider\'s parent elements is specified, the slider tries to have the width and fill up that element instead of the window.')
        ));

        new N2ElementRadio($settings, 'responsiveSliderOrientation', n2_('Portrait or Landscape algorithm'), 'width_and_height', array(
            'options' => array(
                'width_and_height' => n2_('Screen width and height'),
                'width'            => n2_('Screen width only')
            )
        ));

        $limitSlideWidthDesktop = new N2ElementGroup($settings, 'responsive-limit-slide-width-desktop', n2_('Limit slide width') . ' - ' . n2_('Desktop'));
        new N2ElementOnoff($limitSlideWidthDesktop, 'responsiveSlideWidth', n2_('Portrait'), 1, array(
            'relatedFields' => array(
                'responsiveSlideWidthMax'
            )
        ));
        new N2ElementNumberAutocomplete($limitSlideWidthDesktop, 'responsiveSlideWidthMax', n2_('Max'), 3000, array(
            'values' => array(
                3000,
                980
            ),
            'unit'   => 'px',
            'style'  => 'width:40px'
        ));
        new N2ElementOnoff($limitSlideWidthDesktop, 'responsiveSlideWidthDesktopLandscape', n2_('Landscape'), 0, array(
            'relatedFields' => array(
                'responsiveSlideWidthMaxDesktopLandscape'
            )
        ));
        new N2ElementNumberAutocomplete($limitSlideWidthDesktop, 'responsiveSlideWidthMaxDesktopLandscape', n2_('Max'), 1600, array(
            'values' => array(
                3000,
                1600
            ),
            'unit'   => 'px',
            'style'  => 'width:40px'
        ));


        $limitSlideWidthTablet = new N2ElementGroup($settings, 'responsive-limit-slide-width-tablet', n2_('Limit slide width') . ' - ' . n2_('Tablet'));
        new N2ElementOnoff($limitSlideWidthTablet, 'responsiveSlideWidthTablet', n2_('Portrait'), 0, array(
            'relatedFields' => array(
                'responsiveSlideWidthMaxTablet'
            )
        ));
        new N2ElementNumberAutocomplete($limitSlideWidthTablet, 'responsiveSlideWidthMaxTablet', n2_('Max'), 3000, array(
            'values' => array(
                3000,
                980
            ),
            'unit'   => 'px',
            'style'  => 'width:40px'
        ));
        new N2ElementOnoff($limitSlideWidthTablet, 'responsiveSlideWidthTabletLandscape', n2_('Landscape'), 0, array(
            'relatedFields' => array(
                'responsiveSlideWidthMaxTabletLandscape'
            )
        ));
        new N2ElementNumberAutocomplete($limitSlideWidthTablet, 'responsiveSlideWidthMaxTabletLandscape', n2_('Max'), 1200, array(
            'values' => array(
                3000,
                1200
            ),
            'unit'   => 'px',
            'style'  => 'width:40px'
        ));


        $limitSlideWidthMobile = new N2ElementGroup($settings, 'responsive-limit-slide-width-mobile', n2_('Limit slide width') . ' - ' . n2_('Mobile'));
        new N2ElementOnoff($limitSlideWidthMobile, 'responsiveSlideWidthMobile', n2_('Portrait'), 0, array(
            'relatedFields' => array(
                'responsiveSlideWidthMaxMobile'
            )
        ));
        new N2ElementNumberAutocomplete($limitSlideWidthMobile, 'responsiveSlideWidthMaxMobile', n2_('Max'), 480, array(
            'values' => array(
                3000,
                480
            ),
            'unit'   => 'px',
            'style'  => 'width:40px'
        ));
        new N2ElementOnoff($limitSlideWidthMobile, 'responsiveSlideWidthMobileLandscape', n2_('Landscape'), 0, array(
            'relatedFields' => array(
                'responsiveSlideWidthMaxMobileLandscape'
            )
        ));
        new N2ElementNumberAutocomplete($limitSlideWidthMobile, 'responsiveSlideWidthMaxMobileLandscape', n2_('Max'), 740, array(
            'values' => array(
                3000,
                740
            ),
            'unit'   => 'px',
            'style'  => 'width:40px'
        ));


        new N2ElementOnoff($settings, 'responsiveSlideWidthConstrainHeight', n2_('Remove vertical margin'), 0);
    }

    public function parse($params, $responsive, $features) {
        $features->align->align = 'normal';

        $responsive->scaleDown = 1;
        $responsive->scaleUp   = 1;

        $responsive->minimumHeight = intval($params->get('responsiveSliderHeightMin', 0));
        $responsive->maximumHeight = intval($params->get('responsiveSliderHeightMax', 3000));


        if ($params->get('responsiveSlideWidth', 1)) {
            $responsive->maximumSlideWidth = intval($params->get('responsiveSlideWidthMax', 3000));
        }

        if ($params->get('responsiveSlideWidthDesktopLandscape', 0)) {
            $responsive->maximumSlideWidthLandscape = intval($params->get('responsiveSlideWidthMaxDesktopLandscape', 1600));
        }

        if ($params->get('responsiveSlideWidthTablet', 0)) {
            $responsive->maximumSlideWidthTablet = intval($params->get('responsiveSlideWidthMaxTablet', 980));
        }

        if ($params->get('responsiveSlideWidthTabletLandscape', 0)) {
            $responsive->maximumSlideWidthTabletLandscape = intval($params->get('responsiveSlideWidthMaxTabletLandscape', 1200));
        }

        if ($params->get('responsiveSlideWidthMobile', 0)) {
            $responsive->maximumSlideWidthMobile = intval($params->get('responsiveSlideWidthMaxMobile', 480));
        }

        if ($params->get('responsiveSlideWidthMobileLandscape', 0)) {
            $responsive->maximumSlideWidthMobileLandscape = intval($params->get('responsiveSlideWidthMaxMobileLandscape', 780));
        }

        $responsive->maximumSlideWidthConstrainHeight = intval($params->get('responsiveSlideWidthConstrainHeight', 0));

        $responsive->orientationMode = $params->get('responsiveSliderOrientation', 'width_and_height');

        $responsive->forceFull = intval($params->get('responsiveForceFull', 1));

        $responsive->forceFullOverflowX = $params->get('responsiveForceFullOverflowX', 'body');

        $responsive->forceFullHorizontalSelector = $params->get('responsiveForceFullHorizontalSelector', 'body');
    }
}

N2SSPluginSliderResponsive::addType(new N2SSPluginResponsiveFullWidth());