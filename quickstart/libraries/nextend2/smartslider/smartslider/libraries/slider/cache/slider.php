<?php

class N2CacheManifestSlider extends N2CacheManifest {

    private $parameters = array();

    protected $_storageEngine = 'database';

    private $isExtended = false;

    public function __construct($cacheId, $parameters = array()) {
        parent::__construct($cacheId, false);
        $this->parameters = $parameters;

    }

    protected function decode($data) {
        return json_decode($data, true);
    }

    public function makeCache($fileName, $hash, $callable) {

        $variations = 1;
        if ($this->exists($this->getManifestKey('variations'))) {
            $variations = intval($this->get($this->getManifestKey('variations')));
        }
        $fileName = $fileName . mt_rand(1, $variations);

        if (N2SmartSliderSettings::get('serversidemobiledetect', '0') == '1') {
            N2Loader::import('libraries.mobiledetect');

            if (N2MobileDetect::$current['isMobile']) {
                $fileName .= '-mobile';
            } else if (N2MobileDetect::$current['isTablet']) {
                $fileName .= '-tablet';
            } else {
                $fileName .= '-desktop';
            }
        }

        if ($this->exists($this->getManifestKey('data'))) {
            $data     = json_decode($this->get($this->getManifestKey('data')), true);
            $fileName = $this->extendFileName($fileName, $data);
        } else {
            $this->clearCurrentGroup();
        }

        $output = parent::makeCache($fileName, $hash, $callable);

        return $output;
    }

    protected function createCacheFile($fileName, $hash, $content) {

        $this->set($this->getManifestKey('data'), json_encode($this->parameters['slider']->manifestData));

        $fileName = $this->extendFileName($fileName, $this->parameters['slider']->manifestData);

        return parent::createCacheFile($fileName, $hash, $content);
    }

    private function extendFileName($fileName, $manifestData) {

        if ($this->isExtended) {
            return $fileName;
        }

        $this->isExtended = true;

        $generators = $manifestData['generator'];

        if (count($generators)) {
            N2Loader::import("models.generator", "smartslider");
            $generatorModel = new N2SmartsliderGeneratorModel();

            foreach ($generators AS $generator) {
                list($group, $type, $params) = $generator;

                $fileName .= call_user_func_array(array(
                    $generatorModel->getGeneratorGroup($group)
                                   ->getSource($type),
                    'cacheKey'
                ), $params);
            }
        }

        return $fileName;
    }

    protected function isCacheValid(&$manifestData) {

        if (!isset($manifestData['version']) || $manifestData['version'] != N2SS3::$version) {
            return false;
        }

        if (N2SmartSliderHelper::getInstance()
                               ->isSliderChanged($this->parameters['slider']->sliderId, 1)) {
            $this->clearCurrentGroup();
            N2SmartSliderHelper::getInstance()
                               ->setSliderChanged($this->parameters['slider']->sliderId, 0);

            return false;
        }

        $time = N2Platform::getTime();

        if ($manifestData['nextCacheRefresh'] < $time) {
            return false;
        }

        if (!isset($manifestData['currentPath']) || $manifestData['currentPath'] != md5(__FILE__)) {
            return false;
        }

        return true;
    }

    protected function addManifestData(&$manifestData) {

        $manifestData['nextCacheRefresh'] = N2Pluggable::applyFilters('SSNextCacheRefresh', $this->parameters['slider']->getNextCacheRefresh(), array($this->parameters['slider']));
        $manifestData['currentPath']      = md5(__FILE__);
        $manifestData['version']          = N2SS3::$version;

        $variations = 1;

        $params = $this->parameters['slider']->params;
        if (!$params->get('randomize-cache', 0) && ($params->get('randomize', 0) || $params->get('randomizeFirst', 0))) {
            $variations = intval($params->get('variations', 5));
            if ($variations < 1) {
                $variations = 1;
            }
        }

        $this->set($this->getManifestKey('variations'), $variations);
    }
}