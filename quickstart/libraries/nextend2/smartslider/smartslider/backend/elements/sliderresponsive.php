<?php
N2Loader::import('libraries.form.elements.subformImage');

class N2ElementSliderResponsive extends N2ElementSubformImage {

    /** @var N2SSPluginSliderResponsive[] */
    protected $plugins = array();

    protected function loadOptions() {

        $this->plugins = N2SSPluginSliderResponsive::getTypes();
        uasort($this->plugins, 'N2ElementSliderResponsive::sortTypes');

        $options = array();
        foreach ($this->plugins AS $name => $type) {
            $options[$name] = $type->getLabel();
        }

        $this->setOptions($options);
    }

    public static function sortTypes($a, $b) {
        return $a->ordering - $b->ordering;
    }
}