<?php

class N2SystemBackendLinkControllerAjax extends N2BackendControllerAjax
{

    public function actionSearch() {
        $this->validateToken();
        N2Loader::import('libraries.models.link', 'platform');

        $keyword = N2Request::getVar('keyword', '');
        $this->response->respond(N2ModelsLink::search($keyword));
    }
}