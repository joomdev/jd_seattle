<?php
N2Loader::import('libraries.form.elements.imagelistfromfolder');

class N2ElementImageListFromFolderValue extends N2ElementImageListFromFolder {

    protected function renderOptions() {
        $html = '';
        foreach ($this->options AS $value => $option) {

            $selected = $this->isSelected($value);
            if ($value != -1) {
                $image = N2Uri::pathToUri($option['path']);
                $html .= N2Html::openTag("div", array("class" => "n2-radio-option n2-imagelist-option" . ($selected ? ' n2-active' : '')));
                $html .= N2Html::image($image, $option['label']);
                $html .= N2Html::closeTag("div");
            } else {
                $html .= N2Html::tag("div", array("class" => "n2-radio-option" . ($selected ? ' n2-active' : '')), $option['label']);
            }
        }

        return $html;
    }

    function parseValue($image) {
        return pathinfo($image, PATHINFO_FILENAME);
    }
}