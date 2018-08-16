<?php

class N2ElementGroup extends N2Element implements N2FormElementContainer {

    /** @var N2Element[] */
    protected $elements = array();

    protected $style = '';

    public function __construct($parent, $name = '', $label = '', $parameters = array()) {
        parent::__construct($parent, $name, $label, '', $parameters);
    }

    public function addElement($element) {
        $this->elements[] = $element;
    }

    protected function fetchTooltip() {
        if ($this->label) {
            return parent::fetchTooltip();
        }

        return N2Html::tag('label', array(
            'for' => $this->fieldID
        ), '');
    }

    protected function fetchElement() {

        $html = '';
        foreach ($this->elements AS $element) {

            list($label, $fieldHTML) = $element->render($this->control_name);

            $html .= N2Html::tag('div', $element->getRowAttributes() + array(
                    'class'      => 'n2-mixed-group ' . $element->getRowClass(),
                    'data-field' => $element->getName()
                ), N2Html::tag('div', array('class' => 'n2-mixed-label' . (($element->hasLabel() ? '' : ' n2-empty-group-label'))), $label) . N2Html::tag('div', array('class' => 'n2-mixed-element'), $fieldHTML));

            if ($element->getPost() == 'break') {
                $html .= '<br class="' . $element->getClass() . '" />';
            }
        }

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-mixed',
            'style' => $this->style
        ), $html);
    }

    public function setStyle($style) {
        $this->style = $style;
    }
}
