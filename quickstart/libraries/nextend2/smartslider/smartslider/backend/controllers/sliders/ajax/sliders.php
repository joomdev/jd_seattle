<?php

class N2SmartsliderBackendSlidersControllerAjax extends N2SmartSliderControllerAjax {

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.Sliders',
            'models.Slides',
            'models.generator'
        ), 'smartslider');
    }


    public function actionOrder() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $slidersModel = new N2SmartsliderSlidersModel();
        $result       = $slidersModel->order(N2Request::getVar('groupID', 0), N2Request::getVar('sliderorder'), N2Request::getInt('isReversed', 1));
        $this->validateDatabase($result);

        N2Message::success(n2_('Slider order saved.'));
        $this->response->respond();
    }

    public function actionDelete() {
        $this->validateToken();

        $this->validatePermission('smartslider_delete');

        $ids = array_map('intval', array_filter((array)N2Request::getVar('sliders'), 'is_numeric'));

        $this->validateVariable(count($ids), 'Slide');

        $slidersModel = new N2SmartsliderSlidersModel();
        foreach ($ids AS $id) {
            if ($id > 0) {
                $slidersModel->delete($id);
            }
        }
        N2Message::success(n2_('Slider deleted.'));
        $this->response->respond();
    }


    public function actionListGroups() {
    }

    public function actionHideReview() {
        $this->validateToken();

        $this->appType->app->storage->set('free', 'review', 1);

        $this->response->respond();
    }

}