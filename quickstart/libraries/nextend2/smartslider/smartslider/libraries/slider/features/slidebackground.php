<?php

class N2SmartSliderFeatureSlideBackground {

    private $slider;

    public function __construct($slider) {

        $this->slider = $slider;
    }

    public function makeJavaScriptProperties(&$properties) {
        $enabled = intval($this->slider->params->get('slide-background-parallax', 0));
        if (!$enabled) {
            if ($this->slider->params->get('backgroundMode') == 'fixed') {
                $enabled = 1;
            }
        }
        if ($enabled) {
            $properties['backgroundParallax'] = array(
                'strength' => intval($this->slider->params->get('slide-background-parallax-strength', 50)) / 100,
                'tablet'   => intval($this->slider->params->get('bg-parallax-tablet', 0)),
                'mobile'   => intval($this->slider->params->get('bg-parallax-mobile', 0))
            );
        }
    }

    /**
     * @param $slide N2SmartSliderSlide
     *
     * @return string
     */

    public function make($slide) {

        if ($slide->parameters->get('background-type') == '') {
            $slide->parameters->set('background-type', 'color');
            if ($slide->parameters->get('backgroundVideoMp4')) {
                $slide->parameters->set('background-type', 'video');
            } else if ($slide->parameters->get('backgroundImage')) {
                $slide->parameters->set('background-type', 'image');
            }
        }

        $html = $this->makeBackground($slide);

        return $html;
    }

    private function getBackgroundStyle($slide) {

        $style = '';
        $color = $slide->fill($slide->parameters->get('backgroundColor', ''));
        if (strlen($color) > 0 && $color[0] == '#') {
            $color = substr($color, 1);
            if (strlen($color) == 6) {
                $color .= 'ff';
            }
        }
        $gradient = $slide->parameters->get('backgroundGradient', 'off');

        if (!class_exists('N2Color')) {
            N2Loader::import("libraries.image.color");
        }

        if ($gradient != 'off') {
            $colorEnd = $slide->fill($slide->parameters->get('backgroundColorEnd', 'ffffff00'));
            if (strlen($colorEnd) > 0 && $colorEnd[0] == '#') {
                $colorEnd = substr($colorEnd, 1);
                if (strlen($colorEnd) == 6) {
                    $colorEnd .= 'ff';
                }
            }
            switch ($gradient) {
                case 'horizontal':
                    $style .= 'background:linear-gradient(to right, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    break;
                case 'vertical':
                    $style .= 'background:linear-gradient(to bottom, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    break;
                case 'diagonal1':
                    $style .= 'background:linear-gradient(45deg, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    break;
                case 'diagonal2':
                    $style .= 'background:linear-gradient(135deg, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    break;
            }
        } else {
            if (strlen($color) == 8) {

                $alpha = substr($color, 6, 2);
                if ($alpha != '00') {
                    $style = 'background-color: #' . substr($color, 0, 6) . ';';
                    if ($alpha != 'ff') {
                        $style .= "background-color: " . N2Color::colorToRGBA($color) . ";";
                    }
                }
            }
        }

        return $style;
    }

    private function makeBackground($slide) {

        $backgroundType = $slide->parameters->get('background-type');

        if (empty($backgroundType)) {
            $backgroundVideoMp4 = $slide->parameters->get('backgroundVideoMp4', '');
            $backgroundImage    = $slide->parameters->get('backgroundImage', '');
            if (!empty($backgroundVideoMp4)) {
                $backgroundType = 'video';
            } else if (!empty($backgroundImage)) {
                $backgroundType = 'image';
            } else {
                $backgroundType = 'color';
            }
        }

        $backgroundElements = array();

        if ($backgroundType == 'color') {
            $backgroundElements[] = $this->renderColor($slide);

        } else if ($backgroundType == 'video') {


            $backgroundElements[] = $this->renderBackgroundVideo($slide);
            $backgroundElements[] = $this->renderImage($slide);

            $backgroundElements[] = $this->renderColor($slide);

        } else if ($backgroundType == 'image') {


            $backgroundElements[] = $this->renderImage($slide);

            $backgroundElements[] = $this->renderColor($slide);
        }

        $fillMode = $slide->parameters->get('backgroundMode', 'default');
        if ($fillMode == 'default') {
            $fillMode = $this->slider->params->get('backgroundMode', 'fill');
        }

        if ($fillMode == 'fixed') {
            $fillMode = 'fill';
        }

        return N2Html::tag('div', array(
            'class'     => "n2-ss-slide-background n2-ow",
            'data-mode' => $fillMode
        ), implode('', $backgroundElements));
    }

    private function renderColor($slide) {
        $backgroundColorStyle = $this->getBackgroundStyle($slide);

        if (!empty($backgroundColorStyle)) {

            $attributes = array(
                'class' => 'n2-ss-slide-background-color',
                "style" => $backgroundColorStyle
            );

            if ($slide->parameters->get('backgroundColorOverlay', 0)) {
                $attributes['data-overlay'] = 1;
            }

            return N2Html::tag('div', $attributes, '');
        }

        return '';
    }

