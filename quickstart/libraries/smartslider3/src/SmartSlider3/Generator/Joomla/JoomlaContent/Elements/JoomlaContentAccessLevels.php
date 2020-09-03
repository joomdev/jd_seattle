<?php

namespace Nextend\SmartSlider3\Generator\Joomla\JoomlaContent\Elements;

use JFactory;
use Nextend\Framework\Form\Element\Select;


class JoomlaContentAccessLevels extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

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
