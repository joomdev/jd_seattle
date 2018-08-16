<?php
N2Loader::import('libraries.form.elements.radiotab');

class N2ElementInnerAlign extends N2ElementRadioTab {

    protected $class = 'n2-form-element-radio-tab n2-form-element-icon-radio';


    protected $options = array(
        'inherit' => 'n2-i n2-it n2-i-none',
        'left'    => 'n2-i n2-it n2-i-left',
        'center'  => 'n2-i n2-it n2-i-center',
        'right'   => 'n2-i n2-it n2-i-right'
    );

    protected function renderOptions() {
        $length = count($this->options) - 1;

        $html = '';
        $i    = 0;
        foreach ($this->options AS $value => $class) {

            $html .= N2Html::tag('div', array(
                'class' => 'n2-radio-option' . ($this->isSelected($value) ? ' n2-active' : '') . ($i == 0 ? ' n2-first' : '') . ($i == $length ? ' n2-last' : '')
            ), N2Html::tag('i', array('class' => $class)));
            $i++;
        }

        return $html;
    }
}