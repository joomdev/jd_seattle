<?php

N2Loader::import('libraries.stylemanager.storage');

class N2SystemStyleModel extends N2SystemVisualModel {

    public $type = 'style';

    public function renderForm() {

        $form     = new N2Form();
        $firstRow = new N2TabHorizontal($form, 'firstrow');

        new N2ElementColor($firstRow, 'backgroundcolor', n2_('Background color'), '000000FF', array(
            'alpha' => true
        ));

        new N2ElementNumberAutocomplete($firstRow, 'opacity', n2_('Opacity'), '100', array(
            'values' => array(
                0,
                50,
                90,
                100
            ),
            'unit'   => '%',
            'style'  => 'width: 22px;'
        ));

        $paddingConnected = new N2ElementConnected($firstRow, 'padding', n2_('Padding'), '0|*|0|*|0|*|0|*|px');
        new N2ElementNumberAutocomplete($paddingConnected, 'padding-1', '', '', array(
            'style'  => 'width: 22px;',
            'values' => array(
                0,
                5,
                10,
                20,
                30
            )
        ));
        new N2ElementNumberAutocomplete($paddingConnected, 'padding-2', '', '', array(
            'style'  => 'width: 22px;',
            'values' => array(
                0,
                5,
                10,
                20,
                30
            )
        ));
        new N2ElementNumberAutocomplete($paddingConnected, 'padding-3', '', '', array(
            'style'  => 'width: 22px;',
            'values' => array(
                0,
                5,
                10,
                20,
                30
            )
        ));
        new N2ElementNumberAutocomplete($paddingConnected, 'padding-4', '', '', array(
            'style'  => 'width: 22px;',
            'values' => array(
                0,
                5,
                10,
                20,
                30
            )
        ));
        new N2ElementUnits($paddingConnected, 'padding-5', '', '', array(
            'units' => array(
                'px',
                'em',
                '%'
            )
        ));


        $border = new N2ElementMixed($firstRow, 'border', n2_('Border'), '0|*|solid|*|000000ff');
        new N2ElementNumber($border, 'border-1', false, '', array(
            'style' => 'width:32px;',
            'unit'  => 'px'
        ));
        new N2ElementList($border, 'border-2', false, '', array(
            'options' => array(
                'none'   => n2_('None'),
                'dotted' => n2_('Dotted'),
                'dashed' => n2_('Dashed'),
                'solid'  => n2_('Solid'),
                'double' => n2_('Double'),
                'groove' => n2_('Groove'),
                'ridge'  => n2_('Ridge'),
                'inset'  => n2_('Inset'),
                'outset' => n2_('Outset')
            )
        ));
        new N2ElementColor($border, 'border-3', false, '', array(
            'alpha' => true
        ));


        new N2ElementNumberAutocomplete($firstRow, 'borderradius', n2_('Border radius'), '0', array(
            'values' => array(
                0,
                3,
                5,
                10,
                99
            ),
            'unit'   => 'px',
            'style'  => 'width: 22px;'
        ));


        $boxShadow = new N2ElementMixed($firstRow, 'boxshadow', n2_('Box shadow'), '0|*|0|*|0|*|0|*|000000ff');
        new N2ElementNumber($boxShadow, 'boxshadow-1', false, '', array(
            'style' => 'width:22px;',
            'unit'  => 'px'
        ));
        new N2ElementNumber($boxShadow, 'boxshadow-2', false, '', array(
            'style' => 'width:22px;',
            'unit'  => 'px'
        ));
        new N2ElementNumber($boxShadow, 'boxshadow-3', false, '', array(
            'style' => 'width:22px;',
            'unit'  => 'px'
        ));
        new N2ElementNumber($boxShadow, 'boxshadow-4', false, '', array(
            'style' => 'width:22px;',
            'unit'  => 'px'
        ));
        new N2ElementColor($boxShadow, 'boxshadow-5', false, '', array(
            'alpha' => true
        ));

        $form->render('n2-style-editor');
    }

    public function renderFormExtra() {
        $form = new N2Form();
        $tab  = new N2TabRaw($form, 'extracss', n2_('Extra CSS'));
        new N2ElementTextarea($tab, 'extracss', '', '', array(
            'style'      => 'display: block; margin:20px;',
            'fieldStyle' => 'width:100%;height:80px;resize:vertical;'
        ));
        $form->render('n2-style-editor');
    }

    public function renderSetsForm() {

        $form    = new N2Form();
        $setsTab = new N2TabNaked($form, 'style-sets', n2_('Style sets'));
        new N2ElementList($setsTab, 'sets', '', '');

        echo $form->render($this->type . 'set');
    }
}