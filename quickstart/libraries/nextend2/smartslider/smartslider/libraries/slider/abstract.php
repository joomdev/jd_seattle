<?php

N2Loader::import('libraries.parse.font');

N2Loader::importAll("libraries.renderable", "smartslider");

N2Loader::import('libraries.slider.type', 'smartslider');
N2Loader::import('libraries.slider.css', 'smartslider');
N2Loader::import('libraries.slider.group', 'smartslider');
N2Loader::importAll('libraries.slider.features', 'smartslider');
N2Loader::importAll("libraries.slider.slides", "smartslider");
N2Loader::import('libraries.settings.settings', 'smartslider');
N2Loader::import('libraries.slider.widget.widgets', 'smartslider');

abstract class N2SmartSliderAbstract extends N2SmartSliderRenderableAbstract {

    public $manifestData = array(
        'generator' => array()
    );

    protected $isGroup = false;

    public $sliderId = 0;

    public $cacheId = '';

    /** @var  N2Data */
    public $data;

    /** @var  N2Data */
    public $params;

    public $disableResponsive = false;

    protected $parameters = null;

    public $fontSize = 16;

    /**
     * @var N2SmartSliderSlides
     */
    public $slidesBuilder;

    /**
     * @var N2SmartSliderSlide[]
     */
    public $slides;

    public $firstSlideIndex = 0;
    /**
     * @var N2MobileDetect
     */
    protected $device;
    /**
     * @var NextendSmartSliderCSS
     */
    public $assets;
    protected $cache = false;

    public static $_identifier = 'n2-ss';

    /** @var N2SmartSliderSlide[] */
    public $staticSlides = array();

    /** @var  N2SmartSliderType */
    protected $sliderType;

    public $staticHtml = '';

    public $isStaticEdited = false;

    private $sliderRow = null;

    public $exposeSlideData = array(
        'title'         => false,
        'description'   => false,
        'thumbnail'     => false,
        'thumbnailType' => false
    );

    public function __construct($sliderId, $parameters) {

        $this->sliderId = $sliderId;

        $this->setElementId();

        if ($this->isAdmin) {
            $this->cacheId = self::getAdminCacheId($this->sliderId);
        } else {
            $this->cacheId = self::getCacheId($this->sliderId);
        }

        $this->parameters = array_merge(array(
            'extend'                => array(),
            'disableResponsive'     => false,
            'addDummySlidesIfEmpty' => false
        ), $parameters);

        $this->disableResponsive = $this->parameters['disableResponsive'];

        N2Loader::import("models.Sliders", "smartslider");

    }

    public function setElementId() {
        $this->elementId = self::$_identifier . '-' . $this->sliderId;
    }

    public static function getCacheId($sliderId) {
        return self::$_identifier . '-' . $sliderId;
    }

    public static function getAdminCacheId($sliderId) {
        return self::$_identifier . '-admin-' . $sliderId;
    }

    public function getAlias() {
        return $this->data->get('alias', '');
    }

    public function getSliderTypeResource($resourceName) {

        $type = $this->data->get('type', 'simple');

        $class = 'N2SmartSlider' . $resourceName . $type;

        if (!class_exists($class, false)) {
            try {
                N2Loader::importPath(N2SSPluginSliderType::getSliderType($type)
                                                         ->getPath() . $resourceName);
            } catch (Exception $e) {
                if ($type != 'simple') {
                    $this->data->set('type', 'simple');

                    return $this->getSliderTypeResource($resourceName);
                }

                throw $e;
            }
        }

        return new $class($this);
    }

    abstract public function parseSlider($slider);

    public function loadSliderParams() {

        $slidersModel = new N2SmartsliderSlidersModel();
        $slider       = $slidersModel->get($this->sliderId);
        if (empty($slider)) {
            return false;
        }
        $this->data   = new N2Data($slider);
        $this->params = new N2Data($slider['params'], true);
    }

    public function getSliderFromDB() {
        if ($this->sliderRow === null) {
            $slidersModel    = new N2SmartsliderSlidersModel();
            $this->sliderRow = $slidersModel->get($this->sliderId);

            if (empty($this->sliderRow)) {
                $this->sliderRow = false;
            } else {

                if (isset($this->parameters['extend']['sliderData']) && is_array($this->parameters['extend']['sliderData'])) {
                    $sliderData               = $this->parameters['extend']['sliderData'];
                    $this->sliderRow['title'] = $sliderData['title'];
                    unset($sliderData['title']);
                    $this->sliderRow['type'] = $sliderData['type'];
                    unset($sliderData['type']);

                    $this->data   = new N2Data($this->sliderRow);
                    $this->params = new N2Data($sliderData);
                } else {
                    $this->data   = new N2Data($this->sliderRow);
                    $this->params = new N2Data($this->sliderRow['params'], true);
                }
            }
        }

        return $this->sliderRow;
    }

