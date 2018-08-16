<?php


class N2SmartsliderBackendInstallController extends N2SmartSliderController
{

    public function initialize() {

    }

    public function actionIndex($secured = false) {
        if ($secured) {
            N2Loader::import('models.Install', 'smartslider');

            $installModel = new N2SmartsliderInstallModel();

            $installModel->install();
        }
    }
} 