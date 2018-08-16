<?php
N2Loader::import('libraries.form.elements.text');

class N2ElementTextAutocomplete extends N2ElementText {

    protected $class = 'n2-form-element-autocomplete ';

    protected $values = array();

    protected function fetchElement() {
        $html = parent::fetchElement();
        N2JS::addInline('new N2Classes.FormElementAutocompleteSimple("' . $this->fieldID . '", ' . json_encode($this->values) . ');');

        return $html;
    }

    /**
     * @param array $values
     */
    public function setValues($values) {
        $this->values = $values;
    }
}