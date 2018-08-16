<?php

N2Loader::import('libraries.form.elements.image');
N2Loader::import('libraries.image.manager');

class N2ElementImageManager extends N2ElementImage {

    protected $attributes = array();

    protected $class = 'n2-form-element-img ';

    protected function fetchElement() {
        $html = parent::fetchElement();

        $html .= '<a id="' . $this->fieldID . '_manage" class="n2-button n2-button-normal n2-button-m n2-radius-s n2-button-grey n2-h5 n2-uc n2-expert" href="#">' . n2_('Manage') . '</a>';

        N2JS::addInline('new N2Classes.FormElementImageManager("' . $this->fieldID . '", {});');

        return $html;
    }
}
