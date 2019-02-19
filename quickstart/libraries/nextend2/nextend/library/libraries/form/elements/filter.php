<?php
N2Loader::import('libraries.form.elements.list');

class N2ElementFilter extends N2ElementList {

    public function __construct($parent, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($parent, $name, $label, $default, $parameters);

        $no_label = strtolower($this->label);

        $this->options = array(
            '0'  => n2_('All'),
            '1'  => $this->label,
            '-1' => sprintf(n2_('Not %s'), $no_label)
        );
    }
}