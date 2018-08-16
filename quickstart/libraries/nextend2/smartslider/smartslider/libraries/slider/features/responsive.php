<?php

class N2SmartSliderFeatureResponsive {

    /** @var  N2SmartSliderAbstract */
    public $slider;

    public $desktop = 1;

    public $tablet = 1;

    public $mobile = 1;

    public $onResizeEnabled = 1;

    public $type = 'auto';

    public $modeObject = null;

    public $scaleDown = 0;

    public $scaleUp = 0;

    public $forceFull = 0;

    public $forceFullOverflowX = 'body';

    public $forceFullHorizontalSelector = '';

    public $constrainRatio = 1;

    public $minimumHeight = -1;

    public $maximumHeight = -1;

    public $maximumSlideWidth = 10000;
    public $maximumSlideWidthLandscape = -1;

    public $maximumSlideWidthTablet = -1;
    public $maximumSlideWidthTabletLandscape = -1;

    public $maximumSlideWidthMobile = -1;
    public $maximumSlideWidthMobileLandscape = -1;

    public $maximumSlideWidthConstrainHeight = 0;

    public $verticalOffsetSelectors = '';
    public $responsiveDecreaseSliderHeight = 0;

    public $basedOn = 'combined';

    public $desktopPortraitScreenWidth = 1200;

    public $tabletPortraitScreenWidth = 800;

    public $mobilePortraitScreenWidth = 440;

    public $tabletLandscapeScreenWidth = 1024;

    public $mobileLandscapeScreenWidth = 740;

    public $focusUser = 0, $focusAutoplay = 0;

    public $orientationMode = 'width_and_height';

