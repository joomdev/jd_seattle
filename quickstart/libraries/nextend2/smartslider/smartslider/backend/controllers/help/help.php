<?php

class N2SmartsliderBackendHelpController extends N2SmartSliderController {

    public $layoutName = 'default1c';

    public function actionIndex() {

        N2Loader::import('models.Conflicts', 'smartslider.platform');

        $this->addView('index');
        $this->render();

    }
}