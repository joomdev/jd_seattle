<?php

abstract class N2Application {

    /**
     * @var
     */
    public $name;

    public $info;

    public $path;

    /**
     * @var array
     */
    public $applicationTypes = array();

    private $localStorage = array();

    /**
     * @var N2StorageSection
     */
    public $storage;

    public $router;

    /**
     * @param $info N2ApplicationInfo
     */
    public function __construct($info) {
        $this->info = $info;

        $appRootPath = $info->getPath();

        $this->storage = new N2StorageSection($this->name);

        $this->path = $appRootPath;

        $this->autoload();

        $this->router = new N2Router($info);

        $this->initAssetPath();
    }

    protected function autoload() {

    }

    /**
     * @param $typeName
     *
     * @throws Exception
     */
    private function _createApplicationType($typeName) {
        $className = "N2" . ucfirst($this->name) . "ApplicationType" . ucfirst($typeName);

        if ($this->import($typeName, $className)) {
            $this->applicationTypes[$typeName] = new $className($this, $this->path . NDS . $typeName);
        } else {
            throw new Exception("Application type doesn't exists! Type name: '{$typeName}', Class: '{$className}'");
        }
    }

    /**
     * @param $name
     * @param $className
     *
     * @return mixed
     */
    private function import($name, $className) {
        return include $this->path . NDS . $name . NDS . $className . ".php";
    }

    /**
     * @param $typeName
     *
     * @return N2ApplicationType
     */
    public function getApplicationType($typeName) {

        if (!isset($this->applicationTypes[$typeName])) {
            $this->_createApplicationType($typeName);
        }

        return $this->applicationTypes[$typeName];
    }

    /**
     * Defines a constant which point to the application assets directory.
     * Example constant name: NEXTEND_SMARTSLIDER_ASSETS
     */
    public function initAssetPath() {
        define('NEXTEND_' . strtoupper($this->name) . '_ASSETS', $this->info->getAssetsPath());
    }

    public function set($key, $value) {
        $this->localStorage[$key] = $value;

        return $this;
    }

    public function get($key, $default = null) {
        if (isset($this->localStorage[$key])) {
            return $this->localStorage[$key];
        }

        return $default;
    }

    public function getLogo() {
        return N2Filesystem::pathToAbsoluteURL($this->info->getAssetsPath() . "/admin/images/logo.png");
    }

    public function hasExpertMode() {
        return true;
    }
}