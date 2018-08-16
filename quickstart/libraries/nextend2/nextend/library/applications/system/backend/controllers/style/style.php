<?php

class N2SystemBackendStyleController extends N2SystemBackendVisualManagerController
{

    protected $type = 'style';

    public function __construct($path, $appType, $defaultParams) {
        $this->logoText = n2_('Style manager');

        N2Localization::addJS(array(
            'style',
            'styles',
        ));

        parent::__construct($path, $appType, $defaultParams);
    }

    public function getModel() {
        return new N2SystemStyleModel();
    }

}