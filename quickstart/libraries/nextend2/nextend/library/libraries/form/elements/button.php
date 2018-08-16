<?php

class N2ElementButton extends N2Element {

    protected $url = '';
    protected $target = '';
    protected $buttonLabel = '';

    public function __construct($parent, $name = '', $label = '', $buttonLabel = '', $parameters = array()) {
        $this->buttonLabel = $buttonLabel;
        parent::__construct($parent, $name, $label, '', $parameters);
    }

    protected function fetchElement() {

        $attributes = array(
            'class' => 'n2-form-element-single-button n2-button n2-button-normal n2-radius-s n2-button-l n2-button-grey n2-uc',
            'id'    => $this->fieldID
        );

        if (!empty($this->url)) {
            $attributes['href'] = $this->url;
            if (!empty($this->target)) {
                $attributes['target'] = $this->target;
            }
            unset($attributes['onclick']);
        } else {
            $attributes['href']    = '#';
            $attributes['onclick'] = 'return false;';
        }

        return N2Html::tag('a', $attributes, $this->buttonLabel);
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setTarget($target) {
        $this->target = $target;
    }
}