    public function __construct($slider, $features) {

        $this->slider = $slider;

        $this->desktop = intval($slider->params->get('desktop', 1));
        $this->tablet  = intval($slider->params->get('tablet', 1));
        $this->mobile  = intval($slider->params->get('mobile', 1));

        $this->type = $slider->params->get('responsive-mode', 'auto');

        $this->responsivePlugin = N2SSPluginSliderResponsive::getType($this->type);
        $this->responsivePlugin->parse($slider->params, $this, $features);

        $this->onResizeEnabled = !$slider->disableResponsive;

        if (!$this->scaleDown && !$this->scaleUp) {
            $this->onResizeEnabled = 0;
        }


        $this->basedOn = N2SmartSliderSettings::get('responsive-basedon', 'combined');

        $this->desktopPortraitScreenWidth = intval(N2SmartSliderSettings::get('responsive-screen-width-desktop-portrait', 1200));

        $this->tabletPortraitScreenWidth = intval(N2SmartSliderSettings::get('responsive-screen-width-tablet-portrait', 800));
        $this->mobilePortraitScreenWidth = intval(N2SmartSliderSettings::get('responsive-screen-width-mobile-portrait', 440));

        if ($this->tabletPortraitScreenWidth < $this->mobilePortraitScreenWidth) {
            $this->mobilePortraitScreenWidth = $this->tabletPortraitScreenWidth;
        }

        $this->tabletLandscapeScreenWidth = intval(N2SmartSliderSettings::get('responsive-screen-width-tablet-landscape', 1024));
        $this->mobileLandscapeScreenWidth = intval(N2SmartSliderSettings::get('responsive-screen-width-mobile-landscape', 740));

        if ($this->tabletLandscapeScreenWidth < $this->mobileLandscapeScreenWidth) {
            $this->mobileLandscapeScreenWidth = $this->tabletLandscapeScreenWidth;
        }

        $modes           = array(
            'desktopPortrait'  => 1,
            'desktopLandscape' => 0,
            'tabletPortrait'   => 0,
            'tabletLandscape'  => 0,
            'mobilePortrait'   => 0,
            'mobileLandscape'  => 0
        );
        $min             = intval($slider->params->get('desktop-portrait-minimum-font-size', 4));
        $minimumFontSize = array(
            'desktopPortrait'  => $min,
            'desktopLandscape' => $min,
            'tabletPortrait'   => $min,
            'tabletLandscape'  => $min,
            'mobilePortrait'   => $min,
            'mobileLandscape'  => $min
        );
        $ratioModifiers  = array(
            'unknownUnknown'   => 1,
            'desktopPortrait'  => 1,
            'desktopLandscape' => 1,
            'tabletPortrait'   => 1,
            'tabletLandscape'  => 1,
            'mobilePortrait'   => 1,
            'mobileLandscape'  => 1
        );

        $sliderWidth  = intval($slider->params->get('width', 1000));
        $sliderHeight = intval($slider->params->get('height', 500));

        $modeSwitchWidth = array(
            'desktopPortrait'  => $sliderWidth,
            'desktopLandscape' => $sliderWidth,
            'tabletPortrait'   => 0,
            'tabletLandscape'  => 0,
            'mobilePortrait'   => 0,
            'mobileLandscape'  => 0
        );

        if ($slider->params->get('desktop-landscape', 0)) {
            $modes['desktopLandscape'] = 1;

            $landscapeWidth                      = intval($slider->params->get('desktop-landscape-width', 1440));
            $modeSwitchWidth['desktopLandscape'] = $landscapeWidth;

            $landscapeHeight = intval($slider->params->get('desktop-landscape-height'));
            if ($landscapeHeight) {
                $ratioModifiers['desktopLandscape'] = $landscapeHeight / ($modeSwitchWidth['desktopLandscape'] / $sliderWidth * $sliderHeight);
            }
            $minimumFontSize['desktopLandscape'] = intval($slider->params->get('desktop-landscape-minimum-font-size', 4));
        }

        $tabletPortraitEnabled = $slider->params->get('tablet-portrait', 0);
        if ($tabletPortraitEnabled) {
            $tabletWidth = intval($slider->params->get('tablet-portrait-width', 800));
        } else {
            $tabletWidth = intval($sliderWidth * N2SmartSliderSettings::get('responsive-default-ratio-tablet-portrait', 70) / 100);
        }
        if ($tabletWidth > 0) {
            if ($tabletWidth >= $modeSwitchWidth['desktopPortrait']) {
                $tabletWidth = $modeSwitchWidth['desktopPortrait'] - 1;
            }
            if ($tabletWidth > 0) {
                $modes['tabletPortrait']           = 1;
                $modeSwitchWidth['tabletPortrait'] = $tabletWidth;
                $portraitHeight                    = intval($slider->params->get('tablet-portrait-height'));
                if ($tabletPortraitEnabled && $portraitHeight) {
                    $ratioModifiers['tabletPortrait'] = $portraitHeight / ($modeSwitchWidth['tabletPortrait'] / $sliderWidth * $sliderHeight);
                } else {
                    $ratioModifiers['tabletPortrait'] = $ratioModifiers['desktopPortrait'];
                }
                $minimumFontSize['tabletPortrait'] = intval($slider->params->get('tablet-portrait-minimum-font-size', 4));
            }
        }

        if ($slider->params->get('tablet-landscape', 0)) {
            $tabletWidth = intval($slider->params->get('tablet-landscape-width', 1024));
            if ($tabletWidth >= $modeSwitchWidth['desktopLandscape']) {
                $tabletWidth = $modeSwitchWidth['desktopLandscape'] - 1;
            }
            if ($tabletWidth > 0) {
                $modes['tabletLandscape']           = 1;
                $modeSwitchWidth['tabletLandscape'] = $tabletWidth;
                $landscapeHeight                    = intval($slider->params->get('tablet-landscape-height'));
                if ($landscapeHeight) {
                    $ratioModifiers['tabletLandscape'] = $landscapeHeight / ($modeSwitchWidth['tabletLandscape'] / $sliderWidth * $sliderHeight);
                } else {
                    $ratioModifiers['tabletLandscape'] = $ratioModifiers['desktopLandscape'];
                }
                $minimumFontSize['tabletLandscape'] = intval($slider->params->get('tablet-landscape-minimum-font-size', 4));
            }
        } else {
            $this->tabletLandscapeScreenWidth  = $this->tabletPortraitScreenWidth;
            $ratioModifiers['tabletLandscape'] = $ratioModifiers['tabletPortrait'];
        }

        $mobilePortraitEnabled = $slider->params->get('mobile-portrait', 0);
        if ($mobilePortraitEnabled) {
            $mobileWidth = intval($slider->params->get('mobile-portrait-width', 440));
        } else {
            $mobileWidth = intval($sliderWidth * N2SmartSliderSettings::get('responsive-default-ratio-mobile-portrait', 50) / 100);
        }

        if ($mobileWidth > 0) {
            if ($modeSwitchWidth['tabletPortrait'] > 0) {
                if ($mobileWidth >= $modeSwitchWidth['tabletPortrait']) {
                    $mobileWidth = $modeSwitchWidth['tabletPortrait'] - 1;
                }
            } else {
                if ($mobileWidth >= $modeSwitchWidth['desktopPortrait']) {
                    $mobileWidth = $modeSwitchWidth['desktopPortrait'] - 1;
                }
            }
            if ($mobileWidth > 0) {
                $modes['mobilePortrait']           = 1;
                $modeSwitchWidth['mobilePortrait'] = $mobileWidth;
                $portraitHeight                    = intval($slider->params->get('mobile-portrait-height'));
                if ($mobilePortraitEnabled && $portraitHeight) {
                    $ratioModifiers['mobilePortrait'] = $portraitHeight / ($modeSwitchWidth['mobilePortrait'] / $sliderWidth * $sliderHeight);
                } else {
                    $ratioModifiers['mobilePortrait'] = $ratioModifiers['tabletPortrait'];
                }
                $minimumFontSize['mobilePortrait'] = intval($slider->params->get('mobile-portrait-minimum-font-size', 4));
            }
        }

        if ($slider->params->get('mobile-landscape', 0)) {
            $mobileWidth = intval($slider->params->get('mobile-landscape-width', 740));
            if ($modeSwitchWidth['tabletLandscape'] > 0) {
                if ($mobileWidth >= $modeSwitchWidth['tabletLandscape']) {
                    $mobileWidth = $modeSwitchWidth['tabletLandscape'] - 1;
                }
            } else {
                if ($mobileWidth >= $modeSwitchWidth['desktopLandscape']) {
                    $mobileWidth = $modeSwitchWidth['desktopLandscape'] - 1;
                }
            }
            if ($mobileWidth > 0) {
                $modes['mobileLandscape']           = 1;
                $modeSwitchWidth['mobileLandscape'] = $mobileWidth;
                $landscapeHeight                    = intval($slider->params->get('mobile-landscape-height'));
                if ($landscapeHeight) {
                    $ratioModifiers['mobileLandscape'] = $landscapeHeight / ($modeSwitchWidth['mobileLandscape'] / $sliderWidth * $sliderHeight);
                } else {
                    $ratioModifiers['mobileLandscape'] = $ratioModifiers['tabletLandscape'];
                }
                $minimumFontSize['mobileLandscape'] = intval($slider->params->get('mobile-landscape-minimum-font-size', 4));
            }
        } else {
            $this->mobileLandscapeScreenWidth  = $this->mobilePortraitScreenWidth;
            $ratioModifiers['mobileLandscape'] = $ratioModifiers['mobilePortrait'];
        }
        $this->modes                  = $modes;
        $this->sliderWidthToDevice    = $modeSwitchWidth;
        $this->sliderRatioToDevice    = array(
            'Portrait'  => array(
                'tablet' => $modeSwitchWidth['tabletPortrait'] / $modeSwitchWidth['desktopPortrait'],
                'mobile' => $modeSwitchWidth['mobilePortrait'] / $modeSwitchWidth['desktopPortrait']
            ),
            'Landscape' => array(
                'tablet' => $modeSwitchWidth['tabletLandscape'] / $modeSwitchWidth['desktopPortrait'],
                'mobile' => $modeSwitchWidth['mobileLandscape'] / $modeSwitchWidth['desktopPortrait']
            )
        );
        $this->minimumFontSizes       = $minimumFontSize;
        $this->verticalRatioModifiers = $ratioModifiers;

    }

