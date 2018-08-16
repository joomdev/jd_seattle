<?php
N2Loader::import('libraries.form.elements.radiotab');

class N2ElementStyleMode extends N2ElementRadioTab {

    protected $class = 'n2-form-element-style-mode';

    public $hasTooltip = false;

    protected function renderOptions() {

        $length = count($this->options) - 1;

        $html = '';
        $i    = 0;
        foreach ($this->options AS $value => $label) {

            $html .= N2Html::tag('div', array(
                'class' => 'n2-radio-option' . ($this->isSelected($value) ? ' n2-active' : '') . ($i == 0 ? ' n2-first' : '') . ($i == $length ? ' n2-last' : '')
            ), $label);
            $i++;
        }

        N2JS::addInline('new N2Classes.FormElementStyleMode("' . $this->fieldID . '");');

        return N2Html::tag('div', array(
                'class' => 'n2-form-element-style-mode-label'
            ), n2_($this->label)) . N2Html::tag('div', array(
                'class'      => 'n2-form-element-style-mode-reset n2-button n2-button-icon n2-button-s n2-radius-s n2-button-grey',
                'data-n2tip' => n2_('Reset to normal state')
            ), '<i class="n2-i n2-i-reset2"></i>') . N2Html::tag('div', array(
                'class' => 'n2-form-element-radio-tab'
            ), $html);
    }
}