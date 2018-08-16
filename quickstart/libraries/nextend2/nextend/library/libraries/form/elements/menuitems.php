<?php
N2Loader::import('libraries.form.elements.list');

class N2ElementMenuItems extends N2ElementList {

    public function __construct($parent, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($parent, $name, $label, $default, $parameters);

        $menu      = JMenu::getInstance('site');
        $menuItems = $menu->getItems($attributes = array(), $values = array());

        $this->options['0'] = n2_('Default');

        if (count($menuItems)) {
            foreach ($menuItems AS $item) {
                $this->options[$item->id] = '[' . $item->id . '] ' . $item->title;
            }
        }
    }

}
