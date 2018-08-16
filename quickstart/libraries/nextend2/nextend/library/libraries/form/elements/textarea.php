<?php

class N2ElementTextarea extends N2Element {

    protected $fieldStyle = '';

    protected function fetchElement() {

        N2JS::addInline('new N2Classes.FormElementText("' . $this->fieldID . '");');

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-textarea n2-border-radius',
            'style' => $this->style
        ), N2Html::tag('textarea', array(
            'id'           => $this->fieldID,
            'name'         => $this->getFieldName(),
            'class'        => 'n2-h5',
            'autocomplete' => 'off',
            'style'        => $this->fieldStyle
        ), $this->getValue()));
    }

    protected function setFieldStyle($fieldStyle) {
        $this->fieldStyle = $fieldStyle;
    }
}
