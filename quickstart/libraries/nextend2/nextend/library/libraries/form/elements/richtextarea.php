<?php

class N2ElementRichTextarea extends N2Element {

    protected $fieldStyle = '';

    protected function fetchElement() {

        N2JS::addInline('new N2Classes.FormElementRichText("' . $this->fieldID . '");');

        $tools = array(
            N2Html::tag('div', array('class' => 'n2-textarea-rich-bold'), N2Html::tag('I', array('class' => 'n2-i n2-it n2-i-bold'))),
            N2Html::tag('div', array('class' => 'n2-textarea-rich-italic'), N2Html::tag('I', array('class' => 'n2-i n2-it n2-i-italic'))),
            N2Html::tag('div', array('class' => 'n2-textarea-rich-link'), N2Html::tag('I', array('class' => 'n2-i n2-it n2-i-link')))
        );
        $rich  = N2Html::tag('div', array('class' => 'n2-textarea-rich'), implode('', $tools));

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-textarea n2-form-element-rich-textarea n2-border-radius',
            'style' => $this->style
        ), $rich . N2Html::tag('textarea', array(
                'id'           => $this->fieldID,
                'name'         => $this->getFieldName(),
                'class'        => 'n2 - h5',
                'autocomplete' => 'off',
                'style'        => $this->fieldStyle
            ), $this->getValue()));
    }

    /**
     * @param string $fieldStyle
     */
    public function setFieldStyle($fieldStyle) {
        $this->fieldStyle = $fieldStyle;
    }


}
