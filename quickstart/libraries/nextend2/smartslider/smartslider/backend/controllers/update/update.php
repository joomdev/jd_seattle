<?php
class N2SmartsliderBackendUpdateController extends N2SmartSliderController {

    public function actionCheck() {
        if ($this->validateToken()) {
            $status = N2SmartsliderUpdateModel::getInstance()
                                              ->check();

            $hasError = N2SS3::hasApiError($status);
            if (is_array($hasError)) {
                $this->redirect($hasError);
            }

            $updateModel = N2SmartsliderUpdateModel::getInstance();

            if ($updateModel->hasUpdate()) {
                $this->appType->router->setMultiSite();
                $updateUrl = $this->appType->router->createUrl(array(
                    'update/update',
                    N2Form::tokenizeUrl() + array('download' => 1)
                ));
                $this->appType->router->unSetMultiSite();
                N2Message::notice(sprintf(n2_('Update found, <a href="%s">Click here to update!</a>'), $updateUrl));
            } else {
                N2Message::notice(n2_('You have the latest version!'));
            }

            $this->redirectToSliders();
        } else {
            $this->refresh();
        }
    
    }


    public function actionUpdate() {
        if ($this->validateToken()) {
            $status = N2SmartsliderUpdateModel::getInstance()
                                              ->update();
            // Used when WP need to request FTP credentials
            if ($status != 'CREDENTIALS') {
                $hasError = N2SS3::hasApiError($status);
                if (is_array($hasError)) {
                    $this->redirect($hasError);
                } else if ($hasError === false) {
                    N2Message::success(n2_('Smart Slider 3 updated to the latest version!'));
                }

                $this->redirectToSliders();
            }
        } else {
            $this->refresh();
        }
    
    }

}
