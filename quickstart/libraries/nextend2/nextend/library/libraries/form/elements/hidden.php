<?php

class N2ElementHidden extends N2Element {

    protected $hasTooltip = false;

    protected function fetchTooltip() {
        if ($this->hasTooltip) {
            return parent::fetchTooltip();
        } else {
            return $this->fetchNoTooltip();
        }
    }

    protected function fetchElement() {

        return N2Html::tag('input', array(
            'id'           => $this->fieldID,
            'name'         => $this->getFieldName(),
            'value'        => $this->getValue(),
            'type'         => 'hidden',
            'autocomplete' => 'off'
        ), false);
    }
}
