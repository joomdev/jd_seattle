<?php

class N2SystemBackendCssControllerAjax extends N2BackendControllerAjax {

    protected $permission = 'nextend_visual_';

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.css'
        ), 'system');
    }

    public function getModel() {
        return new N2SystemCssModel();
    }

    public function actionLoadVisuals() {
        $this->validateToken();


        $type = N2Request::getCmd('type');
        $this->validateVariable(!empty($type), 'type');

        $model   = $this->getModel();
        $visuals = $model->getVisuals($type);
        if (is_array($visuals)) {
            $this->response->respond(array(
                'visuals' => $visuals
            ));
        }

        N2Message::error(n2_('Unexpected error'));
        $this->response->error();
    }

    public function actionAddVisual() {
        $this->validateToken();

        $this->validatePermission($this->permission . 'edit');

        $type = N2Request::getCmd('type');
        $this->validateVariable(!empty($type), 'type');

        $model = $this->getModel();

        if (($visual = $model->addVisual($type, N2Request::getVar('value')))) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        }

        N2Message::error(n2_('Not editable'));
        $this->response->error();
    }

    public function actionDeleteVisual() {
        $this->validateToken();

        $this->validatePermission($this->permission . 'delete');

        $type = N2Request::getCmd('type');
        $this->validateVariable(!empty($type), 'type');

        $visualId = N2Request::getInt('visualId');
        $this->validateVariable($visualId > 0, 'visual');

        $model = $this->getModel();

        if (($visual = $model->deleteVisual($type, $visualId))) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        }

        N2Message::error(n2_('Not editable'));
        $this->response->error();
    }

    public function actionChangeVisual() {
        $this->validateToken();

        $this->validatePermission($this->permission . 'edit');

        $type = N2Request::getCmd('type');
        $this->validateVariable(!empty($type), 'type');

        $visualId = N2Request::getInt('visualId');
        $this->validateVariable($visualId > 0, 'visual');

        $model = $this->getModel();

        if (($visual = $model->changeVisual($type, $visualId, N2Request::getVar('value')))) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        }

        N2Message::error(n2_('Unexpected error'));
        $this->response->error();
    }

}