    private function loadSlider() {

        $sliderRow = $this->getSliderFromDB();
        if (empty($sliderRow)) {
            return false;
        }

        switch ($sliderRow['type']) {
            case 'group':
                $this->isGroup = true;
                break;
        }

        $this->sliderType = $this->getSliderTypeResource('type');
        $defaults         = $this->sliderType->getDefaults();

        $parallaxOverlap = $this->params->get('animation-parallax-overlap', false);

        if ($parallaxOverlap === false) {
            $animationParallax = $this->params->get('animation-parallax', false);
            if ($animationParallax !== false) {
                $parallaxOverlap = 100 - floatval($animationParallax) * 100;
            } else {
                $parallaxOverlap = 0;
            }
            $this->params->set('animation-parallax-overlap', $parallaxOverlap);
            $this->params->un_set('animation-parallax');
        }

        $this->params->fillDefault($defaults);
        $this->sliderType->limitParams($this->params);

        if (!$this->isGroup) {
            $this->features = new N2SmartSliderFeatures($this);

            $this->initSlides();
        }

        return true;
    }

    private function initSlides() {
        if ($this->isAdmin) {
            N2Loader::importAll("libraries.slider.slides.admin", "smartslider");
            $this->slidesBuilder = new N2SmartSliderSlidesAdmin($this);
        } else {
            $this->slidesBuilder = new N2SmartSliderSlides($this);
        }
        $this->slides = $this->slidesBuilder->getSlides(isset($this->parameters['extend']) ? $this->parameters['extend'] : array(), $this->parameters['addDummySlidesIfEmpty']);
    }

    public function getNextCacheRefresh() {
        if ($this->isGroup) {
            return $this->sliderType->getNextCacheRefresh();
        }

        return $this->slidesBuilder->getNextCacheRefresh();
    }

