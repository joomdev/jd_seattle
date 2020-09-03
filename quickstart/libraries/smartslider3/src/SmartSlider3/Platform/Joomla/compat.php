<?php

use Nextend\SmartSlider3\Application\ApplicationSmartSlider3;

function nextend_smartslider3($sliderId, $usage = 'Used in PHP') {

    $applicationTypeFrontend = ApplicationSmartSlider3::getInstance()
                                                      ->getApplicationTypeFrontend();

    $applicationTypeFrontend->process('slider', 'display', false, array(
        'sliderID' => $sliderId,
        'usage'    => $usage
    ));
}