<?php
N2Loader::import('libraries.form.elements.subformImage');

class N2ElementSliderType extends N2ElementSubformImage {

    /** @var N2SSPluginSliderType[] */
    protected $plugins = array();

    function renderSelector() {

        N2JS::addInline('new N2Classes.FormElementSliderType("' . $this->fieldID . '");');

        return parent::renderSelector();
    }

    protected function loadOptions() {

        $this->plugins = N2SSPluginSliderType::getSliderTypes();

        $options = array();
        foreach ($this->plugins AS $name => $type) {
            $options[$name] = $type->getLabel();
        }

        $this->setOptions($options);
    }
}