<?php


namespace Nextend\SmartSlider3\Slider\SliderType;


use Nextend\Framework\Asset\Builder\BuilderJs;
use Nextend\Framework\Asset\Js\Js;
use Nextend\Framework\Data\Data;
use Nextend\Framework\Filesystem\Filesystem;
use Nextend\Framework\Parser\Color;
use Nextend\Framework\ResourceTranslator\ResourceTranslator;
use Nextend\Framework\View\Html;
use Nextend\SmartSlider3\Application\Frontend\ApplicationTypeFrontend;
use Nextend\SmartSlider3\Slider\Slider;
use Nextend\SmartSlider3\Widget\SliderWidget;

abstract class AbstractSliderTypeFrontend {

    /**
     * @var Slider
     */
    protected $slider;

    protected $jsDependency = array(
        'documentReady',
        'smartslider-frontend'
    );

    protected $javaScriptProperties;

    /** @var  SliderWidget */
    protected $widgets;

    protected $shapeDividerAdded = false;

    protected $style = '';

    public function __construct($slider) {
        $this->slider = $slider;
        $this->jsDependency[] = 'nextend-gsap';
    

        $this->enqueueAssets();
    }

    /**
     * @param AbstractSliderTypeCss $css
     *
     * @return string
     */
    public function render($css) {

        $this->javaScriptProperties = $this->slider->features->generateJSProperties();

        $this->widgets = new SliderWidget($this->slider);

        ob_start();
        $this->renderType($css);

        return ob_get_clean();
    }

    /**
     * @param AbstractSliderTypeCss $css
     *
     * @return string
     */
    protected abstract function renderType($css);

    protected function getSliderClasses() {
        $alias      = $this->slider->getAlias();
        $fadeOnLoad = $this->slider->features->fadeOnLoad->getSliderClass();

        return $alias . ' ' . $fadeOnLoad;
    }

    protected function openSliderElement() {

        $attributes = array(
                'id'           => $this->slider->elementId,
                'data-creator' => 'Smart Slider 3',
                'class'        => 'n2-ss-slider n2-ow n2-has-hover n2notransition ' . $this->getSliderClasses(),

            ) + $this->getFontSizeAttributes();

        $alias = $this->slider->getAlias();
        if (!empty($alias)) {
            $attributes['data-alias'] = $alias;
        }

        return Html::openTag('div', $attributes);
    }

    protected function closeSliderElement() {

        return '</div>';
    }

    private function getFontSizeAttributes() {

        return array(
            'style'         => "font-size: 1rem;",
            'data-fontsize' => $this->slider->fontSize
        );
    }

    public function getDefaults() {
        return array();
    }

    /**
     * @param $params Data
     */
    public function limitParams($params) {

    }

    protected function encodeJavaScriptProperties() {

        $initCallback = implode($this->javaScriptProperties['initCallbacks']);
        unset($this->javaScriptProperties['initCallbacks']);

        $encoded = array();
        foreach ($this->javaScriptProperties as $k => $v) {
            $encoded[] = '"' . $k . '":' . json_encode($v);
        }
        $encoded[] = '"initCallbacks":function($){' . $initCallback . '}';

        return '{' . implode(',', $encoded) . '}';
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

    public function enqueueAssets() {

        Js::addStaticGroup(ApplicationTypeFrontend::getAssetsPath() . '/dist/smartslider-frontend.min.js', 'smartslider-frontend');
    }
}

class SVGFlip {

    private static $viewBoxX;
    private static $viewBoxY;

    /**
     * @param string $svg
     * @param bool   $x
     * @param bool   $y
     *
     * @return string
     */
    public static function mirror($svg, $x, $y) {
        /* @var callable $callable */

        if ($x && $y) {
            $callable = array(
                self::class,
                'xy'
            );
        } else if ($x) {
            $callable = array(
                self::class,
                'x'
            );
        } else if ($y) {
            $callable = array(
                self::class,
                'y'
            );
        } else {
            return $svg;
        }

        preg_match('/(viewBox)=[\'"](.*?)[\'"]/i', $svg, $viewBoxResult);
        $viewBox        = explode(' ', end($viewBoxResult));
        self::$viewBoxX = $viewBox[2];
        self::$viewBoxY = $viewBox[3];

        $pattern = '/(points|d)=[\'"](.*?)[\'"]/i';

        return preg_replace_callback($pattern, $callable, $svg);
    }

