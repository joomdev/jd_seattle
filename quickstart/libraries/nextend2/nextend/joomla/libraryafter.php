<?php

if (class_exists('JEventDispatcher', false)) {
    $dispatcher = JEventDispatcher::getInstance();
    $dispatcher->trigger('onInitN2Library');
} else {
    // Joomla 4
    JFactory::getApplication()->triggerEvent('onInitN2Library');
}
