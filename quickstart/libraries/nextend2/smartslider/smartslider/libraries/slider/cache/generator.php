<?php

class N2CacheManifestGenerator extends N2CacheManifest {

    /**
     * @var N2SmartSliderAbstract
     */
    private $slider;

    private $generator;

    protected $_storageEngine = 'database';

    /**
     * @param N2SmartSliderAbstract        $slider
     * @param N2SmartSliderSlidesGenerator $generator
     */
    public function __construct($slider, $generator) {
        parent::__construct($slider->cacheId, false);
        $this->slider    = $slider;
        $this->generator = $generator;
    }

    protected function decode($data) {
        return json_decode($data, true);
    }

    protected function isCacheValid(&$manifestData) {
        $nextRefresh = $manifestData['cacheTime'] + max(0, floatval($this->generator->currentGenerator['params']->get('cache-expiration', 1))) * 60 * 60;
        if ($manifestData['cacheTime'] + max(0, floatval($this->generator->currentGenerator['params']->get('cache-expiration', 1))) * 60 * 60 < N2Platform::getTime()) {
            return false;
        }
        $this->generator->setNextCacheRefresh($nextRefresh);

        return true;
    }

    protected function addManifestData(&$manifestData) {
        $manifestData['cacheTime'] = N2Platform::getTime();
        $this->generator->setNextCacheRefresh($manifestData['cacheTime'] + max(0, floatval($this->generator->currentGenerator['params']->get('cache-expiration', 1))) * 60 * 60);
    }
}
