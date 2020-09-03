<?php


namespace Nextend\SmartSlider3\Platform\Joomla;


use JUri;
use Nextend\SmartSlider3\Platform\AbstractSmartSlider3Platform;

class SmartSlider3PlatformJoomla extends AbstractSmartSlider3Platform {

    public function start() {

        require_once(dirname(__FILE__) . '/compat.php');
    }


    public function getAdminUrl() {

        return JUri::root() . 'administrator/index.php?option=com_smartslider3';
    }

    public function getAdminAjaxUrl() {

        return JUri::root() . 'administrator/index.php?option=com_smartslider3&nextendajax=1';
    }
}