    /**
     * @param $slide N2SmartSliderSlide
     *
     * @return string
     */
    private function renderImage($slide) {

        $rawBackgroundImage = $slide->parameters->get('backgroundImage', '');

        if (empty($rawBackgroundImage)) {
            return '';
        }

        $backgroundImageBlur = max(0, $slide->parameters->get('backgroundImageBlur', 0));

        $x = max(0, min(100, $slide->parameters->get('backgroundFocusX', 50)));
        $y = max(0, min(100, $slide->parameters->get('backgroundFocusY', 50)));

        if ($slide->hasGenerator()) {

            $backgroundImage = $slide->fill($rawBackgroundImage);

            $imageData = N2ImageManager::getImageData($rawBackgroundImage);

            $imageData['desktop-retina']['image'] = $slide->fill($imageData['desktop-retina']['image']);
            $imageData['tablet']['image']         = $slide->fill($imageData['tablet']['image']);
            $imageData['tablet-retina']['image']  = $slide->fill($imageData['tablet-retina']['image']);
            $imageData['mobile']['image']         = $slide->fill($imageData['mobile']['image']);
            $imageData['mobile-retina']['image']  = $slide->fill($imageData['mobile-retina']['image']);
        } else {
            $backgroundImage = $slide->fill($rawBackgroundImage);
            $imageData       = N2ImageManager::getImageData($backgroundImage);
        }

        if (empty($backgroundImage)) {
            $src = N2Image::base64Transparent();
        } else {
            $src = N2ImageHelper::dynamic($this->slider->features->optimize->optimizeBackground($backgroundImage, $x, $y));
            $slide->addImage(N2ImageHelper::fixed($src));
        }


        $alt   = $slide->fill($slide->parameters->get('backgroundAlt', ''));
        $title = $slide->fill($slide->parameters->get('backgroundTitle', ''));

        $deviceAttributes = $this->getDeviceAttributes($src, $imageData);

        $imageAttributes = array();
        if (!empty($title)) {
            $imageAttributes['title'] = $title;
        }


        $opacity = min(100, max(0, $slide->parameters->get('backgroundImageOpacity', 100)));

        $style = array();
        if ($opacity < 100) {
            $style[] = 'opacity:' . ($opacity / 100);
        }

        if ($x != '50' || $y != '50') {
            $style[] = 'background-position: ' . $x . '% ' . $y . '%';
        }

        $attributes = $deviceAttributes + array(
                "class"     => 'n2-ss-slide-background-image',
                "data-blur" => $backgroundImageBlur
            );
        if (!empty($style)) {
            $attributes['style'] = implode(';', $style);
        }

        if ($slide->isCurrentlyEdited()) {
            $attributes['data-opacity'] = $opacity;
            $attributes['data-x']       = $x;
            $attributes['data-y']       = $y;
        }

        return N2HTML::tag('div', $attributes, $this->getDefaultImage($src, $alt, $imageAttributes, $deviceAttributes));
    }

    private function getDeviceAttributes($image, $imageData) {

        $attributes                 = array();
        $attributes['data-hash']    = md5($image);
        $attributes['data-desktop'] = N2ImageHelper::fixed($image);

        if ($imageData['desktop-retina']['image'] == '' && $imageData['tablet']['image'] == '' && $imageData['tablet-retina']['image'] == '' && $imageData['mobile']['image'] == '' && $imageData['mobile-retina']['image'] == '') {

        } else {
            if ($imageData['desktop-retina']['image'] != '') {
                $attributes['data-desktop-retina'] = N2ImageHelper::fixed($imageData['desktop-retina']['image']);
            }
            if ($imageData['tablet']['image'] != '') {
                $attributes['data-tablet'] = N2ImageHelper::fixed($imageData['tablet']['image']);
            }
            if ($imageData['tablet-retina']['image'] != '') {
                $attributes['data-tablet-retina'] = N2ImageHelper::fixed($imageData['tablet-retina']['image']);
            }
            if ($imageData['mobile']['image'] != '') {
                $attributes['data-mobile'] = N2ImageHelper::fixed($imageData['mobile']['image']);
            }
            if ($imageData['mobile-retina']['image'] != '') {
                $attributes['data-mobile-retina'] = N2ImageHelper::fixed($imageData['mobile-retina']['image']);
            }

            //We have to force the fade on load enabled to make sure the user get great result.
            $this->slider->features->fadeOnLoad->forceFadeOnLoad();
        }

        return $attributes;
    }

    private function getDefaultImage($src, $alt, $imageAttributes, $deviceAttributes) {
        if (count($deviceAttributes) > 2 || $this->slider->features->lazyLoad->isEnabled > 0) {
            return '';
        }

        return N2Html::image(N2ImageHelper::fixed($src), $alt, $imageAttributes);
    }

    private function renderBackgroundVideo($slide) {

        return '';
    }
}