<?php

namespace Nextend\SmartSlider3\Generator;

use Nextend\Framework\Pattern\GetPathTrait;
use Nextend\Framework\Url\Url;

abstract class AbstractGeneratorGroup {

    use GetPathTrait;

    protected $name = '';

    /** @var AbstractGeneratorGroupConfiguration */
    protected $configuration;

    protected $needConfiguration = false;

    protected $url = '';

    /** @var AbstractGenerator[] */
    protected $sources = array();

    protected $isLoaded = false;

    protected $isDeprecated = false;

    public function __construct() {

        GeneratorFactory::addGenerator($this);
    }

    /**
     * @return AbstractGeneratorGroup $this
     */
    public function load() {
        if (!$this->isLoaded) {
            if ($this->isInstalled()) {
                $this->loadSources();
            }
            $this->isLoaded = true;
        }

        return $this;
    }

    protected abstract function loadSources();

    public function addSource($name, $source) {
        $this->sources[$name] = $source;
    }

    /**
     * @param $name
     *
     * @return false|AbstractGenerator
     */
    public function getSource($name) {
        if (!isset($this->sources[$name])) {
            return false;
        }

        return $this->sources[$name];
    }

    /**
     * @return AbstractGenerator[]
     */
    public function getSources() {
        return $this->sources;
    }

    public function hasConfiguration() {

        return !!$this->configuration;
    }

    /**
     * @return AbstractGeneratorGroupConfiguration
     */
    public function getConfiguration() {

        return $this->configuration;
    }

    /**
     * @return string
     *
     */
    public abstract function getLabel();

    /**
     * @return string
     */
    public function getDescription() {
        return n2_('No description.');
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
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

    public function getImageUrl() {

        return Url::pathToUri(self::getPath() . '/dynamic.png');
    }

    /**
     * @return bool
     */
    public function isDeprecated() {
        return $this->isDeprecated;
    }

}