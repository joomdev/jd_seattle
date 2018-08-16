<?php

class N2SystemBackendContentControllerAjax extends N2BackendControllerAjax
{

    public function actionSearch() {
        $this->validateToken();
        N2Loader::import('libraries.models.content', 'platform');

        $keyword = N2Request::getVar('keyword', '');
        $this->response->respond(N2ModelsContent::search($keyword));
    }
}