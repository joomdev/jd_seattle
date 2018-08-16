<?php

N2Loader::import('libraries.parse.parse');

abstract class N2SliderGeneratorPluginAbstract {

    protected $name = '';

    protected $configuration = false;

    protected $needConfiguration = false;

    protected $url = '';

    /** @var N2GeneratorAbstract[] */
    protected $sources = array();

    protected $isLoaded = false;

    /**
     * @return N2SliderGeneratorPluginAbstract $this
     */
    public function load() {
        if (!$this->isLoaded) {
            if ($this->isInstalled()) {
                $this->importGenerators();
                $this->loadSources();
            }
            $this->isLoaded = true;
        }

        return $this;
    }

    protected function loadSources() {

    }

    public function addSource($name, $source) {
        $this->sources[$name] = $source;
    }

    /**
     * @param $name
     *
     * @return false|N2GeneratorAbstract
     */
    public function getSource($name) {
        if (!isset($this->sources[$name])) {
            return false;
        }

        return $this->sources[$name];
    }

    /**
     * @return N2GeneratorAbstract[]
     */
    public function getSources() {
        return $this->sources;
    }

    /**
     * @todo abstract
     */
    protected function initConfiguration() {
    }

    public function getConfiguration() {

        $this->initConfiguration();

        return $this->configuration;
    }

    /**
     * @todo abstract
     * @return string
     */
    public function getPath() {
        return '';
    }

    private function importGenerators() {
        N2Loader::importPathAll($this->getPath() . 'sources');
    }

    /**
     * @todo make abstract
     *
     * @return string
     */
    public function getLabel() {
        return '';
    }

    public function loadElements() {
        $path = $this->getPath() . '/elements/';
        if (N2Filesystem::existsFolder($path)) {
            N2Loader::importPathAll($path);
        }
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }


    public function hasConfiguration() {
        return $this->needConfiguration;
    }

    public function isInstalled() {
        return true;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

}

class N2GeneratorInfo {

    public $group, $title, $path, $installed = true, $type = '', $readMore = '', $hasConfiguration = false, $configurationClass = '';

    private $configuration;

    public static function getInstance($group, $title, $path) {
        return new N2GeneratorInfo($group, $title, $path);
    }

    public function __construct($group, $title, $path) {
        $this->group = $group;
        $this->title = $title;
        $this->path  = $path;
    }

    public function getConfiguration() {
        if (!$this->configuration) {
            require_once $this->path . '/../configuration.php';
            $class               = $this->configurationClass;
            $this->configuration = new $class($this);
        }

        return $this->configuration;
    }

    public function setInstalled($installed = true) {
        $this->installed = $installed;

        return $this;
    }

    public function isInstalled() {
        return $this->installed;
    }

    public function setUrl($url) {
        $this->readMore = $url;

        return $this;
    }

    public function setType($type) {
        $this->type = $type;

        return $this;
    }

    public function setConfiguration($configurationClass) {
        $this->configurationClass = $configurationClass;
        $this->hasConfiguration   = true;

        return $this;
    }

    public function setData($key, $value) {
        $this->{$key} = $value;

        return $this;
    }
}