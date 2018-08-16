<?php

N2Loader::import('libraries.form.elements.hidden');
N2Loader::import('libraries.form.form');

class N2ElementIconManager extends N2ElementHidden {

    public $hasTooltip = true;

    protected function fetchElement() {

        $html = N2Html::tag('div', array(
            'class' => 'n2-form-element-text n2-form-element-icon n2-border-radius'
        ), N2Html::image(N2Image::base64Transparent(), '', array(
                'class' => 'n2-form-element-preview'
            )) . '<a id="' . $this->fieldID . '_edit" class="n2-form-element-button n2-icon-button n2-h5 n2-uc" href="#"><i class="n2-i n2-it  n2-i-layer-image"></i></a>' . parent::fetchElement());

        N2JS::addInline('
            new N2Classes.FormElementIconManager("' . $this->fieldID . '");
        ');

        return $html;
    }

}