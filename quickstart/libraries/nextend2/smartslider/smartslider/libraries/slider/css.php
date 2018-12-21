<?php

N2Loader::import('libraries.parse.font');
N2Loader::import('libraries.parse.style');

abstract class N2SmartSliderCSSAbstract {

    /**
     * @var N2SmartSliderAbstract
     */
    protected $slider;

    public $sizes = array();

    protected $context = array();

    public function __construct($slider) {
        $this->slider = $slider;

        $params = $slider->params;

        if (!N2Platform::needStrongerCSS()) {
            N2CSS::addStaticGroup(NEXTEND_SMARTSLIDER_ASSETS . '/smartslider.min.css', 'smartslider');
        
        }

        $width  = intval($params->get('width', 900));
        $height = intval($params->get('height', 500));
        if ($width < 10 || $height < 10) {
            N2Message::error(n2_('Slider size is too small!'));
        }
        $this->context = array_merge($this->context, array(
            'sliderid'       => "~'#{$slider->elementId}'",
            'width'          => $width . 'px',
            'height'         => $height . 'px',
            'canvas'         => 0,
            'count'          => ($slider->slides == null ? 0 : count($slider->slides)),
            'margin'         => '0px 0px 0px 0px',
            'hasPerspective' => 0
        ));

        $perspective = intval($params->get('perspective', 1500));
        if ($perspective > 0) {
            $this->context['hasPerspective'] = 1;
            $this->context['perspective']    = $perspective . 'px';
        }

        if ($params->get('imageload', 0)) {
            $this->slider->addLess(NEXTEND_SMARTSLIDER_ASSETS . '/less/spinner.n2less', $this->context);
        }
    }

    public function getCSS() {
        $css = '';
        if (N2Platform::needStrongerCSS()) {
            $css = file_get_contents(NEXTEND_SMARTSLIDER_ASSETS . '/smartslider.min.css');
        
        }

        foreach ($this->slider->less AS $file => $context) {
            $compiler = new n2lessc();
            $compiler->setVariables($context);
            $css .= $compiler->compileFile($file);
        }
        $css .= implode('', $this->slider->css);

        if (N2Platform::needStrongerCSS()) {
            $css = preg_replace(array(
                '/\.n2-ss-align([\. \{,])/',
                '/(?<!' . preg_quote('#' . $this->slider->elementId) . ')\.n2-ss-slider([\. \{,])/'
            ), array(
                '#' . $this->slider->elementId . '-align$1',
                '#' . $this->slider->elementId . '$1'
            ), $css);
        }

        $css .= $this->slider->params->get('custom-css-codes', '');

        return $css;
    }

    public function initSizes() {

        $this->sizes['marginVertical']   = 0;
        $this->sizes['marginHorizontal'] = 0;

        $this->sizes['width']        = intval($this->context['width']);
        $this->sizes['height']       = intval($this->context['height']);
        $this->sizes['canvasWidth']  = intval($this->context['canvaswidth']);
        $this->sizes['canvasHeight'] = intval($this->context['canvasheight']);
    }


    protected function setContextFonts($matches, $fonts, $value) {
        $this->context['font' . $fonts] = '~".' . $matches[0] . '"';

        $font                                    = new N2ParseFont($value);
        $this->context['font' . $fonts . 'text'] = '";' . $font->printTab() . '"';
        $font->mixinTab('Link');
        $this->context['font' . $fonts . 'link'] = '";' . $font->printTab('Link') . '"';
        $font->mixinTab('Link:Hover', 'Link');
        $this->context['font' . $fonts . 'hover'] = '";' . $font->printTab('Link:Hover') . '"';
    }

    protected function setContextStyles($selector, $styles, $value) {
        $this->context['style' . $styles] = '~".' . $selector . '"';

        $style                                       = new N2ParseStyle($value);
        $this->context['style' . $styles . 'normal'] = '";' . $style->printTab('Normal') . '"';
        $this->context['style' . $styles . 'hover']  = '";' . $style->printTab('Hover') . '"';

    }
}