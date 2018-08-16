<?php
N2Loader::import('libraries.form.elements.text');

class N2ElementAutocomplete extends N2ElementText {

    protected $options = array();

    protected $attributes = array();

    public $fieldType = 'text';

    protected $class = 'n2-form-element-autocomplete ';

    protected function fetchElement() {
        $html = parent::fetchElement();

        N2JS::addInline('new N2Classes.FormElementAutocomplete("' . $this->fieldID . '", ' . json_encode($this->options) . ');');

        return $html;
    }

    protected function getStyle() {
        return $this->style;
    }

    protected function pre() {
        return '';
    }

    protected function post() {
        return N2Html::tag('a', array(
            'href'  => '#',
            'class' => 'n2-form-element-clear'
        ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-empty n2-i-grey-opacity'), ''));
    }

    /**
     * @param array $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }
}