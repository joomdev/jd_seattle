<?php
N2Loader::import('libraries.form.elements.radio');

class N2ElementRadioTab extends N2ElementRadio {

    protected $class = 'n2-form-element-radio-tab';

    protected function renderOptions() {

        $length = count($this->options) - 1;

        $html = '';
        $i    = 0;
        foreach ($this->options AS $value => $label) {
            $html .= N2Html::tag('div', array(
                'class' => 'n2-radio-option n2-h4' . ($this->isSelected($value) ? ' n2-active' : '') . ($i == 0 ? ' n2-first' : '') . ($i == $length ? ' n2-last' : '')
            ), $label);
            $i++;
        }

        return $html;
    }
}
