<?php

class N2SmartsliderBackendSettingsControllerAjax extends N2SmartSliderControllerAjax {

    public function actionRated() {
        $this->validateToken();
        $this->appType->app->storage->set('free', 'rated', 1);
        $this->response->respond();
    }

    public function actionSubscribed() {
        $this->validateToken();
        $this->appType->app->storage->set('free', 'subscribeOnImport', 1);
    
        $this->response->respond();
    }

    public function actionHideContentMode() {
        $this->validateToken();
        $this->appType->app->storage->set('tutorial', 'hideContentMode', 1);
        $this->response->respond();
    }

    public function actionHideTutorialFree() {
        $this->validateToken();
        $this->appType->app->storage->set('tutorial', 'free', 1);
        $this->response->respond();
    }
}