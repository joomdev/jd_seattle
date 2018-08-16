<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementSwitcher extends N2ElementHidden {

    protected $hasTooltip = true;

    protected $options = array();

    protected function fetchElement() {

        $html = "<div class='n2-form-element-switcher' style='" . $this->style . "'>";

        $i            = 0;
        $units        = count($this->options) - 1;
        $currentValue = $this->getValue();
        foreach ($this->options AS $value => $label) {

            $html .= N2Html::tag('div', array(
                'class' => 'n2-switcher-unit n2-h5 n2-uc ' . ($value == $currentValue ? 'n2-active ' : '') . ($i == 0 ? 'n2-first ' : '') . ($i == $units ? 'n2-last ' : '')
            ), $label);
            $i++;
        }

        $html .= parent::fetchElement();

        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementSwitcher("' . $this->fieldID . '", ' . json_encode(array_keys($this->options)) . ');');

        return $html;
    }

    /**
     * @param array $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }


}
