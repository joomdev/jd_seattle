<?php


abstract class N2ApplicationInfo {

    private $acl = '';
    private $url = '';

    protected $path = '';
    protected $assetPath = '';

    public function __construct() {

        N2Loader::addPath($this->getName(), $this->getPath());
        $platformPath = N2Filesystem::realpath($this->getPath() . '/../' . N2Platform::getPlatform());
        if ($platformPath) {
            N2Loader::addPath($this->getName() . '.platform', $platformPath);
        }
        $this->loadLocale();

        $filterClass = 'N2' . ucfirst($this->getName()) . 'ApplicationInfoFilter';
        N2Loader::import($filterClass, $this->getName() . '.platform');
        $callable = $filterClass . '::filter';
        if (is_callable($callable)) {
            call_user_func($filterClass . '::filter', $this);
        }
    }

    public function loadLocale() {
        static $loaded;
        if ($loaded == null) {
            N2Localization::load_plugin_textdomain($this->getPath());
            $loaded = true;
        }
    }

    public function onReady() {
        N2Loader::import('libraries.image.helper');
        N2ImageHelper::addKeyword($this->getPathKey(), $this->getAssetsPath(), $this->getUri());
    }

    public abstract function isPublic();

    public abstract function getLabel();

    public abstract function getName();

    public function getUrl() {
        return $this->url;
    }

    public function getAcl() {
        return $this->acl;
    }

    public function setAcl($acl) {
        $this->acl = $acl;
    }

    /**
     * @return N2Application
     */
    public abstract function getInstance();

    public abstract function getPathKey();

    public function getUri() {
        return N2Uri::pathToUri($this->getAssetsPath());
    }

    public function assetsBackend() {

    }

    public function assetsFrontend($force = false) {

    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setAssetsPath($path) {
        $this->assetPath = $path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getAssetsPath() {
        return $this->assetPath;
    }

    public function getPath() {
        return $this->path;
    }
}
