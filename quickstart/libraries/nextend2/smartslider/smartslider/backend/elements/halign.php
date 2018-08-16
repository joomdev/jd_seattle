<?php
N2Loader::import('libraries.form.elements.radiotab');

class N2ElementHAlign extends N2ElementRadioTab {

    protected $inherit = false;

    protected $class = 'n2-form-element-radio-tab n2-form-element-icon-radio';

    protected $options = array(
        'left'   => 'n2-i n2-it n2-i-horizontal-left',
        'center' => 'n2-i n2-it n2-i-horizontal-center',
        'right'  => 'n2-i n2-it n2-i-horizontal-right'
    );

    public function __construct($parent, $name = '', $label = '', $default = '', array $parameters = array()) {
        parent::__construct($parent, $name, $label, $default, $parameters);

        if ($this->inherit) {
            $this->options = array(
                    'inherit' => 'n2-i n2-it n2-i-none'
                ) + $this->options;
        }
    }

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

    /**
     * @param bool $inherit
     */
    public function setInherit($inherit) {
        $this->inherit = $inherit;
    }

}