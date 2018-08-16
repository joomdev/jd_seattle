<?php

N2Loader::import('libraries.form.element.list');

class N2ElementJoomlaAccessLevels extends N2ElementList {

    public function __construct($parent, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($parent, $name, $label, $default, $parameters);

        $db = JFactory::getDBO();

        $query = 'SELECT
                    m.id, 
                    m.title AS name, 
                    m.title, 
                    m.ordering
                FROM #__viewlevels m
                ORDER BY m.ordering';


        $db->setQuery($query);
        $menuItems = $db->loadObjectList();

        $this->options['0'] = n2_('All');

        if (count($menuItems)) {
            foreach ($menuItems AS $option) {
                $this->options[$option->id] = $option->name;
            }
        }
    }

}
