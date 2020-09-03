<?php


namespace Nextend;


use JEventDispatcher;
use JFactory;
use Nextend\Framework\Pattern\GetPathTrait;
use Nextend\Framework\Pattern\SingletonTrait;

class Nextend {

    use GetPathTrait;
    use SingletonTrait;

    protected function init() {
        if (class_exists('\JEventDispatcher', false)) {
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onInitN2Library');
        } else {
            // Joomla 4
            JFactory::getApplication()
                     ->triggerEvent('onInitN2Library');
        }
    
    }
}