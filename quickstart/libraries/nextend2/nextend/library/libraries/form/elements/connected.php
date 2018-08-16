<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementConnected extends N2ElementHidden implements N2FormElementContainer {

    protected $hasTooltip = true;

    private static $separator = '|*|';

    protected $style = '';

    /** @var N2Element[] */
    protected $elements = array();

    public function addElement($element) {
        $this->elements[] = $element;
    }

    protected function fetchElement() {
        $default = explode(self::$separator, $this->defaultValue);
        $value   = explode(self::$separator, $this->getValue());
        $value   = $value + $default;

        $html        = "<div class='n2-form-element-connected' style='" . $this->style . "'>";
        $elementsIDs = array();
        $i           = 0;
        foreach ($this->elements AS $element) {

            $element->setExposeName(false);
            if (isset($value[$i])) {
                $element->setDefaultValue($value[$i]);
            }

            $elementHtml = $element->render($this->name . $this->control_name);
            $html .= $elementHtml[1];
            $elementsIDs[$i] = $element->getID();
            $i++;
        }

        $html .= parent::fetchElement();
        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementMixed("' . $this->fieldID . '", ' . json_encode($elementsIDs) . ', "' . self::$separator . '");');

        return $html;
    }

    /**
     * @param string $style
     */
    public function setStyle($style) {
        $this->style = $style;
    }
}