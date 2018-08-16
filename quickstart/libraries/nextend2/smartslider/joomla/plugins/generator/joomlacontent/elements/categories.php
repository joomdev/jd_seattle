<?php

N2Loader::import('libraries.form.element.list');

class N2ElementJoomlaCategories extends N2ElementList {

    public function __construct($parent, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($parent, $name, $label, $default, $parameters);

        $db = JFactory::getDBO();

        $query = 'SELECT
                    m.id, 
                    m.title AS name, 
                    m.title, 
                    m.parent_id AS parent, 
                    m.parent_id
                FROM #__categories m
                WHERE m.published = 1 AND (m.extension = "com_content" OR m.extension = "system")
                ORDER BY m.lft';


        $db->setQuery($query);
        $menuItems = $db->loadObjectList();
        $children  = array();
        if ($menuItems) {
            foreach ($menuItems as $v) {
                $pt   = $v->parent_id;
                $list = isset($children[$pt]) ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }

        $this->options['0'] = n2_('All');

        jimport('joomla.html.html.menu');
        $options = JHTML::_('menu.treerecurse', 1, '', array(), $children, 9999, 0, 0);
        if (count($options)) {
            foreach ($options AS $option) {
                $this->options[$option->id] = $option->treename;
            }
        }
        if ($this->getValue() == '') {
            reset($this->options);
            $this->setValue(key($this->options));
        }

    }

}
