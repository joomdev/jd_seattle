<?php

class N2StyleManager {

    public static function init() {
        static $inited = false;
        if (!$inited) {

            N2Pluggable::addAction('afterApplicationContent', 'N2StyleManager::load');
            $inited = true;
        }
    }

    public static function load() {
        N2Base::getApplication('system')
              ->getApplicationType('backend')
              ->run(array(
                  'useRequest' => false,
                  'controller' => 'style',
                  'action'     => 'index'
              ));
    }
}

N2StyleManager::init();