    private static function x($paths) {
        $path = $paths[2];
        if ($paths[1] == 'points') {
            $points = explode(' ', $path);
            for ($i = 0; $i < count($points); $i = $i + 2) {
                $points[$i] = self::$viewBoxX - $points[$i];
            }

            return 'points="' . implode(' ', $points) . '"';
        } else if ($paths[1] == 'd') {
            $path    = substr($path, 0, -1);
            $values  = explode(' ', $path);
            $newPath = '';
            for ($i = 0; $i < count($values); $i++) {
                $pathCommand = substr($values[$i], 0, 1);
                $pathPart    = substr($values[$i], 1);
                $points      = explode(',', $pathPart);
                for ($j = 0; $j < count($points); $j = $j + 2) {
                    switch ($pathCommand) {
                        case 'v':
                        case 'V':
                        case 'c':
                            $points[$j] = -$points[$j];
                            break;
                        default:
                            $points[$j] = self::$viewBoxX - $points[$j];
                            break;
                    }
                }
                $newPath .= $pathCommand . implode(',', $points);
            }

            return 'd="' . $newPath . 'z"';
        }
    }

    private static function y($paths) {
        $path = $paths[2];
        if ($paths[1] == 'points') {
            $points = explode(' ', $path);
            for ($i = 1; $i < count($points); $i = $i + 2) {
                $points[$i] = self::$viewBoxY - $points[$i];
            }

            return 'points="' . implode(' ', $points) . '"';
        } else if ($paths[1] == 'd') {
            $path    = substr($path, 0, -1);
            $values  = explode(' ', $path);
            $newPath = '';
            for ($i = 0; $i < count($values); $i++) {
                $pathCommand = substr($values[$i], 0, 1);
                $pathPart    = substr($values[$i], 1);
                $points      = explode(',', $pathPart);
                for ($j = 0; $j < count($points); $j = $j + 2) {
                    switch ($pathCommand) {
                        case 'v':
                            $points[$j] = -$points[$j];
                            break;
                        case 'V':
                            $points[$j] = -($points[$j] - self::$viewBoxY);
                            break;
                        case 'c':
                            if (isset($points[$j + 1])) {
                                $points[$j + 1] = -$points[$j + 1];
                            }
                            break;
                        case 'C':
                            if (isset($points[$j + 1])) {
                                $points[$j + 1] = -($points[$j + 1] - self::$viewBoxY);
                            }
                            break;
                        default:
                            if (isset($points[$j + 1])) {
                                $points[$j + 1] = self::$viewBoxY - $points[$j + 1];
                            }
                            break;
                    }
                }
                $newPath .= $pathCommand . implode(',', $points);
            }

            return 'd="' . $newPath . 'z"';
        }
    }

    private static function xy($paths) {
        $path = $paths[2];
        if ($paths[1] == 'points') {
            $points = explode(' ', $path);
            for ($i = 0; $i < count($points); $i = $i + 2) {
                $points[$i]     = self::$viewBoxX - $points[$i];
                $points[$i + 1] = self::$viewBoxY - $points[$i + 1];
            }

            return 'points="' . implode(' ', $points) . '"';
        } else if ($paths[1] == 'd') {
            $path    = substr($path, 0, -1);
            $values  = explode(' ', $path);
            $newPath = '';
            for ($i = 0; $i < count($values); $i++) {
                $pathCommand = substr($values[$i], 0, 1);
                $pathPart    = substr($values[$i], 1);
                $points      = explode(',', $pathPart);
                for ($j = 0; $j < count($points); $j = $j + 2) {
                    switch ($pathCommand) {
                        case 'v':
                            break;
                        case 'V':
                            $points[$j] = $points[$j] + self::$viewBoxY;
                            break;
                        case 'c':
                            $points[$j] = -$points[$j];
                            if (isset($points[$j + 1])) {
                                $points[$j + 1] = -$points[$j + 1];
                            }
                            break;
                        case 'C':
                            $points[$j] = self::$viewBoxX - $points[$j];
                            if (isset($points[$j + 1])) {
                                $points[$j + 1] = -($points[$j + 1] - self::$viewBoxY);
                            }
                            break;
                        default:
                            $points[$j] = self::$viewBoxX - $points[$j];
                            if (isset($points[$j + 1])) {
                                $points[$j + 1] = self::$viewBoxY - $points[$j + 1];
                            }
                            break;
                    }
                }
                $newPath .= $pathCommand . implode(',', $points);
            }

            return 'd="' . $newPath . 'z"';
        }
    }
}