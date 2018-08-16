<?php

class N2SmartsliderBackendPreviewController extends N2SmartSliderController {

    public $layoutName = 'preview';

    private $sliderId = 0;

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.Sliders',
            'models.Slides',
            'models.generator'
        ), 'smartslider');

        $this->sliderId = N2Request::getInt('sliderid');

        N2SS3::$forceDesktop = true;
    }

    public function actionIndex() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            $sliderData = N2Post::getVar('slider', false);
            if (!is_array($sliderData)) {
                $sliderData = false;
            }
            $this->addView("index", array(
                'sliderData' => $sliderData,
                'sliderId'   => $this->sliderId
            ));
            $this->render();
        }
    }

    public function actionSlide() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            $sliderId = N2Request::getInt('sliderId');
            $slideId  = N2Request::getInt('slideId');
            if ($sliderId) {
                $slidesData  = array();
                $slidesModel = new N2SmartsliderSlidesModel();
                $_slide      = N2Request::getVar('slide');
                if (is_array($_slide)) {
                    $slide = $slidesModel->getRowFromPost($sliderId, $_slide);
                    if ($slideId) {
                        $slide['id']          = $slideId;
                        $slidesData[$slideId] = $slide;
                    } else {
                        $slide['id']       = '-1000';
                        $slidesData['add'] = $slide;
                    }
                }
                $this->addView("slide", array(
                    'slidesData' => $slidesData,
                    'sliderId'   => $sliderId
                ));
                $this->render();
            }
        }
    }

    public function actionGenerator() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            $generator_id = N2Request::getInt('generator_id');

            $generatorModel = new N2SmartsliderGeneratorModel();
            $sliderId       = $generatorModel->getSliderId($generator_id);

            if ($sliderId) {
                $generatorData = array();

                $generatorData[$generator_id] = N2Request::getVar('generator');

                $this->addView("generator", array(
                    'generatorData' => $generatorData,
                    'sliderId'      => $sliderId
                ));
                $this->render();
            }
        }
    }
}