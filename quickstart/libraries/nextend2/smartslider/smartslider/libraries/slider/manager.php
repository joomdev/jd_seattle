<?php

class N2SmartSliderManager {

    private $hasError = false;

    protected $usage = 'Unknown';

    public $slider;

    public $nextCacheRefresh;

    public function __construct($sliderIDorAlias, $backend = false, $parameters = array()) {
        $sliderID = false;

        if (!is_numeric($sliderIDorAlias)) {
            $model  = new N2SmartsliderSlidersModel();
            $slider = $model->getByAlias($sliderIDorAlias);
            if ($slider) {
                $sliderID = intval($slider['id']);
            }
        } else {
            $sliderID = intval($sliderIDorAlias);
        }

        if ($sliderID) {
            if ($backend) {
                N2Loader::import("libraries.slider.backend", "smartslider");
                $this->slider = new N2SmartSliderBackend($sliderID, $parameters);
            } else {
                N2Loader::import("libraries.slider.abstract", "smartslider");
                $this->slider = new N2SmartSlider($sliderID, $parameters);
            }

            N2AssetsManager::addCachedGroup($this->slider->cacheId);
        } else {
            $this->hasError = true;
        }
    }

    public function setUsage($usage) {
        $this->usage = $usage;
    }

    public function getSlider() {
        return $this->slider;
    }

    public function render($cache = false) {
        if ($this->hasError) {
            return '';
        }

        if (!N2Platform::$isAdmin && N2SmartSliderSettings::get('serversidemobiledetect', '0') == '1') {
            if (!$this->slider->canDisplayOnCurrentDevice()) {
                return '';
            }
        }
        if (!$cache) {
            return $this->slider->render();
        }
        N2Loader::import("libraries.slider.cache.slider", "smartslider");

        return N2SmartSlider::addCMSFunctions($this->cacheSlider());
    }

    private function cacheSlider() {
        $cache = new N2CacheManifestSlider($this->slider->cacheId, array(
            'slider' => $this->slider
        ));

        $cachedSlider = $cache->makeCache('slider' . N2Translation::getCurrentLocale(), '', array(
            $this,
            'renderCachedSlider'
        ));

        $this->nextCacheRefresh = $cache->getData('nextCacheRefresh', false);

        if ($cachedSlider === false) {
            return '<!--Smart Slider #' . $this->slider->sliderId . ' does NOT EXIST or has NO SLIDES!' . $this->usage . '-->';
        }
        N2AssetsManager::loadFromArray($cachedSlider['assets']);

        return $cachedSlider['html'];
    }

    public function renderCachedSlider() {
        N2AssetsManager::createStack();

        $content         = array();
        $content['html'] = $this->slider->render();

        $assets = N2AssetsManager::removeStack();

        if ($content['html'] === false) {
            return false;
        }

        $content['assets'] = $assets;

        return $content;
    }
}