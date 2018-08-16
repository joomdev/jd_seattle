<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementRadio extends N2ElementHidden {

    protected $options;

    protected $class = 'n2-form-element-radio';

    protected $style = '';

    protected $value;

    public $hasTooltip = true;

    protected function fetchElement() {

        $this->value = $this->getValue();

        $html = N2Html::tag('div', array(
            'class' => $this->class,
            'style' => $this->style
        ), $this->renderOptions() . parent::fetchElement());

        N2JS::addInline('new N2Classes.FormElementRadio("' . $this->fieldID . '", ' . json_encode(array_keys($this->options)) . ');');

        return $html;
    }

    /**
     * @return string
     */
    protected function renderOptions() {

        $length = count($this->options) - 1;

        $html = '';
        $i    = 0;
        foreach ($this->options AS $value => $label) {
            $html .= N2Html::tag('div', array(
                'class' => 'n2-radio-option n2-h4' . ($this->isSelected($value) ? ' n2-active' : '') . ($i == 0 ? ' n2-first' : '') . ($i == $length ? ' n2-last' : '')
            ), N2Html::tag('div', array(
                    'class' => 'n2-radio-option-marker'
                ), '<i class="n2-i n2-it n2-i-tick"></i>') . '<span>' . $label . '</span>');
            $i++;
        }

        return $html;
    }

    function isSelected($value) {
        if ((string)$value == $this->value) {
            return true;
        }

        return false;
    }

    /**
     * @param array $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    public function setStyle($style) {
        $this->style = $style;
    }
}
