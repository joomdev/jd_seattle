<?php
N2Loader::import('libraries.form.elements.number');

class N2ElementNumberAutocomplete extends N2ElementNumber {

    protected $values = array();

    protected $class = 'n2-form-element-number n2-form-element-autocomplete ';

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