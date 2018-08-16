<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementMixed extends N2ElementHidden implements N2FormElementContainer {

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

        $html     = "<div class='n2-form-element-mixed' style='" . $this->style . "'>";
        $elements = array();
        $i        = 0;
        foreach ($this->elements AS $element) {

            $element->setExposeName(false);
            if (isset($value[$i])) {
                $element->setDefaultValue($value[$i]);
            }

            $elementHtml = $element->render($this->name . $this->control_name);

            $html .= N2Html::tag('div', $element->getRowAttributes() + array(
                    'class' => "n2-mixed-group " . $element->getRowClass()
                ), N2Html::tag('div', array(
                    'class' => 'n2-mixed-label'
                ), $elementHtml[0]) . N2Html::tag('div', array(
                    'class' => 'n2-mixed-element'
                ), $elementHtml[1]));


            $elements[$i] = $element->getID();
            $i++;
        }

        $html .= parent::fetchElement();
        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementMixed("' . $this->fieldID . '", ' . json_encode($elements) . ', "' . self::$separator . '");');

        return $html;
    }

    /**
     * @param string $style
     */
    public function setStyle($style) {
        $this->style = $style;
    }


}
