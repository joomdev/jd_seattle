<?php
N2Loader::import('libraries.form.elements.radio');

class N2ElementImageListLabel extends N2ElementRadio {

    protected function fetchElement() {
        return N2Html::tag("div", array(
            'class' => 'n2-imagelist n2-imagelistlabel',
            'style' => $this->style
        ), parent::fetchElement());
    }

    protected function renderOptions() {
        $html = '';
        foreach ($this->options AS $value => $option) {

            $html .= N2Html::tag("div", array(
                "class" => "n2-radio-option n2-imagelist-option" . ($this->isSelected($value) ? ' n2-active' : ''),
                "style" => "background-image:URL(" . N2ImageHelper::fixed($option['image']) . ");"
            ), N2Html::tag('span', array(), $option['label']));
        }

        return $html;
    }

    function isSelected($value) {
        if (basename($value) == basename($this->getValue())) {
            return true;
        }

        return false;
    }
}
