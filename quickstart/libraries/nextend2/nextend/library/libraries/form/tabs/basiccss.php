<?php
N2Loader::import('libraries.form.tab');

class N2TabBasicCSS extends N2Tab {


    public function render($control_name) {

        N2JS::addInline('new N2Classes.BasicCSS("n2-css-' . $this->name . '", "' . N2Base::getApplication('system')->router->createUrl('css/index') . '");');

        echo N2Html::openTag('div', array(
            'id'    => 'n2-css-' . $this->name,
            'class' => 'n2-basic-css-container'
        ));
        foreach ($this->tabs AS $tabname => $tab) {
            $tab->render($control_name);
        }
        echo N2Html::closeTag('div');
    }
}