<?php

N2Loader::import('libraries.form.elements.list');

class N2ElementNotice extends N2ElementHidden {

    protected function fetchElement() {
        echo N2Html::tag('div', array(
            'class' => 'n2-ss-editor-window-notice'
        ), N2Html::tag('div', array(), $this->name));
    }
}