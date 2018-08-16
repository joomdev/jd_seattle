<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementUnits extends N2ElementHidden {

    public $hasTooltip = true;

    protected $style = '';

    protected $units = array();

    protected function fetchElement() {

        $values = array();

        $html = "<div class='n2-form-element-units' style='" . $this->style . "'>";

        $currentValue = $this->getValue();
        $currentLabel = '';

        $html .= N2Html::openTag('div', array(
            'class' => 'n2-element-units'
        ));
        foreach ($this->units AS $unit) {
            $values[] = $unit;

            $html .= N2Html::tag('div', array(
                'class' => 'n2-element-unit n2-h5 n2-uc '
            ), $unit);

            if ($currentValue == $unit) {
                $currentLabel = $unit;
            }
        }

        $html .= "</div>";

        $html .= N2Html::tag('div', array(
            'class' => 'n2-element-current-unit n2-h5 n2-uc '
        ), $currentLabel);

        $html .= parent::fetchElement();

        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementUnits("' . $this->fieldID . '", ' . json_encode($values) . ');');

        return $html;
    }

    /**
     * @param string $style
     */
    public function setStyle($style) {
        $this->style = $style;
    }

    /**
     * @param array $units
     */
    public function setUnits($units) {
        $this->units = $units;
    }


}
