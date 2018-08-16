<?php

N2Loader::import('libraries.form.element.list');

class N2ElementJoomlaTags extends N2ElementList {

    public function __construct($parent, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($parent, $name, $label, $default, $parameters);

        $db = JFactory::getDBO();

        $query = 'SELECT id, title FROM #__tags WHERE published = 1 ORDER BY id';

        $db->setQuery($query);
        $menuItems = $db->loadObjectList();

        $this->options['0'] = n2_('All');

        if (count($menuItems)) {
            array_shift($menuItems);
            foreach ($menuItems AS $option) {
                $this->options[$option->id] = $option->title;
            }
        }
    }

}
