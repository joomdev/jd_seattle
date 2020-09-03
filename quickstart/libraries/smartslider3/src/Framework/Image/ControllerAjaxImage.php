<?php


namespace Nextend\Framework\Image;


use Nextend\Framework\Controller\Admin\AdminVisualManagerAjaxController;
use Nextend\Framework\Notification\Notification;
use Nextend\Framework\Request\Request;
use Nextend\Framework\ResourceTranslator\ResourceTranslator;

class ControllerAjaxImage extends AdminVisualManagerAjaxController {

    protected $type = 'image';

    public function actionLoadVisualForImage() {
        $this->validateToken();
        $model  = $this->getModel();
        $image  = Request::$REQUEST->getVar('image');
        $visual = $model->getVisual($image);
        if (!empty($visual)) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        } else {

            if (($visual = $model->addVisual($image, ImageStorage::$emptyImage))) {
                $this->response->respond(array(
                    'visual' => $visual
                ));
            }
        }

        Notification::error(n2_('Unexpected error'));
        $this->response->error();
    }

    public function actionGenerateImage() {
        $this->validateToken();

        $device = Request::$REQUEST->getVar('device');
        $this->validateVariable($device == 'tablet' || $device == 'mobile', 'device');

        $image = Request::$REQUEST->getVar('image');
        $this->validateVariable(!empty($image), 'image');

        $scale = array(
            'tablet' => 0.5,
            'mobile' => 0.3
        );

        $newImage = ImageEdit::scaleImage('image', $image, $scale[$device], true);

        $this->response->respond(array(
            'image' => ResourceTranslator::toUrl($newImage)
        ));
    }

    public function actionAddVisual() {
        $this->validateToken();

        $image = Request::$REQUEST->getVar('image');
        $this->validateVariable(!empty($image), 'image');

        $model = $this->getModel();

        if (($visual = $model->addVisual($image, Request::$REQUEST->getVar('value')))) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        }

        Notification::error(n2_('Unexpected error'));
        $this->response->error();
    }

    public function actionDeleteVisual() {
        $this->validateToken();

        $visualId = Request::$REQUEST->getInt('visualId');
        $this->validateVariable($visualId > 0, 'image');

        $model = $this->getModel();

        if (($visual = $model->deleteVisual($visualId))) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        }

        Notification::error(n2_('Not editable'));
        $this->response->error();
    }

    public function actionChangeVisual() {
        $this->validateToken();

        $visualId = Request::$REQUEST->getInt('visualId');
        $this->validateVariable($visualId > 0, 'image');

        $model = $this->getModel();

        if (($visual = $model->changeVisual($visualId, Request::$REQUEST->getVar('value')))) {
            $this->response->respond(array(
                'visual' => $visual
            ));
        }

        Notification::error(n2_('Unexpected error'));
        $this->response->error();
    }

    public function getModel() {
        return new ModelImage($this);
    }
}