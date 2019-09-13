<?php

N2Loader::import('libraries.mvc.applicationInfo');

class N2SystemApplicationInfo extends N2ApplicationInfo {

    public function __construct() {
        $this->path      = dirname(__FILE__);
        $this->assetPath = realpath(N2LIBRARYASSETS);
        parent::__construct();
    }

    public function isPublic() {
        return false;
    }

    public function getName() {
        return 'system';
    }

    public function getLabel() {
        return 'Nextend system application';
    }

    public function getInstance() {
        require_once $this->path . NDS . "N2SystemApplication.php";

        return new N2SystemApplication($this);
    }

    public function getPathKey() {
        return '$system$';
    }

    public function assetsBackend() {

        $path = $this->getAssetsPath();
        N2JS::addStaticGroup($path . "/dist/system-backend.min.js", "system-backend");
    
    }

    public function assetsFrontend($force = false) {

    }
}


return new N2SystemApplicationInfo();