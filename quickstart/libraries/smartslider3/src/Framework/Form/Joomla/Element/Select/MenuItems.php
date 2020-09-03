<?php


namespace Nextend\Framework\Form\Joomla\Element\Select;


use JMenu;
use Nextend\Framework\Form\Element\Select;

class MenuItems extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

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