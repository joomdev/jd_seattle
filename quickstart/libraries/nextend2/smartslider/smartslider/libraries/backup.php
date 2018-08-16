<?php

class N2SmartSliderBackup
{

    public $NextendImageHelper_Export, $slider, $slides, $generators = array(), $NextendImageManager_ImageData = array(), $imageTranslation = array(), $visuals = array();

    public function __construct() {
        $this->NextendImageHelper_Export = N2ImageHelper::export();
    }
}