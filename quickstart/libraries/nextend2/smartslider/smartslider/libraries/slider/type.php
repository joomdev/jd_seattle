<?php

abstract class N2SmartSliderType {

    /**
     * @var N2SmartSliderAbstract
     */
    protected $slider;

    protected $jsDependency = array(
        'nextend-frontend',
        'smartslider-frontend'
    );

    protected $javaScriptProperties;

    /** @var  N2SmartSliderWidgets */
    protected $widgets;

    protected $shapeDividerAdded = false;

    protected $style = '';

    public function __construct($slider) {
        $this->slider = $slider;
        $this->jsDependency[] = 'nextend-gsap';
    

        if ($slider->isAdmin) {
            $this->jsDependency[] = 'documentReady';
        }
    }

    public static function getItemDefaults() {
        return array();
    }

    /**
     * @param N2SmartSliderCSSAbstract $css
     *
     * @return string
     */
    public function render($css) {

        $this->javaScriptProperties = $this->slider->features->generateJSProperties();

        $this->widgets = new N2SmartSliderWidgets($this->slider);

        ob_start();
        $this->renderType($css);

        return ob_get_clean();
    }

    /**
     * @param N2SmartSliderCSSAbstract $css
     *
     * @return string
     */
    protected abstract function renderType($css);

    protected function getSliderClasses() {
        return $this->slider->features->fadeOnLoad->getSliderClass();
    }

    protected function openSliderElement() {
        return N2Html::openTag('div', array(
                'id'           => $this->slider->elementId,
                'data-creator' => 'Smart Slider 3',
                'class'        => 'n2-ss-slider n2-ow n2-has-hover n2notransition ' . $this->getSliderClasses(),

            ) + $this->getFontSizeAttributes());
    }

    private function getFontSizeAttributes() {

        return $this->slider->features->responsive->getMinimumFontSizeAttributes() + array(
                'style'         => "font-size: " . $this->slider->fontSize . "px;",
                'data-fontsize' => $this->slider->fontSize
            );
    }

    public function getDefaults() {
        return array();
    }

    /**
     * @param $params N2Data
     */
    public function limitParams($params) {

    }

    protected function initParticleJS() {
    }

    protected function renderShapeDividers() {
    }

    private function renderShapeDivider($side, $params) {
    }

    /**
     * @return string
     */
    public function getScript() {
        return '';
    }

    public function getStyle() {
        return $this->style;
    }

    public function setJavaScriptProperty($key, $value) {
        $this->javaScriptProperties[$key] = $value;
    }
}