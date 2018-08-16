<?php

class N2SmartsliderBackendSliderController extends N2SmartSliderController {

    public $sliderId = 0;
    public $layoutName = 'default1c';

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.Sliders',
            'models.Slides',
            'models.generator'
        ), 'smartslider');

        $this->sliderId = N2Request::getInt('sliderid');
    }

    public function actionClearCache() {
        if ($this->validateToken()) {
            $slidersModel = new N2SmartsliderSlidersModel();
            $slider       = $slidersModel->get($this->sliderId);
            if ($this->validateDatabase($slider)) {

                $slidersModel->refreshCache($this->sliderId);
                N2Message::success(n2_('Cache cleared.'));
                $this->redirect(array(
                    "slider/edit",
                    array("sliderid" => $this->sliderId)
                ));
            }
        }
    }

    public function actionCachedSlider() {
        if ($this->validateToken()) {
            $slidersModel = new N2SmartsliderSlidersModel();
            $slider       = $slidersModel->get($this->sliderId);
            if ($this->validateDatabase($slider)) {

                $this->addView('cachedslider', array(
                    'slider' => $slider
                ));
                $this->render();

            }
        }
    }

    public function actionEdit() {

        if ($this->validatePermission('smartslider_edit')) {

            $slidersModel = new N2SmartsliderSlidersModel();

            $slider = $slidersModel->get($this->sliderId);

            if (!$slider) {
                $this->redirectToSliders();
            }

            $xref   = new N2SmartsliderSlidersXrefModel();
            $groups = $xref->getGroups($this->sliderId);
            if (!empty($groups)) {
                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "slider/edit",
                        array('sliderid' => $groups[0]['group_id'])
                    )),
                    'class' => 'n2-h4'
                ), $groups[0]['title']));
            }


            $this->layout->addBreadcrumb(N2Html::tag('a', array(
                'href'  => $this->appType->router->createUrl(array(
                    "slider/edit",
                    array('sliderid' => $this->sliderId)
                )),
                'class' => 'n2-h4 n2-active'
            ), $slider['title']));

            N2Loader::import('libraries.fonts.fontmanager');
            N2Loader::import('libraries.stylemanager.stylemanager');

            switch ($slider['type']) {
                case 'group':
                    $this->loadSliderManager();
                    $this->addView("group", array(
                        'slider' => $slider
                    ));
                    break;
                default:
                    $this->addView("edit", array(
                        'slider' => $slider
                    ));
            }

            $this->render();

        }
    }

    public function actionDelete() {
        if ($this->validateToken() && $this->validatePermission('smartslider_delete')) {
            $slidersModel = new N2SmartsliderSlidersModel();
            $slidersModel->delete($this->sliderId);
            N2Message::success(n2_('Slider deleted.'));
            $this->redirectToSliders();
        }
    }

    public function actionDuplicate() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            $slidersModel = new N2SmartsliderSlidersModel();
            if (($sliderid = N2Request::getInt('sliderid')) && $slidersModel->get($sliderid)) {
                $newSliderId = $slidersModel->duplicate($sliderid);
                N2Message::success(n2_('Slider duplicated.'));
                $this->redirect(array(
                    "slider/edit",
                    array("sliderid" => $newSliderId)
                ));
            }
            $this->redirectToSliders();
        }
    }

    public function actionExport() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            N2Loader::import('libraries.export', 'smartslider');
            $export = new N2SmartSliderExport($this->sliderId);
            $export->create();
        }
    
    }

    public function actionExportHTML() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            N2Loader::import('libraries.export', 'smartslider');
            $export = new N2SmartSliderExport($this->sliderId);
            $export->createHTML();
        }
    
    }

    public function actionPublishHTML() {
    }

    public function actionShapeDivider() {
    }

    public function actionParticle() {
    }

}