<?php

class N2SmartsliderBackendSlidesController extends N2SmartSliderController {

    public $layoutName = 'default1c';

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.Sliders',
            'models.generator',
            'models.Layers',
            'models.Slides'
        ), 'smartslider');

        N2Localization::addJS(array(
            'In animation',
            'Loop animation',
            'Out animation',
            'Add Keyframe',
            'Layer Animation - Basic',
            'Split Text In',
            'Split Text Out',
        ));

        N2SS3::$forceDesktop = true;
    }

    private function getAdminSliderManager() {

        return new N2SmartSliderManager(N2Get::getInt('sliderid'), true, array(
            'disableResponsive' => true
        ));
    }

    public function actionCreate() {
        if ($this->validatePermission('smartslider_edit')) {
            $sliderId = N2Request::getInt('sliderid');

            $slidersModel = new N2SmartsliderSlidersModel();
            $slider       = $slidersModel->get($sliderId);
            if ($this->validateDatabase($slider)) {
                $sliderManager = $this->getAdminSliderManager();

                $xref   = new N2SmartsliderSlidersXrefModel();
                $groups = $xref->getGroups($sliderId);
                if (!empty($groups)) {
                    $this->layout->addBreadcrumb(N2Html::tag('a', array(
                        'href'  => $this->appType->router->createUrl(array(
                            "slider/edit",
                            array('sliderid' => $groups[0]['group_id'])
                        )),
                        'class' => 'n2-h4'
                    ), n2_esc_html($groups[0]['title'])));
                }

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "slider/edit",
                        array('sliderid' => $sliderId)
                    )),
                    'class' => 'n2-h4'
                ), n2_esc_html($slider['title'])));

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => '#',
                    'class' => 'n2-h4 n2-active'
                ), n2_('Add empty slide')));

                if (N2Request::getCmd('mode') == 'sample') {

                    $this->addView("edit", array(
                        "sliderManager" => $sliderManager,
                        "slidesModel"   => new N2SmartsliderSlidesModel(),
                        "sliderId"      => $sliderId,
                        "slider"        => $slider,
                        "isAddSample"   => true
                    ));

                    $this->render(array(
                        'class' => 'n2-ss-add-slide-with-sample'
                    ));
                } else {

                    $this->addView("edit", array(
                        "sliderManager" => $sliderManager,
                        "slidesModel"   => new N2SmartsliderSlidesModel(),
                        "sliderId"      => $sliderId,
                        "slider"        => $slider,
                        "isAddSample"   => false
                    ));

                    $this->render();
                }
            }
        }
    }

    public function actionEdit() {
        if ($this->validatePermission('smartslider_edit')) {
            $slidersModel = new N2SmartsliderSlidersModel();
            $sliderId     = N2Request::getInt('sliderid');
            $slider       = $slidersModel->get($sliderId);
            if ($this->validateDatabase($slider)) {
                $slidesModel = new N2SmartsliderSlidesModel();
                if (!($slide = $slidesModel->get(N2Request::getInt('slideid')))) {
                    $this->redirect("sliders/index");
                }

                $xref   = new N2SmartsliderSlidersXrefModel();
                $groups = $xref->getGroups($sliderId);
                if (!empty($groups)) {
                    $this->layout->addBreadcrumb(N2Html::tag('a', array(
                        'href'  => $this->appType->router->createUrl(array(
                            "slider/edit",
                            array('sliderid' => $groups[0]['group_id'])
                        )),
                        'class' => 'n2-h4'
                    ), n2_esc_html($groups[0]['title'])));
                }

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "slider/edit",
                        array('sliderid' => $sliderId)
                    )),
                    'class' => 'n2-h4'
                ), n2_esc_html($slider['title'])));

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "slides/edit",
                        array(
                            'sliderid' => $sliderId,
                            'slideid'  => $slide['id']
                        )
                    )),
                    'class' => 'n2-h4 n2-active'
                ), n2_esc_html($slide['title'])));

                if ($slide['generator_id'] > 0) {
                    $this->layout->addBreadcrumb(N2Html::tag('a', array(
                        'href'  => $this->appType->router->createUrl(array(
                            "generator/edit",
                            array(
                                'generator_id' => $slide['generator_id']
                            )
                        )),
                        'class' => 'n2-h4'
                    ), n2_('Edit generator')));
                }

                $this->addView("edit", array(
                    "sliderManager" => $this->getAdminSliderManager(),
                    "slidesModel"   => new N2SmartsliderSlidesModel(),
                    "sliderId"      => $sliderId,
                    "slider"        => $slider,
                    "isAddSample"   => false
                ));

                $this->render();
            }
        }
    }

    public function actionDelete() {
        if ($this->validateToken() && $this->validatePermission('smartslider_delete')) {
            if ($slideId = N2Request::getInt('slideid')) {
                $slidesModel = new N2SmartsliderSlidesModel();
                $slidesModel->delete($slideId);
            }

            $sliderId = N2Request::getInt("sliderid");
            if ($sliderId) {
                $this->redirect(array(
                    "slider/edit",
                    array(
                        "sliderid" => $sliderId
                    )
                ));
            }
            $this->redirect(array("sliders/index"));
        }
    }

    public function actionDuplicate() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            if ($slideId = N2Request::getInt('slideid')) {
                $slidesModel = new N2SmartsliderSlidesModel();
                $newSlideId  = $slidesModel->duplicate($slideId);

                N2Message::success(n2_('Slide duplicated.'));

                $this->redirect(array(
                    "slides/edit",
                    array(
                        "sliderid" => N2Request::getInt("sliderid"),
                        "slideid"  => $newSlideId
                    )
                ));
            }
            $this->redirect(array("sliders/index"));
        }
    }

    public function actionFirst() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            if (($slideId = N2Request::getInt('slideid')) && ($sliderid = N2Request::getInt('sliderid'))) {
                $slidesModel = new N2SmartsliderSlidesModel();
                $slidesModel->first($slideId);
                $this->redirect(N2Request::getUrlReferrer());
            }
            $this->redirect(array("sliders/index"));
        }
    }

    public function actionPublish() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            if ($slideId = N2Request::getInt('slideid')) {
                $slidesModel = new N2SmartsliderSlidesModel();
                $slidesModel->publish($slideId);
                $this->redirect(N2Request::getUrlReferrer());
            }
            $this->redirect(array("sliders/index"));
        }
    }

    public function actionUnPublish() {
        if ($this->validateToken() && $this->validatePermission('smartslider_edit')) {
            if ($slideId = N2Request::getInt('slideid')) {
                $slidesModel = new N2SmartsliderSlidesModel();
                $slidesModel->unpublish($slideId);
                $this->redirect(N2Request::getUrlReferrer());
            }
            $this->redirect(array("sliders/index"));
        }
    }

}