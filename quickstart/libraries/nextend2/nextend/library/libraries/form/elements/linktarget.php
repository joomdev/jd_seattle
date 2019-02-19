<?php

N2Loader::import('libraries.form.elements.list');

class N2ElementLinkTarget extends N2ElementList {


    public function __construct(N2FormElementContainer $parent, $name = '', $label = '', $default = '_self', array $parameters = array()) {
        $this->options = array(
            '_self'   => n2_('Self'),
            '_blank'  => n2_('New'),
            '_parent' => n2_('Parent'),
            '_top'    => n2_('Top')
        );

        parent::__construct($parent, $name, $label, $default, $parameters);
    }
}