    public function render() {

        if (!$this->loadSlider()) {
            return false;
        }

        if (!$this->isGroup && count($this->slides) == 0) {
            return false;
        }
        $this->assets = $this->getSliderTypeResource('css');

        if (!$this->isGroup) {
            $this->slides[$this->firstSlideIndex]->setFirst();
            for ($i = 0; $i < count($this->slides); $i++) {
                $this->slides[$i]->prepare();
                $this->slides[$i]->setSlidesParams();
            }

            $this->renderStaticSlide();
        }
        $slider = $this->sliderType->render($this->assets);

        $slider = str_replace('n2-ss-0', $this->elementId, $slider);
        if (!N2Platform::$isAdmin) {
            $rocketAttributes = '';
            $dependency       = max(0, intval($this->params->get('dependency')));
            if ($dependency && ($dependency != $this->sliderId)) {
                $rocketAttributes .= 'data-dependency="' . $dependency . '"';
            } else {
                $delay = max(0, intval($this->params->get('delay'), 0));
                if ($delay > 0) {
                    $rocketAttributes .= 'data-delay="' . $delay . '"';
                }
            }

            if (!empty($rocketAttributes)) {
                $slider = '<template id="' . $this->elementId . '" ' . $rocketAttributes . '>' . $slider . '</template>';
            }
        }
        if (!$this->isGroup) {
            $slider = $this->features->translateUrl->renderSlider($slider);

            $slider = $this->features->loadSpinner->renderSlider($this, $slider);
            $slider = $this->features->align->renderSlider($slider, $this->assets->sizes['width']);
            $slider = $this->features->margin->renderSlider($slider);


            $style = $this->sliderType->getStyle();
            if (N2Platform::$isAdmin) {
                $slider = '<style type="text/css">' . $style . '</style>' . $slider;
            } else {
                $cssMode = N2Settings::get('css-mode', 'normal');
                switch ($cssMode) {
                    case 'inline':
                        N2CSS::addInline($style);
                        break;
                    case 'async':
                        $this->sliderType->setJavaScriptProperty('css', $style);
                        break;
                    default:
                        $slider = '<style>' . $style . '</style>' . $slider;
                        break;
                }
            }

            $jsInlineMode = N2Settings::get('javascript-inline', 'head');
            switch ($jsInlineMode) {
                case 'body':
                    $slider .= N2Html::script($this->sliderType->getScript());
                    break;
                case 'head':
                default:
                    N2JS::addInline($this->sliderType->getScript());
                    break;
            }


            $slider .= $this->features->fadeOnLoad->renderPlaceholder($this->assets->sizes);
        }

        $alias = $this->data->get('alias', '');
        if (intval($this->params->get('alias-id', 0)) && !empty($alias)) {
            $aliasHTML = '<div id="' . $alias . '" style="height:0; line-height:0; min-height:0; margin:0; padding:0;"></div>';
            if (intval($this->params->get('alias-slideswitch', 0))) {
                $slide_count = count($this->slides);
                for ($i = 1; $i < $slide_count + 1; $i++) {
                    $aliasHTML .= '<div id="' . $alias . '-' . $i . '" style="height:0; line-height:0; min-height:0; margin:0; padding:0;"></div>';
                }
                $aliasJS = '
                var anchor = window.location.href.split("#")[1];
                var slide = null;
                if(typeof anchor === \'undefined\' || anchor.indexOf("-") > -1){
                    var url_vars = {};
                    var url_parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                        url_vars[key] = value;
                    });
                    if(typeof url_vars[\'' . $alias . '\']!==\'undefined\'){
                        slide = parseInt(url_vars[\'' . $alias . '\'])-1;
                    }
                }
                if((typeof anchor !== \'undefined\' && anchor.indexOf("-") > -1) || slide != null){
                    if(slide == null){
                        var anchorparts = anchor.split("-");
                        slide = parseInt(anchorparts[anchorparts.length - 1])-1;
                    }
                    N2R(\'windowLoad\',function($){
                        N2R("#n2-ss-' . $this->sliderId . '", function($, slider){
                            slider.slide(slide);
                        });
                    });
                }';
                if (intval($this->params->get('alias-slideswitch', 0))) {
                    $aliasJS .= '
                    N2R(\'windowLoad\',function($){';
                    $speed   = N2SmartSliderSettings::get('smooth-scroll-speed', 400);
                    for ($i = 1; $i < $slide_count + 1; $i++) {
                        $aliasJS .= '$("a[href=\'#' . $alias . '-' . $i . '\']").click(function(){
                                        N2R("#n2-ss-' . $this->sliderId . '", function($, slider){
                                            slider.slide(' . ($i - 1) . ');
                                        });';
                        if (intval($this->params->get('alias-smoothscroll', 0))) {
                            $aliasJS .= '$("html, body").animate({scrollTop:$("#' . $alias . '-' . $i . '").offset().top}, ' . $speed . ')';
                        }
                        $aliasJS .= '});';
                    }
                    $aliasJS .= '});';
                }
            }
            if (intval($this->params->get('alias-smoothscroll', 0))) {
                $speed   = N2SmartSliderSettings::get('smooth-scroll-speed', 400);
                $aliasJS .= '
                N2R(\'windowLoad\',function($){
                    $("a[href=\'#' . $alias . '\']").click(function(){
                        $("html, body").animate({scrollTop:$("#' . $alias . '").offset().top}, ' . $speed . ');
                    });
                });';
            }
            $slider = $aliasHTML . $slider;
            N2JS::addInline($aliasJS);
        }

        $slider = N2Html::tag("div", array('class' => 'n2-section-smartslider ' . $this->params->get('classes', '')), $slider);

        if (intval($this->params->get('clear-both', 0))) {
            $slider = '<div class="n2-clear"></div>' . $slider;
        }

        if (!$this->params->get('optimize-jetpack-photon', 0)) {
            N2AssetsManager::$image->add($this->images);
        }

        return $slider;
    }

    public function addStaticSlide($slide) {
        $this->staticSlides[] = $slide;
    }

    public function renderStaticSlide() {
        $this->staticHtml = '';
        if (count($this->staticSlides)) {
            for ($i = 0; $i < count($this->staticSlides); $i++) {
                $this->staticHtml .= $this->staticSlides[$i]->getAsStatic();
            }
        }
    }

    /**
     * @return N2SmartSliderSlide
     */
    public function getPreviousSlide() {
        $length = count($this->slides);

        if ($this->firstSlideIndex == 0) {
            return $this->slides[$length - 1];
        }

        return $this->slides[$this->firstSlideIndex - 1];
    }

    /**
     * @return N2SmartSliderSlide
     */
    public function getNextSlide() {
        $length = count($this->slides);
        if ($this->firstSlideIndex == $length - 1) {
            return $this->slides[0];
        }

        return $this->slides[$this->firstSlideIndex + 1];
    }

    public static function removeShortcode($content) {
        $content = preg_replace('/smartslider3\[([0-9]+)\]/', '', $content);
        $content = preg_replace('/\[smartslider3 slider="([0-9]+)"\]/', '', $content);
        $content = preg_replace('/\[smartslider3 slider=([0-9]+)\]/', '', $content);

        return $content;
    }

    public function setStatic($isStaticEdited) {
        $this->isStaticEdited = $isStaticEdited;
    }

    public function canDisplayOnCurrentDevice() {
        if ($this->getSliderFromDB()) {
            N2Loader::import('libraries.mobiledetect');

            if (N2MobileDetect::$current['isMobile'] && $this->params->get('mobile', '1') == '0') {
                return false;
            }

            if (N2MobileDetect::$current['isTablet'] && $this->params->get('tablet', '1') == '0') {
                return false;
            }

            if (N2MobileDetect::$current['isDesktop'] && $this->params->get('desktop', '1') == '0') {
                return false;
            }
        }

        return true;
    }
}

N2Loader::import("libraries.slider.slider", "smartslider.platform");


class N2SmartSliderSliderBehavior {

}