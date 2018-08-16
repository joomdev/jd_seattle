<?php

class N2SmartsliderBackendSlidesControllerAjax extends N2SmartSliderControllerAjax {

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.Slides',
            'models.Sliders',
            'models.generator'
        ), 'smartslider');
    }

    public function actionCreate() {
        $this->validateToken();
        $this->validatePermission('smartslider_edit');

        $slidersModel = new N2SmartsliderSlidersModel();
        $sliderId     = N2Request::getInt('sliderid');
        $slider       = $slidersModel->get($sliderId);
        $this->validateDatabase($slider);

        if (N2Request::getInt('save')) {

            if (N2SmartSliderSettings::get('slide-as-file', 0) && isset($_FILES['slide']) && N2Request::getVar('slide')) {
                N2Request::$storage['slide']['slide'] = N2Filesystem::readFile($_FILES['slide']['tmp_name']);
            }

            $slidesModel = new N2SmartsliderSlidesModel();
            $slideId     = $slidesModel->create($sliderId, N2Request::getVar('slide'));
            $this->validateDatabase($slideId);

            $this->response->redirect(array(
                "slides/edit",
                array(
                    "sliderid" => N2Request::getInt("sliderid"),
                    "slideid"  => $slideId
                )
            ));
        }
    }

    public function actionEdit() {
        $this->validateToken();
        $this->validatePermission('smartslider_edit');

        $slidersModel = new N2SmartsliderSlidersModel();
        $sliderId     = N2Request::getInt('sliderid');
        $slider       = $slidersModel->get($sliderId);

        $this->validateDatabase($slider);

        $slidesModel = new N2SmartsliderSlidesModel();
        $this->validateDatabase($slidesModel->get(N2Request::getInt('slideid')));

        $response = array();

        if (N2Request::getInt('save')) {

            if (N2SmartSliderSettings::get('slide-as-file', 0) && isset($_FILES['slide']) && N2Request::getVar('slide')) {
                N2Request::$storage['slide']['slide'] = N2Filesystem::readFile($_FILES['slide']['tmp_name']);
            }

            if ($slideId = $slidesModel->save(N2Request::getInt('slideid'), N2Request::getVar('slide'))) {
                N2Message::success(n2_('Slide saved.'));
                if (N2Request::getInt('static') == 1) {
                    $slideCount = $slidesModel->makeStatic(N2Request::getInt('slideid'));
                    if ($slideCount) {
                        N2Message::success(sprintf(n2_('%d static slides generated.'), $slideCount));

                        $this->response->redirect(array(
                            "slider/edit",
                            array(
                                "sliderid" => $sliderId
                            )
                        ));
                    }
                }
            }
        }
        $this->response->respond($response);
    }

    public function actionFirst() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $slideId = N2Request::getInt('id');
        $this->validateVariable($slideId > 0, 'Slide id');

        $slidesModel = new N2SmartsliderSlidesModel();
        $slidesModel->first($slideId);
        N2Message::success(n2_('First slide changed.'));

        $this->response->respond();
    }

    public function actionPublish() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $ids = array_map('intval', array_filter((array)N2Request::getVar('slides'), 'is_numeric'));

        $this->validateVariable(count($ids), 'Slides');

        $slidesModel = new N2SmartsliderSlidesModel();
        foreach ($ids AS $id) {
            if ($id > 0) {
                $slidesModel->publish($id);
            }
        }
        N2Message::success(n2_('Slide published.'));
        $this->response->respond();
    }

    public function actionUnPublish() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $ids = array_map('intval', array_filter((array)N2Request::getVar('slides'), 'is_numeric'));
        $this->validateVariable(count($ids), 'Slides');

        $slidesModel = new N2SmartsliderSlidesModel();
        foreach ($ids AS $id) {
            if ($id > 0) {
                $slidesModel->unpublish($id);
            }
        }
        N2Message::success(n2_('Slide unpublished.'));
        $this->response->respond();
    }

    public function actionOrder() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $sliderid = N2Request::getInt('sliderid');
        $this->validateVariable($sliderid > 0, 'Slider');

        $slidesModel = new N2SmartsliderSlidesModel();

        $result = $slidesModel->order($sliderid, N2Request::getVar('slideorder'));
        $this->validateDatabase($result);

        N2Message::success(n2_('Slide order saved.'));
        $this->response->respond();
    }

    public function actionCopy() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $slideId = N2Request::getInt('slideid');
        $this->validateVariable($slideId > 0, 'Slide');

        $sliderID = N2Request::getInt('targetSliderID');
        $this->validateVariable($sliderID > 0, 'Slider ID');

        $slidesModel = new N2SmartsliderSlidesModel();
        $newSlideId  = $slidesModel->copy($slideId, $sliderID);
        $slide       = $slidesModel->get($newSlideId);

        $this->validateDatabase($slide);

        N2Message::success(n2_('Slide(s) copied.'));


        $this->response->redirect(array(
            "slider/edit",
            array(
                "sliderid" => $sliderID
            )
        ));
    }

    public function actionCopySlides() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $ids = array_map('intval', array_filter((array)N2Request::getVar('slides'), 'is_numeric'));

        $this->validateVariable(count($ids), 'Slides');

        $sliderID = N2Request::getInt('targetSliderID');
        $this->validateVariable($sliderID > 0, 'Slider ID');

        $slidesModel = new N2SmartsliderSlidesModel();
        foreach ($ids AS $id) {
            $slidesModel->copy($id, $sliderID);
        }
        N2Message::success(n2_('Slide(s) copied.'));

        $this->response->redirect(array(
            "slider/edit",
            array(
                "sliderid" => $sliderID
            )
        ));
    }

    public function actionDuplicate() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $slideId = N2Request::getInt('slideid');
        $this->validateVariable($slideId > 0, 'Slide');

        $slidesModel = new N2SmartsliderSlidesModel();
        $newSlideId  = $slidesModel->duplicate($slideId);
        $slide       = $slidesModel->get($newSlideId);

        $this->validateDatabase($slide);

        N2Message::success(n2_('Slide duplicated.'));

        $sliderObj = new N2SmartSlider($slide['slider'], array());
        $sliderObj->loadSliderParams();
        $optimize = new N2SmartSliderFeatureOptimize($sliderObj);

        $slideObj = new N2SmartSliderSlide($sliderObj, $slide);
        $slideObj->initGenerator();
        $slideObj->fillSample();

        $this->addView('slidebox', array(
            'slider'   => $sliderObj,
            'slide'    => $slideObj,
            'optimize' => $optimize
        ));
        ob_start();
        $this->render();
        $box = ob_get_clean();
        $this->response->respond($box);
    }


    public function actionDelete() {
        $this->validateToken();

        $this->validatePermission('smartslider_delete');

        $ids = array_map('intval', array_filter((array)N2Request::getVar('slides'), 'is_numeric'));

        $this->validateVariable(count($ids), 'Slide');

        $slidesModel = new N2SmartsliderSlidesModel();
        foreach ($ids AS $id) {
            if ($id > 0) {
                $slidesModel->delete($id);
            }
        }
        N2Message::success(n2_('Slide deleted.'));
        $this->response->respond();
    }

    public function actionQuickImages() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $sliderId = N2Request::getInt('sliderid');
        $this->validateVariable($sliderId > 0, 'Slider');

        $images = json_decode(n2_base64_decode(N2Request::getVar('images')), true);
        $this->validateVariable(count($images), 'Images');

        $sliderObj = new N2SmartSlider($sliderId, array());
        $sliderObj->loadSliderParams();
        $optimize = new N2SmartSliderFeatureOptimize($sliderObj);

        $slidesModel = new N2SmartsliderSlidesModel();
        foreach ($images AS $image) {
            $newSlideId = $slidesModel->createQuickImage($image, $sliderId);

            $slide      = $slidesModel->get($newSlideId);

            $slideObj = new N2SmartSliderSlide($sliderObj, $slide);
            $slideObj->initGenerator();
            $slideObj->fillSample();

            $this->addView('slidebox', array(
                'slider'   => $sliderObj,
                'slide'    => $slideObj,
                'optimize' => $optimize
            ));
        }

        ob_start();
        $this->render();
        $box = ob_get_clean();
        N2Message::success(n2_n('Slide created.', 'Slides created.', count($images)));
        $this->response->respond($box);
    }

    public function actionQuickVideo() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $sliderId = N2Request::getInt('sliderid');
        $this->validateVariable($sliderId > 0, 'Slider');

        $slidesModel = new N2SmartsliderSlidesModel();

        $s     = urldecode(n2_base64_decode(N2Request::getVar('video')));
        $video = json_decode($s, true);
        $this->validateVariable($video, 'Video');

        $newSlideId = $slidesModel->createQuickVideo($video, $sliderId);
        $slide      = $slidesModel->get($newSlideId);
        $this->validateDatabase($slide);

        $sliderObj = new N2SmartSlider($slide['slider'], array());
        $sliderObj->loadSliderParams();
        $optimize = new N2SmartSliderFeatureOptimize($sliderObj);

        $slideObj = new N2SmartSliderSlide($sliderObj, $slide);
        $slideObj->initGenerator();
        $slideObj->fillSample();

        $this->addView('slidebox', array(
            'slider'   => $sliderObj,
            'slide'    => $slideObj,
            'optimize' => $optimize
        ));

        ob_start();
        $this->render();
        $box = ob_get_clean();
        N2Message::success(n2_('Slide created.'));
        $this->response->respond($box);
    }

    public function actionQuickPost() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $sliderId = N2Request::getInt('sliderid');
        $this->validateVariable($sliderId > 0, 'Slider');

        $slidesModel = new N2SmartsliderSlidesModel();
        $post        = N2Request::getVar('post');
        $this->validateVariable($post, 'Post');

        $newSlideId = $slidesModel->createQuickPost($post, $sliderId);
        $slide      = $slidesModel->get($newSlideId);
        $this->validateDatabase($slide);

        $sliderObj = new N2SmartSlider($slide['slider'], array());
        $sliderObj->loadSliderParams();
        $optimize = new N2SmartSliderFeatureOptimize($sliderObj);

        $slideObj = new N2SmartSliderSlide($sliderObj, $slide);
        $slideObj->initGenerator();
        $slideObj->fillSample();

        $this->addView('slidebox', array(
            'slider'   => $sliderObj,
            'slide'    => $slideObj,
            'optimize' => $optimize
        ));

        ob_start();
        $this->render();
        $box = ob_get_clean();
        N2Message::success(n2_('Slide created.'));
        $this->response->respond($box);
    }

    public function actionQuickEdit() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $sliderId = N2Request::getInt('sliderid');
        $this->validateVariable($sliderId > 0, 'Slider');

        $slidesModel = new N2SmartsliderSlidesModel();
        $slides      = $slidesModel->getAll($sliderId);

        $changed = json_decode(n2_base64_decode(N2Request::getVar('changed')), true);

        if (!$changed || !is_array($changed)) {
            $changed = array();
        }

        foreach ($slides AS $slide) {
            if (!empty($changed[$slide['id']])) {
                $slidesModel->quickSlideUpdate($slide, $changed[$slide['id']]['name'], $changed[$slide['id']]['description'], $changed[$slide['id']]['link']);
            }
        }

        $sliderObj = new N2SmartSlider($sliderId, array());
        $slides    = $slidesModel->getAll($sliderId);

        $slidesObj = array();
        foreach ($slides AS $i => $slide) {
            if (!empty($changed[$slide['id']])) {
                $slidesObj[$i] = new N2SmartSliderSlide($sliderObj, $slide);
                $slidesObj[$i]->initGenerator();
            }
        }

        $updateSlideBox = array();
        /** @var N2SmartSliderSlide $slideObj */
        foreach ($slidesObj AS $slideObj) {
            $slideObj->fillSample();
            $updateSlideBox[$slideObj->id] = array(
                'title'          => $slideObj->getTitle() . ($slideObj->hasGenerator() ? ' [' . $slideObj->getSlideStat() . ']' : ''),
                'rawTitle'       => $slideObj->getRawTitle(),
                'rawDescription' => $slideObj->getRawDescription(),
                'rawLink'        => $slideObj->getRawLink()
            );
        }

        N2Message::success(sprintf(n2_('%d slide(s) modified!'), count($slidesObj)));

        $this->response->respond($updateSlideBox);
    }
} 