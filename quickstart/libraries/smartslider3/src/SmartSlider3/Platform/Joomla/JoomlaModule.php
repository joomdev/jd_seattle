<?php


namespace Nextend\SmartSlider3\Platform\Joomla;


use Joomla\Registry\Registry;

class JoomlaModule {

    /**
     * JoomlaModule constructor.
     *
     * @param Registry $params
     */
    public function __construct($params) {

        $sliderId = intval($params->get('slider'));

        if (defined('LITESPEED_ESI_SUPPORT')) {
            nextend_smartslider3($sliderId);
        } else {
            echo 'smartslider3[' . $sliderId . ']';
        }
    }
}