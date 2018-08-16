<?php

class N2ElementText extends N2Element {

    protected $attributes = array();

    public $fieldType = 'text';

    protected $unit = false;

    protected function fetchElement() {

        N2JS::addInline('new N2Classes.FormElementText("' . $this->fieldID . '");');

        $html = N2Html::openTag('div', array(
            'class' => 'n2-form-element-text ' . $this->getClass() . ($this->unit ? ' n2-text-has-unit ' : '') . ' n2-border-radius',
            'style' => ($this->fieldType == 'hidden' ? 'display: none;' : '')
        ));

        $html .= $this->pre();
        $html .= N2Html::tag('input', $this->attributes + array(
                'type'         => $this->fieldType,
                'id'           => $this->fieldID,
                'name'         => $this->getFieldName(),
                'value'        => $this->getValue(),
                'class'        => 'n2-h5',
                'style'        => $this->getStyle(),
                'autocomplete' => 'off'
            ), false);

        $html .= $this->post();

        if (!empty($this->unit)) {
            $html .= N2Html::tag('div', array(
                'class' => 'n2-text-unit n2-h5 n2-uc'
            ), $this->unit);
        }
        $html .= "</div>";

        return $html;
    }

    public function setUnit($unit) {
        $this->unit = $unit;
    }

    protected function pre() {
        return '';
    }

    protected function post() {
        return '';
    }
}