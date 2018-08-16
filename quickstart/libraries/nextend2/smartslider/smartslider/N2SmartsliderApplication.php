<?php
N2Loader::import("smartslider3", "smartslider");

class N2SmartSliderApplication extends N2Application {

    public $name = "smartslider";

    protected function autoload() {
        N2Loader::importAll('libraries.plugins', 'smartslider');
        N2Loader::import("libraries.slider.helper", "smartslider");
        N2Loader::import("libraries.slider.manager", "smartslider");

        N2Filesystem::registerTranslate(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'plugins', $this->info->getAssetsPath() . '/plugins');
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'loadplugin.php';

        N2Loader::import('plugins.loadplugin', 'smartslider.platform');

        N2Pluggable::doAction('n2_ss_plugins_loaded');

        N2Loader::import('libraries.link', 'smartslider');
    }

    public function hasExpertMode() {
        return false;
    
    }
}