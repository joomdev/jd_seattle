<?php

class N2SystemBackendImageController extends N2SystemBackendVisualManagerController
{

    public $layoutName = "fulllightbox";

    protected $type = 'image';

    public function __construct($path, $appType, $defaultParams) {
        $this->logoText = n2_('Image manager');

        N2Localization::addJS(array(
            'Generate',
            'Desktop image is empty!',
            'image',
            'images'
        ));

        parent::__construct($path, $appType, $defaultParams);
    }

    public function getModel() {
        return new N2SystemImageModel();
    }
}