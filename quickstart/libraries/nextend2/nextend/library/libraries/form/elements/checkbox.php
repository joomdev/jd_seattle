<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementCheckbox extends N2ElementHidden {

    public $hasTooltip = true;


    protected $value = null;

    protected $options = array();

    protected $style = '';

    protected function fetchElement() {

        $this->value = explode('||', $this->getValue());

        $html = N2Html::tag('div', array(
            'class' => 'n2-form-element-checkbox',
            'style' => $this->style
        ), $this->renderOptions() . parent::fetchElement());

        N2JS::addInline('new N2Classes.FormElementCheckbox("' . $this->fieldID . '", ' . json_encode(array_keys($this->options)) . ');');

        return $html;
    }

    /**
     *
     * @return string
     */
    protected function renderOptions() {

        $html = '';

        foreach ($this->option AS $value => $label) {

            $attributes = array(
                'class' => 'nextend-checkbox-option'
            );
            if ($this->isSelected($value)) {
                $attributes['selected'] = 'selected';
            }
            $html .= N2Html::tag('div', $attributes, $label);
        }

        return $html;
    }

    function isSelected($value) {
        if (in_array($value, $this->value)) {
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

    /**
     * @param string $style
     */
    public function setStyle($style) {
        $this->style = $style;
    }


}
