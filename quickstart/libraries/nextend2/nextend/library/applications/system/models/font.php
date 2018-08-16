<?php

N2Loader::import('libraries.fonts.storage');

class N2SystemFontModel extends N2SystemVisualModel {

    public $type = 'font';

    public function renderForm() {

        $form     = new N2Form();
        $firstRow = new N2TabHorizontal($form, 'firstrow');
        new N2ElementFamily($firstRow, 'family', n2_('Family'), 'Arial, Helvetica', array(
            'style' => 'width:150px;'
        ));
        new N2ElementColor($firstRow, 'color', n2_('Color'), '000000FF', array(
            'alpha' => true
        ));

        $sizeConnected = new N2ElementConnected($firstRow, 'size', n2_('Size'), '14|*|px');
        new N2ElementNumberAutocomplete($sizeConnected, 'size-1', '', '', array(
            'style'  => 'width: 22px;',
            'values' => array(
                8,
                10,
                12,
                14,
                18,
                24,
                30,
                48,
                72
            )
        ));
        new N2ElementUnits($sizeConnected, 'size-2', '', '', array(
            'units' => array(
                'px',
                '%'
            )
        ));


        new N2ElementList($firstRow, 'weight', n2_('Font weight'), '', array(
            'options' => array(
                '0'   => n2_('Normal'),
                '1'   => n2_('Bold'),
                '100' => '100',
                '200' => '200 - ' . n2_('Extra light'),
                '300' => '300 - ' . n2_('Light'),
                '600' => '600 - ' . n2_('Semi bold'),
                '700' => '700 - ' . n2_('Bold'),
                '800' => '800 - ' . n2_('Extra bold'),
            )
        ));
        new N2ElementDecoration($firstRow, 'decoration', n2_('Decoration'));
        new N2ElementTextAutocomplete($firstRow, 'lineheight', n2_('Line height'), '18px', array(
            'values' => array(
                'normal',
                '1',
                '1.2',
                '1.5',
                '1.8',
                '2'
            ),
            'style'  => 'width:70px;'
        ));
        new N2ElementTextAlign($firstRow, 'textalign', n2_('Text align'), 'inherit');
        new N2ElementTextAutocomplete($firstRow, 'letterspacing', n2_('Letter spacing'), 'normal', array(
            'values' => array(
                'normal',
                '1px',
                '2px',
                '5px',
                '10px',
                '15px'
            ),
            'style'  => 'width:50px;'
        ));
        new N2ElementTextAutocomplete($firstRow, 'wordspacing', n2_('Word spacing'), 'normal', array(
            'values' => array(
                'normal',
                '2px',
                '5px',
                '10px',
                '15px'
            ),
            'style'  => 'width:50px;'
        ));
        new N2ElementList($firstRow, 'texttransform', n2_('Transform'), 'none', array(
            'options' => array(
                'none'       => n2_('None'),
                'capitalize' => n2_('Capitalize'),
                'uppercase'  => n2_('Uppercase'),
                'lowercase'  => n2_('Lowercase')
            )
        ));


        $textShadow = new N2ElementMixed($firstRow, 'tshadow', n2_('Text shadow'), '0|*|0|*|1|*|000000FF', array(
            'class' => 'n2-expert'
        ));
        new N2ElementNumber($textShadow, 'tshadow-1', false, '', array(
            'style' => 'width:32px;',
            'unit'  => 'px'
        ));
        new N2ElementNumber($textShadow, 'tshadow-2', false, '', array(
            'style' => 'width:32px;',
            'unit'  => 'px'
        ));
        new N2ElementNumber($textShadow, 'tshadow-3', false, '', array(
            'style' => 'width:32px;',
            'unit'  => 'px'
        ));
        new N2ElementColor($textShadow, 'tshadow-4', false, '', array(
            'alpha' => true
        ));


        $form->render('n2-font-editor');
    }

    public function renderFormExtra() {
        $form = new N2Form();
        $tab  = new N2TabRaw($form, 'extracss', n2_('Extra CSS'));
        new N2ElementTextarea($tab, 'extracss', '', '', array(
            'style'      => 'display: block; margin:20px;',
            'fieldStyle' => 'width:100%;height:80px;resize:vertical;'
        ));
        $form->render('n2-font-editor');
    }

    public function renderSetsForm() {

        $form    = new N2Form();
        $setsTab = new N2TabNaked($form, 'font-sets', n2_('Font sets'));
        new N2ElementList($setsTab, 'sets', '', '');

        echo $form->render($this->type . 'set');
    }
}