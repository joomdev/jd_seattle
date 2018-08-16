<?php

N2Loader::import('libraries.form.elements.text');

class N2ElementJoomlaComponentOptions extends N2ElementText {

    protected $component = '';

    protected function fetchElement() {
        JHTML::_('behavior.modal');
        $html = '<a class="nextend-configurator-button modal" rel="{handler: \'iframe\', size: {x: 875, y: 550}}" href="index.php?option=com_config&view=component&component=' . $this->component . '&tmpl=component">Configure</a>';

        return $html;
    }

    /**
     * @param string $component
     */
    public function setComponent($component) {
        $this->component = $component;
    }

}
