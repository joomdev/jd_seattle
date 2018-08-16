<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementMarginPadding extends N2ElementHidden implements N2FormElementContainer {

    protected $hasTooltip = true;

    private static $separator = '|*|';

    /** @var N2Element[] */
    protected $elements = array();

    protected $unit = false;

    public function addElement($element) {
        $this->elements[] = $element;
    }

    protected function fetchElement() {
        $default = explode(self::$separator, $this->defaultValue);
        $value   = explode(self::$separator, $this->getValue());
        $value   = $value + $default;

        $html = "<div class='n2-form-element-connected-marginpadding' style='" . $this->style . "'>";

        $html .= '<div class="n2-text-sub-label n2-h5 n2-uc"><i class="n2-i n2-it n2-i-layerunlink"></i></div>';
        $elements = array();
        $i        = 0;
        foreach ($this->elements AS $element) {

            $element->setExposeName(false);
            if (isset($value[$i])) {
                $element->setDefaultValue($value[$i]);
            }

            $elementHtml = $element->render($this->name . $this->control_name);

            $html .= $elementHtml[1];
            $elements[$i] = $element->getID();
            $i++;
        }

        if ($this->unit) {
            $html .= '<div class="n2-form-element-units"><div class="n2-element-current-unit n2-h5 n2-uc">' . $this->unit . '</div></div>';
        }

        $html .= parent::fetchElement();
        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementMarginPadding("' . $this->fieldID . '", ' . json_encode($elements) . ', "' . self::$separator . '");');

        return $html;
    }

    /**
     * @param bool|string $unit
     */
    public function setUnit($unit) {
        $this->unit = $unit;
    }


}