<?php

class N2ElementBackground extends N2ElementHidden {

    private $options;

    public function __construct($parent, $name = '', $default = '', $parameters = array()) {
        parent::__construct($parent, $name, '', $default, $parameters);
    }

    protected function fetchElement() {
        $this->options = array(
            'image' => 'Image',
            'color' => 'Color'
        );
    

        N2JS::addInline('new N2Classes.FormElementBackground("' . $this->fieldID . '", "' . $this->getValue() . '");');


        $html = '<div id="' . $this->fieldID . '-panel" class="n2-subform-image">';
        foreach ($this->options AS $k => $value) {
            $html .= $this->getOptionHtml('$ss$/admin/images/background/', $k, $value);
        }
        $html .= '</div>';

        return $html . parent::fetchElement();
    }

    function getOptionHtml($path, $k, $label) {
        return N2Html::tag('div', array(
            'class'      => 'n2-subform-image-option ' . $this->isActive($k),
            'data-value' => $k
        ), N2Html::tag('div', array(
                'class' => 'n2-subform-image-element',
                'style' => 'background-image: URL(' . $this->getImage($path, $k) . ');'
            )) . N2Html::tag('div', array(
                'class' => 'n2-subform-image-title n2-h4'
            ), $label));
    }

    function getImage($path, $key) {
        return N2ImageHelper::fixed($path . $key . '.png');
    }

    function isActive($value) {
        if ($this->getValue() == $value) {
            return 'n2-active';
        }

        return '';
    }
}