<?php

abstract class N2SystemBackendVisualManagerControllerAjax extends N2BackendControllerAjax
{

    protected $type = '';

    protected $permission = 'nextend_visual_';

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.visual',
            'models.' . $this->type
        ), 'system');
    }

    /**
     * @return N2SystemVisualModel
     */
    public abstract function getModel();

    public function  actionCreateSet() {
        $this->validateToken();

        $this->validatePermission($this->permission . 'edit');

        $name = N2Request::getVar('name');
        $this->validateVariable(!empty($name), 'set name');

        $model = $this->getModel();
        if (($set = $model->createSet($name))) {
            $this->response->respond(array(
                'set' => $set
            ));
        }

        N2Message::error(n2_('Unexpected error'));
        $this->response->error();
    }

    public function actionRenameSet() {
        $this->validateToken();

        $this->validatePermission($this->permission . 'edit');

        $setId = N2Request::getInt('setId');
        $this->validateVariable($setId > 0, 'set');

        $name = N2Request::getVar('name');
        $this->validateVariable(!empty($name), 'set name');

        $model = $this->getModel();

        if (($set = $model->renameSet($setId, $name))) {
            $this->response->respond(array(
                'set' => $set
            ));
        }

        N2Message::error(n2_('Set is not editable'));
        $this->response->error();
    }

    public function actionDeleteSet() {
        $this->validateToken();

        $this->validatePermission($this->permission . 'delete');

        $setId = N2Request::getInt('setId');
        $this->validateVariable($setId > 0, 'set');

        $model = $this->getModel();

        if (($set = $model->deleteSet($setId))) {
            $this->response->respond(array(
                'set' => $set
            ));
        }

        N2Message::error(n2_('Set is not editable'));
        $this->response->error();
    }

    public function actionLoadVisualsForSet() {
        $this->validateToken();


        $setId = N2Request::getInt('setId');
        $this->validateVariable($setId > 0, 'set');

        $model   = $this->getModel();
        $visuals = $model->getVisuals($setId);
        if (is_array($visuals)) {
            $this->response->respond(array(
                'visuals' => $visuals
            ));
        }

        N2Message::error(n2_('Unexpected error'));
        $this->response->error();
    }

    public function actionLoadSetByVisualId() {
        $this->validateToken();

        $visualId = N2Request::getInt('visualId');
        $this->validateVariable($visualId > 0, 'visual');

        $model = $this->getModel();

        $set = $model->getSetByVisualId($visualId);

        if (is_array($set) && is_array($set['visuals'])) {
            $this->response->respond(array(
                'set' => $set
            ));
        }

        N2Message::error(n2_('Visual do not exists'));
        $this->response->error();
    }

    public function actionAddVisual() {
        $this->validateToken();

        $this->validatePermission($this->permission . 'edit');

        $setId = N2Request::getInt('setId');
        $this->validateVariable($setId > 0, 'set');

        $model = $this->getModel();

        if (($visual = $model->addVisual($setId, N2Request::getVar('value')))) {
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

        $visualId = N2Request::getInt('visualId');
        $this->validateVariable($visualId > 0, 'visual');

        $model = $this->getModel();

        if (($visual = $model->deleteVisual($visualId))) {
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

        $visualId = N2Request::getInt('visualId');
        $this->validateVariable($visualId > 0, 'visual');

        $model = $this->getModel();

        if (($visual = $model->changeVisual($visualId, N2Request::getVar('value')))) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        }

        N2Message::error(n2_('Unexpected error'));
        $this->response->error();
    }

}