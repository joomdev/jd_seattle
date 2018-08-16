<?php

class N2SSPluginTypeSimple extends N2SSPluginSliderType {

    protected $name = 'simple';

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function getLabel() {
        return n2_x('Simple', 'Slider type');
    }

    public function renderFields($form) {
        $animationSettings = new N2Tab($form, 'simpledefaultslidertypeanimation', n2_('Simple slider type') . ' - ' . n2_('Animation'));

        new N2ElementRadio($animationSettings, 'animation', n2_('Main animation'), 'horizontal', array(
            'options' => array(
                'no'                  => n2_('No animation'),
                'fade'                => n2_('Fade'),
                'crossfade'           => n2_('Crossfade'),
                'horizontal'          => n2_('Horizontal'),
                'vertical'            => n2_('Vertical'),
                'horizontal-reversed' => n2_('Horizontal - reversed'),
                'vertical-reversed'   => n2_('Vertical - reversed')
            )
        ));

        $mainanimationGroup = new N2ElementGroup($animationSettings, 'slider-main-animation', n2_('Main animation properties'));

        new N2ElementNumberAutocomplete($mainanimationGroup, 'animation-duration', n2_('Duration'), 800, array(
            'min'    => 0,
            'values' => array(
                800,
                1500,
                2000
            ),
            'unit'   => 'ms',
            'style'  => 'width:35px;'
        ));

        $backgroundAnimationGroup = new N2ElementGroup($animationSettings, 'slider-background-animation', n2_('Background animation'));
        new N2ElementBackgroundAnimation($backgroundAnimationGroup, 'background-animation', n2_('Animation(s)'), '', array(
            'relatedFields' => array(
                'background-animation-color',
                'background-animation-speed',
                'animation-shifted-background-animation'
            )
        ));
        new N2ElementHidden($backgroundAnimationGroup, 'background-animation-color', '', '333333ff');

        new N2ElementList($backgroundAnimationGroup, 'background-animation-speed', n2_('Speed'), 'normal', array(
            'options' => array(
                'superSlow10' => n2_('Super slow') . ' 10x',
                'superSlow'   => n2_('Super slow') . ' 3x',
                'slow'        => n2_('Slow') . ' 1.5x',
                'normal'      => n2_('Normal') . ' 1x',
                'fast'        => n2_('Fast') . ' 0.75x.',
                'superFast'   => n2_('Super fast') . ' 0.5x'
            )
        ));
    }

    public function renderSlideFields($form) {

        $_simpleAnimation = new N2TabGroupped($form, 'simple-animation', false);
        $simpleAnimation  = new N2Tab($_simpleAnimation, 'simple-animation-tab');

        $backgroundAnimationGroup = new N2ElementGroup($simpleAnimation, 'backgroundanimation', n2_('Background animation'));
        new N2ElementBackgroundAnimation($backgroundAnimationGroup, 'background-animation', n2_('Animation(s)'), '', array(
            'relatedFields' => array(
                'background-animation-speed'
            )
        ));

        new N2ElementList($backgroundAnimationGroup, 'background-animation-speed', n2_('Speed'), 'default', array(
            'options' => array(
                'default'     => n2_('Default'),
                'superSlow10' => n2_('Super slow') . ' 10x',
                'superSlow'   => n2_('Super slow') . ' 3x',
                'slow'        => n2_('Slow') . ' 1.5x',
                'normal'      => n2_('Normal') . ' 1x',
                'fast'        => n2_('Fast') . ' 0.75x.',
                'superFast'   => n2_('Super fast') . ' 0.5x'
            )
        ));
    }

    public function export($export, $slider) {
        $export->addImage($slider['params']->get('background', ''));
        $export->addImage($slider['params']->get('backgroundVideoMp4', ''));
    }

    public function import($import, $slider) {

        $slider['params']->set('background', $import->fixImage($slider['params']->get('background', '')));
        $slider['params']->set('backgroundVideoMp4', $import->fixImage($slider['params']->get('backgroundVideoMp4', '')));
    }
}

N2SSPluginSliderType::addSliderType(new N2SSPluginTypeSimple);