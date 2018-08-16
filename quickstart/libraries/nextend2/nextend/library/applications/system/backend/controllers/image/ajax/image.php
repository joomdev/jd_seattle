<?php

class N2SystemBackendImageControllerAjax extends N2SystemBackendVisualManagerControllerAjax
{

    protected $type = 'image';

    public function actionLoadVisualForImage() {
        $this->validateToken();
        $model  = $this->getModel();
        $image  = N2Request::getVar('image');
        $visual = $model->getVisual($image);
        if (!empty($visual)) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        } else {

            if (($visual = $model->addVisual($image, N2StorageImage::$emptyImage))) {
                $this->response->respond(array(
                    'visual' => $visual
                ));
            }
        }

        N2Message::error(n2_('Unexpected error'));
        $this->response->error();
    }

    public function actionGenerateImage() {
        $this->validateToken();

        $device = N2Request::getVar('device');
        $this->validateVariable($device == 'tablet' || $device == 'mobile', 'device');

        $image = N2Request::getVar('image');
        $this->validateVariable(!empty($image), 'image');

        N2Loader::import('libraries.image.image');
        $scale = array(
            'tablet' => 0.5,
            'mobile' => 0.3
        );

        $newImage = N2Image::scaleImage('image', $image, $scale[$device], true);

        $this->response->respond(array(
            'image' => N2ImageHelper::fixed($newImage)
        ));
    }

    public function actionAddVisual() {
        $this->validateToken();

        $image = N2Request::getVar('image');
        $this->validateVariable(!empty($image), 'image');

        $model = $this->getModel();

        if (($visual = $model->addVisual($image, N2Request::getVar('value')))) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        }

        N2Message::error(n2_('Unexpected error'));
        $this->response->error();
    }

    public function actionDeleteVisual() {
        $this->validateToken();

        $visualId = N2Request::getInt('visualId');
        $this->validateVariable($visualId > 0, 'image');

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

        $visualId = N2Request::getInt('visualId');
        $this->validateVariable($visualId > 0, 'image');

        $model = $this->getModel();

        if (($visual = $model->changeVisual($visualId, N2Request::getVar('value')))) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        }

        N2Message::error(n2_('Unexpected error'));
        $this->response->error();
    }

    public function getModel() {
        return new N2SystemImageModel();
    }
}