    public function makeJavaScriptProperties(&$properties) {
        $normalizedDeviceModes = array(
            'unknownUnknown'  => array(
                'unknown',
                'Unknown'
            ),
            'desktopPortrait' => array(
                'desktop',
                'Portrait'
            )
        );
        if ($this->orientationMode == 'width') {
            if (!$this->modes['desktopLandscape']) {
                $normalizedDeviceModes['desktopLandscape'] = $normalizedDeviceModes['desktopPortrait'];
            } else {
                $normalizedDeviceModes['desktopLandscape'] = array(
                    'desktop',
                    'Landscape'
                );
            }
            if (!$this->modes['tabletLandscape']) {
                $normalizedDeviceModes['tabletLandscape'] = $normalizedDeviceModes['desktopPortrait'];

            } else {
                $normalizedDeviceModes['tabletLandscape'] = array(
                    'tablet',
                    'Landscape'
                );
            }
            if (!$this->modes['tabletPortrait']) {
                $normalizedDeviceModes['tabletPortrait'] = $normalizedDeviceModes['tabletLandscape'];
            } else {
                $normalizedDeviceModes['tabletPortrait'] = array(
                    'tablet',
                    'Portrait'
                );
            }
            if (!$this->modes['mobileLandscape']) {
                $normalizedDeviceModes['mobileLandscape'] = $normalizedDeviceModes['tabletPortrait'];
            } else {
                $normalizedDeviceModes['mobileLandscape'] = array(
                    'mobile',
                    'Landscape'
                );
            }
            if (!$this->modes['mobilePortrait']) {
                $normalizedDeviceModes['mobilePortrait'] = $normalizedDeviceModes['mobileLandscape'];
            } else {
                $normalizedDeviceModes['mobilePortrait'] = array(
                    'mobile',
                    'Portrait'
                );
            }
        } else {
            if (!$this->modes['desktopLandscape']) {
                $normalizedDeviceModes['desktopLandscape'] = $normalizedDeviceModes['desktopPortrait'];
            } else {
                $normalizedDeviceModes['desktopLandscape'] = array(
                    'desktop',
                    'Landscape'
                );
            }
            if (!$this->modes['tabletPortrait']) {
                $normalizedDeviceModes['tabletPortrait'] = $normalizedDeviceModes['desktopPortrait'];
            } else {
                $normalizedDeviceModes['tabletPortrait'] = array(
                    'tablet',
                    'Portrait'
                );
            }
            if (!$this->modes['tabletLandscape']) {
                if ($normalizedDeviceModes['desktopLandscape'][1] == 'Landscape') {
                    $normalizedDeviceModes['tabletLandscape'] = $normalizedDeviceModes['desktopLandscape'];
                } else {
                    $normalizedDeviceModes['tabletLandscape'] = $normalizedDeviceModes['tabletPortrait'];
                }
            } else {
                $normalizedDeviceModes['tabletLandscape'] = array(
                    'tablet',
                    'Landscape'
                );
            }
            if (!$this->modes['mobilePortrait']) {
                $normalizedDeviceModes['mobilePortrait'] = $normalizedDeviceModes['tabletPortrait'];
            } else {
                $normalizedDeviceModes['mobilePortrait'] = array(
                    'mobile',
                    'Portrait'
                );
            }
            if (!$this->modes['mobileLandscape']) {
                if ($normalizedDeviceModes['tabletLandscape'][1] == 'Landscape') {
                    $normalizedDeviceModes['mobileLandscape'] = $normalizedDeviceModes['tabletLandscape'];
                } else {
                    $normalizedDeviceModes['mobileLandscape'] = $normalizedDeviceModes['mobilePortrait'];
                }
            } else {
                $normalizedDeviceModes['mobileLandscape'] = array(
                    'mobile',
                    'Landscape'
                );
            }
        }

        if ($this->maximumSlideWidthLandscape <= 0) {
            $this->maximumSlideWidthLandscape = $this->maximumSlideWidth;
        }

        if ($this->maximumSlideWidthTablet <= 0) {
            $this->maximumSlideWidthTablet = $this->maximumSlideWidth;
        }

        if ($this->maximumSlideWidthTabletLandscape <= 0) {
            $this->maximumSlideWidthTabletLandscape = $this->maximumSlideWidthTablet;
        }

        if ($this->maximumSlideWidthMobile <= 0) {
            $this->maximumSlideWidthMobile = $this->maximumSlideWidth;
        }

        if ($this->maximumSlideWidthMobileLandscape <= 0) {
            $this->maximumSlideWidthMobileLandscape = $this->maximumSlideWidthMobile;
        }

        $properties['responsive'] = array(
            'desktop' => N2SS3::$forceDesktop ? 1 : $this->desktop,
            'tablet'  => $this->tablet,
            'mobile'  => $this->mobile,

            'onResizeEnabled'                  => $this->onResizeEnabled,
            'type'                             => $this->type,
            'downscale'                        => $this->scaleDown,
            'upscale'                          => $this->scaleUp,
            'minimumHeight'                    => $this->minimumHeight,
            'maximumHeight'                    => $this->maximumHeight,
            'maximumSlideWidth'                => $this->maximumSlideWidth,
            'maximumSlideWidthLandscape'       => $this->maximumSlideWidthLandscape,
            'maximumSlideWidthTablet'          => $this->maximumSlideWidthTablet,
            'maximumSlideWidthTabletLandscape' => $this->maximumSlideWidthTabletLandscape,
            'maximumSlideWidthMobile'          => $this->maximumSlideWidthMobile,
            'maximumSlideWidthMobileLandscape' => $this->maximumSlideWidthMobileLandscape,
            'maximumSlideWidthConstrainHeight' => intval($this->maximumSlideWidthConstrainHeight),
            'forceFull'                        => $this->forceFull,
            'forceFullOverflowX'               => $this->forceFullOverflowX,
            'forceFullHorizontalSelector'      => $this->forceFullHorizontalSelector,
            'constrainRatio'                   => $this->constrainRatio,
            'verticalOffsetSelectors'          => $this->verticalOffsetSelectors,
            'decreaseSliderHeight'             => $this->responsiveDecreaseSliderHeight,

            'focusUser'     => $this->focusUser,
            'focusAutoplay' => $this->focusAutoplay,

            'deviceModes'            => $this->modes,
            'normalizedDeviceModes'  => $normalizedDeviceModes,
            'verticalRatioModifiers' => $this->verticalRatioModifiers,
            'minimumFontSizes'       => $this->minimumFontSizes,
            'ratioToDevice'          => $this->sliderRatioToDevice,
            'sliderWidthToDevice'    => $this->sliderWidthToDevice,

            'basedOn'         => $this->basedOn,
            'orientationMode' => $this->orientationMode,

            'scrollFix'          => intval($this->slider->params->get('scroll-fix', 0)),
            'overflowHiddenPage' => intval($this->slider->params->get('overflow-hidden-page', 0)),

            'desktopPortraitScreenWidth' => $this->desktopPortraitScreenWidth,
            'tabletPortraitScreenWidth'  => $this->tabletPortraitScreenWidth,
            'mobilePortraitScreenWidth'  => $this->mobilePortraitScreenWidth,
            'tabletLandscapeScreenWidth' => $this->tabletLandscapeScreenWidth,
            'mobileLandscapeScreenWidth' => $this->mobileLandscapeScreenWidth,
        );
    }

    public function getMinimumFontSizeAttributes() {
        $return = array();
        foreach ($this->minimumFontSizes AS $k => $v) {
            $return['data-minFontSize' . $k] = $v;
        }

        return $return;
    }
}