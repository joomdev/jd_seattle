<?php

class N2SmartSliderTypeSimple extends N2SmartSliderType {

    private $backgroundAnimation = false;

    public function getDefaults() {
        return array(
            'background'                             => '',
            'background-size'                        => 'cover',
            'background-fixed'                       => 0,
            'padding'                                => '0|*|0|*|0|*|0',
            'border-width'                           => 0,
            'border-color'                           => '3E3E3Eff',
            'border-radius'                          => 0,
            'slider-css'                             => '',
            'slide-css'                              => '',
            'animation'                              => 'horizontal',
            'animation-duration'                     => 800,
            'animation-delay'                        => 0,
            'animation-easing'                       => 'easeOutQuad',
            'animation-parallax-overlap'             => 0,
            'animation-shifted-background-animation' => 'auto',
            'carousel'                               => 1,

            'background-animation' => '',
            'kenburns-animation'   => ''
        );
    }

    protected function renderType($css) {

        $params = $this->slider->params;

        $this->loadResources();

        $background = $params->get('background');
        $backgroundColor = $params->get('background-color', '');
        $sliderCSS  = $params->get('slider-css');
		$borderRadius = $params->get('border-radius', 0);
        if (!empty($borderRadius)) {
            $sliderCSS .= 'overflow:hidden';
        }
        if (!empty($background)) {
            $sliderCSS .= 'background-image: URL(' . N2ImageHelper::fixed($background) . ');';
        }
        if (!empty($backgroundColor)) {
            $rgba = N2Color::hex2rgba($backgroundColor);
            if ($rgba[3] != 0) {
                $sliderCSS .= 'background-color:RGBA(' . $rgba[0] . ',' . $rgba[1] . ',' . $rgba[2] . ',' . round($rgba[3] / 127, 2) . ');';
            }
        }

        $slideCSS = $params->get('slide-css');

        $this->initBackgroundAnimation();

        echo $this->openSliderElement();
        $this->widgets->echoAbove();
        ?>

        <div class="n2-ss-slider-1 n2-ss-swipe-element n2-ow" style="<?php echo n2_esc_attr($sliderCSS); ?>">
            <?php
            echo $this->getBackgroundVideo($params);
            ?>
            <div class="n2-ss-slider-2 n2-ow">
                <?php if ($this->backgroundAnimation): ?>
                    <div class="n2-ss-background-animation n2-ow"></div>
                <?php endif; ?>
                <div class="n2-ss-slider-3 n2-ow" style="<?php echo $slideCSS; ?>">

                    <?php
                    echo $this->slider->staticHtml;

                    echo N2Html::tag('div', array('class' => 'n2-ss-slide-backgrounds'));

                    foreach ($this->slider->slides AS $i => $slide) {
                        $slide->finalize();

                        echo N2Html::tag('div', N2HTML::mergeAttributes($slide->attributes, $slide->linkAttributes, array(
                            'class' => 'n2-ss-slide n2-ss-canvas n2-ow ' . $slide->classes,
                            'style' => $slide->style
                        )), $slide->background . $slide->getHTML());
                    }
                    ?>
                </div>
            </div>
            <?php
            $this->widgets->echoRemainder();
            ?>
        </div>
        <?php
        $this->widgets->echoBelow();
        echo N2Html::closeTag('div');

        $this->javaScriptProperties['mainanimation'] = array(
            'type'                       => $params->get('animation'),
            'duration'                   => intval($params->get('animation-duration')),
            'delay'                      => intval($params->get('animation-delay')),
            'ease'                       => $params->get('animation-easing'),
            'parallax'                   => floatval($params->get('animation-parallax')),
            'shiftedBackgroundAnimation' => $params->get('animation-shifted-background-animation')
        );

        $this->javaScriptProperties['mainanimation']['parallax'] = intval($params->get('animation-parallax-overlap'));
        $this->javaScriptProperties['mainanimation']['shiftedBackgroundAnimation'] = 0;
    

        $this->javaScriptProperties['carousel'] = intval($params->get('carousel'));

        $this->javaScriptProperties['dynamicHeight'] = intval($params->get('dynamic-height', '0'));
        $this->javaScriptProperties['dynamicHeight'] = 0;
    

        $this->style .= $css->getCSS();

        $this->jsDependency[] = 'smartslider-simple-type-frontend';

        echo N2Html::clear();
    }

