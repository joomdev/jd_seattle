<?php
N2Loader::import('libraries.animations.renderer');

class N2AnimationManager {

    public static function init() {
        static $inited = false;
        if (!$inited) {

            N2Pluggable::addAction('afterApplicationContent', 'N2AnimationManager::load');
            $inited = true;
        }
    }

    public static function load() {
        N2Base::getApplication('system')
              ->getApplicationType('backend')
              ->run(array(
                  'useRequest' => false,
                  'controller' => 'animation',
                  'action'     => 'index'
              ));
    }
}

N2AnimationManager::init();