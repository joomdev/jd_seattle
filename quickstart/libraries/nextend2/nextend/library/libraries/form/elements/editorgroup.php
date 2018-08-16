<?php
N2Loader::import('libraries.form.elements.group');

class N2ElementEditorGroup extends N2ElementGroup {

    protected function fetchTooltip() {
        return N2Html::tag('div', array(
            'class' => 'n2-editor-header n2-h2 n2-uc'
        ), '<span>' . $this->label . '</span>');
    }
}