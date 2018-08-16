<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementOnOff extends N2ElementHidden {

    protected $hasTooltip = true;

    protected $relatedFields = array();

    protected $relatedAttribute = '';

    protected $isEnable = true;

    protected function fetchElement() {
        $html = "<div class='n2-form-element-onoff " . $this->isOn() . "' style='" . $this->style . "'>";
        $html .= N2Html::tag('div', array(
            'class' => 'n2-onoff-slider'
        ), N2Html::tag('div', array(
                'class' => 'n2-onoff-yes'
            ), '<i class="n2-i n2-i-tick"></i>') . N2Html::tag('div', array(
                'class' => 'n2-onoff-round'
            )) . N2Html::tag('div', array(
                'class' => 'n2-onoff-no'
            ), '<i class="n2-i n2-i-close"></i>'));
        $html .= parent::fetchElement();
        $html .= "</div>";

        $options = array(
            'relatedFields'    => $this->relatedFields,
            'relatedAttribute' => $this->relatedAttribute
        );
        N2JS::addInline('new N2Classes.FormElementOnoff("' . $this->fieldID . '",' . json_encode($this->isEnable) . ', ' . json_encode($options) . ');');

        return $html;
    }

    private function isOn() {
        if ($this->getValue()) {
            return 'n2-onoff-on';
        }

        return '';
    }

    /**
     * @param string $relatedFields
     */
    public function setRelatedFields($relatedFields) {
        $this->relatedFields = $relatedFields;
    }

    public function setRelatedAttribute($relatedAttribute) {
        $this->relatedAttribute = $relatedAttribute;
    }

    /**
     * @param bool $isEnable
     */
    public function setIsEnable($isEnable) {
        $this->isEnable = $isEnable;
    }


}
