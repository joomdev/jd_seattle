<?php
N2Loader::import('libraries.form.elements.text');

class N2ElementFamily extends N2ElementText {

    protected $class = 'n2-form-element-autocomplete ';

    protected function fetchElement() {
        $html         = parent::fetchElement();
        $fontSettings = N2Fonts::loadSettings();
        $families     = explode("\n", $fontSettings['preset-families']);

        usort($families, 'N2ElementFamily::sort');
        N2JS::addInline('new N2Classes.FormElementAutocompleteSimple("' . $this->fieldID . '", ' . json_encode($families) . ');');

        return $html;
    }

    public static function sort($a, $b) {
        $a = preg_replace('|[^a-zA-Z0-9 ]|', '', $a);
        $b = preg_replace('|[^a-zA-Z0-9 ]|', '', $b);

        return strnatcmp($a, $b);
    }
}