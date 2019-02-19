<?php

N2Loader::import('libraries.form.elements.list');

class N2ElementNotice extends N2ElementList {

    public function __construct($parent, $parameter) {
        echo N2Html::tag('div', array(
            'class' => 'n2-label',
            'style' => 'background:rgba(255,0,0,0.4); width:100%; box-sizing: border-box;padding-bottom:3px;'
        ), N2Html::tag('div', array(), $parameter));
    }
}