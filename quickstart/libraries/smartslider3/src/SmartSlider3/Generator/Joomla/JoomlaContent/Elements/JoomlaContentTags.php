<?php

namespace Nextend\SmartSlider3\Generator\Joomla\JoomlaContent\Elements;

use JFactory;
use Nextend\Framework\Form\Element\Select;


class JoomlaContentTags extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $db = JFactory::getDBO();

        $query = 'SELECT id, title FROM #__tags WHERE published = 1 ORDER BY title';

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
