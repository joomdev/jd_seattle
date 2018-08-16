<?php

N2Loader::import('libraries.form.elements.text');

class N2ElementDate extends N2ElementText {

    protected function fetchElement() {

        N2JS::addInline('$("#' . $this->fieldID . '").datetimepicker({lazyInit: true, format:"Y-m-d H:i:s", weeks: false, className: "n2", lang: "' . substr(N2Localization::getLocale(), 0, 2) . '"});');

        return parent::fetchElement();
    }

    protected function getStyle() {
        return $this->style . '; text-align:center;';
    }
}
