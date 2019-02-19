<?php

class N2ElementImportant extends N2Element {

    protected function fetchTooltip() {
        return ' ';
    }

    protected function fetchElement() {
        return N2Html::tag('div', array(
            'class' => 'n2-element-important'
        ), $this->label);
    }
}
