<?php
N2Loader::import('libraries.backgroundanimation.storage', 'smartslider');

class N2BackgroundAnimationManager
{

    public static function init() {
        static $inited = false;
        if (!$inited) {

            N2Pluggable::addAction('afterApplicationContent', 'N2BackgroundAnimationManager::load');
            $inited = true;
        }
    }

    public static function load() {
        N2Base::getApplication('system')->getApplicationType('backend');
        N2Base::getApplication('smartslider')->getApplicationType('backend')->run(array(
            'useRequest' => false,
            'controller' => 'backgroundanimation',
            'action'     => 'index'
        ));
    }
}

N2BackgroundAnimationManager::init();