    public function getScript() {
        return "N2R(" . json_encode($this->jsDependency) . ",function(){new N2Classes.SmartSliderSimple('#{$this->slider->elementId}', " . json_encode($this->javaScriptProperties) . ");});";
    }

    public function loadResources() {
        N2JS::addStaticGroup(N2Filesystem::translate(dirname(__FILE__)) . '/dist/smartslider-simple-type-frontend.min.js', 'smartslider-simple-type-frontend');
    
    }

    private function initBackgroundAnimation() {
        $speed = $this->slider->params->get('background-animation-speed', 'normal');

        $this->javaScriptProperties['bgAnimationsColor'] = N2Color::colorToRGBA($this->slider->params->get('background-animation-color', '333333ff'));
        $this->javaScriptProperties['bgAnimations']      = array(
            'global' => $this->parseBackgroundAnimations($this->slider->params->get('background-animation', '')),
            'color'  => N2Color::colorToRGBA($this->slider->params->get('background-animation-color', '333333ff')),
            'speed'  => $speed
        );

        $slides    = array();
        $hasCustom = false;

        foreach ($this->slider->slides AS $i => $slide) {
            $animation = $this->parseBackgroundAnimations($slide->parameters->get('background-animation'));
            if ($animation) {
                $slideSpeed = $slide->parameters->get('background-animation-speed', 'default');
                if ($slideSpeed == 'default') {
                    $slideSpeed = $speed;
                }
                $slides[$i] = array(
                    'animation' => $this->parseBackgroundAnimations($slide->parameters->get('background-animation')),
                    'speed'     => $slideSpeed
                );
                if ($slides[$i]) {
                    $hasCustom = true;
                }
            }
        }
        if ($hasCustom) {
            $this->javaScriptProperties['bgAnimations']['slides'] = $slides;
        } else if (!$this->javaScriptProperties['bgAnimations']['global']) {
            $this->javaScriptProperties['bgAnimations'] = 0;
        }

        if ($this->javaScriptProperties['bgAnimations'] != 0) {

            $this->jsDependency[] = "smartslider-backgroundanimation";
            // We have background animation so load the required JS files
            N2JS::addStaticGroup(N2Filesystem::translate(dirname(__FILE__)) . '/dist/smartslider-backgroundanimation.min.js', 'smartslider-backgroundanimation');
        
        }

    }

    private function parseBackgroundAnimations($backgroundAnimation) {
        $backgroundAnimations = array_unique(array_map('intval', explode('||', $backgroundAnimation)));

        $jsProps = array();

        if (count($backgroundAnimations)) {
            N2Loader::import('libraries.backgroundanimation.storage', 'smartslider');

            foreach ($backgroundAnimations AS $animationId) {
                $animation = N2StorageSectionAdmin::getById($animationId, 'backgroundanimation');
                if (isset($animation)) {
                    $jsProps[] = $animation['value']['data'];
                }

            }

            if (count($jsProps)) {
                $this->backgroundAnimation = true;

                return $jsProps;
            }
        }

        return 0;
    }

    private function getBackgroundVideo($params) {
        $mp4 = N2ImageHelper::fixed($params->get('backgroundVideoMp4', ''));

        if (empty($mp4)) {
            return '';
        }

        $attributes = array();

        if ($params->get('backgroundVideoMuted', 1)) {
            $attributes['muted'] = 'muted';
        }

        if ($params->get('backgroundVideoLoop', 1)) {
            $attributes['loop'] = 'loop';
        }

        return N2Html::tag('div', array('class' => 'n2-ss-slider-background-video-container n2-ow'), N2Html::tag('video', $attributes + array(
                'class'              => 'n2-ss-slider-background-video n2-ow',
                'data-mode'          => $params->get('backgroundVideoMode', 'fill'),
                'playsinline'        => 1,
                'webkit-playsinline' => 1,
                'data-keepplaying'   => 1,
                'preload'            => 'none'
            ), N2Html::tag("source", array(
            "src"  => $mp4,
            "type" => "video/mp4"
        ), '', false)));

    }
}

