<?php
N2Loader::import(array(
    'libraries.backgroundanimation.storage'
), 'smartslider');

class N2SmartSliderBackgroundAnimationModel extends N2SystemVisualModel {

    public $type = 'backgroundanimation';

    public function __construct($tableName = null) {

        parent::__construct($tableName);
        $this->storage = N2Base::getApplication('smartslider')->storage;
    }

    protected function getPath() {
        return dirname(__FILE__);
    }

    public function renderSetsForm() {

        $form    = new N2Form();
        $setsTab = new N2TabNaked($form, 'backgroundanimation-sets');
        new N2ElementList($setsTab, 'sets', '', '');

        echo $form->render($this->type . 'set');
    }

    public function renderForm() {
        $form = new N2Form();

        $properties = new N2Tab($form, 'background-animation-form', n2_('Properties'));

        new N2ElementColor($properties, 'color', n2_('Pimary color'), '333333ff', array(
            'alpha' => true
        ));

        $form->render('n2-background-animation');
    }
}