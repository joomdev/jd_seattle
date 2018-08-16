<?php


class N2SystemBackendInstallController extends N2BackendController
{

    public function initialize() {

    }
    
    public function actionIndex($secured = false) {
        if ($secured) {
            N2Loader::import('models.Install', 'system');

            $installModel = new N2SystemInstallModel();

            $installModel->install();
        }
    }
}
