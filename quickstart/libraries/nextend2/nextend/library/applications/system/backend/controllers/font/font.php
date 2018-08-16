<?php

class N2SystemBackendFontController extends N2SystemBackendVisualManagerController
{

    protected $type = 'font';

    public function __construct($path, $appType, $defaultParams) {
        $this->logoText = n2_('Font manager');

        N2Localization::addJS(array(
            'font',
            'fonts',
        ));

        parent::__construct($path, $appType, $defaultParams);
    }

    public function getModel() {
        return new N2SystemFontModel();
    }
}