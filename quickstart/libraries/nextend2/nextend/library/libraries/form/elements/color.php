<?php
N2Loader::import('libraries.form.elements.text');

class N2ElementColor extends N2ElementText {

    protected $alpha = false;

    protected $class = 'n2-form-element-color ';

    protected function fetchElement() {

        if ($this->alpha) {
            $this->class .= 'n2-form-element-color-alpha ';
        }

        $html = parent::fetchElement();
        N2JS::addInline('new N2Classes.FormElementColor("' . $this->fieldID . '", ' . intval($this->alpha) . ');');

        return $html;
    }

    protected function pre() {
        return '<div class="n2-sp-replacer"><div class="n2-sp-preview"><div class="n2-sp-preview-inner" style="background-color: rgb(62, 62, 62);"></div></div><div class="n2-sp-dd">&#9650;</div></div>';
    }

    protected function post() {
        return '';
    }

    /**
     * @param boolean $alpha
     */
    public function setAlpha($alpha) {
        $this->alpha = $alpha;
    }
}
