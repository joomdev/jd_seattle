<?php

class N2SmartsliderApplicationTypeFrontend extends N2ApplicationType
{

    public $type = "frontend";

    public function __construct($app, $appTypePath) {
        parent::__construct($app, $appTypePath);

        N2AssetsManager::addCachedGroup('core');
        N2AssetsManager::addCachedGroup('smartslider');
    }

    protected function autoload() {
        N2Loader::import(array(
            'libraries.cache.NextendModuleCache'
        ));

        N2Loader::import(array(
            'libraries.settings.settings'
        ), 'smartslider');
    }
}

