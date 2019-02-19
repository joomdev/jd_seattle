<?php

N2Loader::import('libraries.form.elements.group');

class N2ElementWidgetPosition extends N2ElementGroup {

    protected function fetchElement() {
        $values = explode('|*|', $this->getValue());
        if (!isset($values[6]) || $values[6] == '') {
            $values[6] = 1;
        }
        $values[6] = intval($values[6]);


        $this->getForm()
             ->set($this->name, implode('|*|', $values));


        new N2ElementSwitcher($this, $this->name . '-mode', n2_('Mode'), 'simple', array(
            'post'     => 'break',
            'rowClass' => 'n2-expert',
            'options'  => array(
                'simple'   => n2_('Simple'),
                'advanced' => n2_('Advanced')
            )
        ));

        $this->addSimple();

        $this->addAdvanced();

        N2JS::addInline('new N2Classes.FormElementWidgetPosition("' . $this->fieldID . '");');

        return parent::fetchElement();
    }

    protected function addSimple() {

        $simple = new N2ElementGroup($this, $this->name . '-simple');

        new N2ElementSliderWidgetArea($simple, $this->name . '-area', false);
        new N2ElementList($simple, $this->name . '-stack', n2_('Stack'), 1, array(
            'options' => array(
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5
            )
        ));
        new N2ElementNumber($simple, $this->name . '-offset', n2_('Offset'), 0, array(
            'style' => 'width:30px;',
            'unit'  => 'px'
        ));
    }

    protected function addAdvanced() {

        $advanced = new N2ElementGroup($this, $this->name . '-advanced', false, array(
            'style' => 'width:400px;'
        ));

        new N2ElementSwitcher($advanced, $this->name . '-horizontal', n2_('Horizontal'), 'left', array(
            'options' => array(
                'left'  => n2_('Left'),
                'right' => n2_('Right')
            )
        ));

        new N2ElementText($advanced, $this->name . '-horizontal-position', n2_('Position'), 0, array(
            'style' => 'width:100px;'
        ));

        new N2ElementSwitcher($advanced, $this->name . '-horizontal-unit', n2_('Unit'), 'px', array(
            'options' => array(
                'px' => 'px',
                '%'  => '%'
            )
        ));

        new N2ElementSwitcher($advanced, $this->name . '-vertical', n2_('Vertical'), 'top', array(
            'options' => array(
                'top'    => n2_('Top'),
                'bottom' => n2_('Bottom')
            )
        ));

        new N2ElementText($advanced, $this->name . '-vertical-position', n2_('Position'), 0, array(
            'style' => 'width:100px;'
        ));

        new N2ElementSwitcher($advanced, $this->name . '-vertical-unit', n2_('Unit'), 'px', array(
            'options' => array(
                'px' => 'px',
                '%'  => '%'
            )
        ));
    }
}
