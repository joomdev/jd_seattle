<?php

N2Loader::import('libraries.animations.storage');

class N2SystemAnimationModel extends N2SystemVisualModel {

    public $type = 'animation';


    public function renderSetsForm() {

        $form    = new N2Form();
        $setsTab = new N2TabNaked($form, 'animation-sets', n2_('Animation sets'));
        new N2ElementList($setsTab, 'sets', '', '');

        echo $form->render($this->type . 'set');
    }

    public function renderForm() {
        $form = new N2Form();

        $firstRow = new N2TabHorizontal($form, 'firstrow');
        new N2ElementText($firstRow, 'name', n2_('Name'));
        new N2ElementNumberAutocomplete($firstRow, 'duration', n2_('Duration'), 500, array(
            'values' => array(
                500,
                800,
                1000,
                1500,
                2000
            ),
            'min'    => 0,
            'unit'   => 'ms',
            'wide'   => 5
        ));
        new N2ElementNumberAutocomplete($firstRow, 'delay', n2_('Delay'), 0, array(
            'values' => array(
                0,
                500,
                800,
                1000,
                1500,
                2000
            ),
            'min'    => 0,
            'unit'   => 'ms',
            'wide'   => 5
        ));
        new N2ElementEasing($firstRow, 'easing', n2_('Easing'), 'easeOutCubic');

        $secondRow = new N2TabHorizontal($form, 'secondrow');
        new N2ElementNumberAutocomplete($secondRow, 'opacity', n2_('Opacity'), 100, array(
            'wide'   => 3,
            'min'    => 0,
            'max'    => 100,
            'values' => array(
                0,
                50,
                100
            ),
            'unit'   => '%'
        ));
        new N2ElementNumberSlider($secondRow, 'n2blur', n2_('Blur'), 0, array(
            'wide' => 3,
            'min'  => 0,
            'max'  => 100,
            'unit' => 'px'
        ));

        $offset = new N2ElementMixed($secondRow, 'offset', n2_('Offset'), '0|*|0|*|0');
        new N2ElementNumberAutocomplete($offset, 'offset-x', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'X',
            'values'   => array(
                -800,
                -400,
                -200,
                -100,
                -50,
                0,
                50,
                100,
                200,
                400,
                800
            ),
            'unit'     => 'px'
        ));
        new N2ElementNumberAutocomplete($offset, 'offset-y', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'Y',
            'values'   => array(
                -800,
                -400,
                -200,
                -100,
                -50,
                0,
                50,
                100,
                200,
                400,
                800
            ),
            'unit'     => 'px'
        ));
        new N2ElementNumberAutocomplete($offset, 'offset-z', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'Z',
            'values'   => array(
                0
            ),
            'unit'     => 'px',
            'rowClass' => 'n2-expert'
        ));


        $rotate = new N2ElementMixed($secondRow, 'rotate', n2_('Rotate'), '0|*|0|*|0');
        new N2ElementNumberAutocomplete($rotate, 'rotate-x', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'X',
            'values'   => array(
                0,
                90,
                180,
                -90,
                -180
            ),
            'unit'     => '°'
        ));
        new N2ElementNumberAutocomplete($rotate, 'rotate-y', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'Y',
            'values'   => array(
                0,
                90,
                180,
                -90,
                -180
            ),
            'unit'     => '°'
        ));
        new N2ElementNumberAutocomplete($rotate, 'rotate-z', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'Z',
            'values'   => array(
                0,
                90,
                180,
                -90,
                -180
            ),
            'unit'     => '°'
        ));

        $scale = new N2ElementMixed($secondRow, 'scale', n2_('Scale'), '100|*|100|*|100');
        new N2ElementNumberAutocomplete($scale, 'scale-x', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'X',
            'min'      => 0,
            'values'   => array(
                0,
                50,
                100,
                150
            ),
            'unit'     => '%'
        ));
        new N2ElementNumberAutocomplete($scale, 'scale-y', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'Y',
            'min'      => 0,
            'values'   => array(
                0,
                50,
                100,
                150
            ),
            'unit'     => '%'
        ));
        new N2ElementNumberAutocomplete($scale, 'scale-z', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'Z',
            'min'      => 0,
            'values'   => array(
                0,
                50,
                100,
                150
            ),
            'unit'     => '%',
            'rowClass' => 'n2-expert'
        ));

        new N2ElementNumber($secondRow, 'skew', n2_('Skew'), 0, array(
            'wide'     => 4,
            'unit'     => '%',
            'rowClass' => 'n2-expert'
        ));


        $form->render('n2-animation-editor');
    }

    public function renderFormExtra() {
        $form = new N2Form();

        $tab = new N2Tab($form, 'layer-animation-global', n2_('Layer global animation properties'));

        new N2ElementOnOff($tab, 'special-zero', n2_('Special zero'), 0, array(
            'rowClass' => 'n2-expert'
        ));

        $repeat = new N2ElementGroup($tab, 'repeat', n2_('Repeat'));
        new N2ElementNumber($repeat, 'repeat-count', n2_('Count'), 0, array(
            'wide' => 3,
            'unit' => n2_('loop')
        ));
        new N2ElementNumber($repeat, 'repeat-start-delay', n2_('Start delay'), 0, array(
            'wide' => 5,
            'unit' => 'ms'
        ));

        $transformOrigin = new N2ElementMixed($tab, 'transformorigin', n2_('Transform origin'), '50|*|50|*|0');

        new N2ElementNumberAutocomplete($transformOrigin, 'transformorigin-x', false, 50, array(
            'wide'     => 4,
            'sublabel' => 'X',
            'values'   => array(
                0,
                50,
                100
            ),
            'unit'     => '%'
        ));
        new N2ElementNumberAutocomplete($transformOrigin, 'transformorigin-y', false, 50, array(
            'wide'     => 4,
            'sublabel' => 'Y',
            'values'   => array(
                0,
                50,
                100
            ),
            'unit'     => '%'
        ));
        new N2ElementNumberAutocomplete($transformOrigin, 'transformorigin-z', false, 0, array(
            'wide'     => 4,
            'sublabel' => 'Z',
            'values'   => array(
                0,
            ),
            'unit'     => 'px',
            'rowClass' => 'n2-expert'
        ));

        $eventNames = array(
            'layerAnimationPlayIn',
            'LayerClick',
            'LayerMouseEnter',
            'LayerMouseLeave',
            'SlideClick',
            'SlideMouseEnter',
            'SlideMouseLeave',
            'SliderClick',
            'SliderMouseEnter',
            'SliderMouseLeave'
        );

        $events = new N2ElementGroup($tab, 'events', n2_('Event'));
        new N2ElementAutocomplete($events, 'play', n2_('Play'), '', array(
            'options' => $eventNames,
        ));
        new N2ElementAutocomplete($events, 'pause', n2_('Pause'), '', array(
            'options' => $eventNames,
        ));
        new N2ElementAutocomplete($events, 'stop', n2_('Stop'), '', array(
            'options' => $eventNames,
        ));

        new N2ElementOnOff($events, 'repeatable', n2_('Repeatable'), 0);
        new N2ElementOnOff($events, 'repeat-self-only', n2_('Repeat self only'), 0);
        new N2ElementOnOff($events, 'instant-out', n2_('Instant out'), 0);

        $form->render('n2-animation-editor');
    }
}