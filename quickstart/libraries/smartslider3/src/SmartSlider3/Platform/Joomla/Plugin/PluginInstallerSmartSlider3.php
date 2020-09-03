<?php

namespace Nextend\SmartSlider3\Platform\Joomla\Plugin;

use JFactory;
use JPlugin;
use Nextend\SmartSlider3\Application\Model\ModelLicense;
use Nextend\SmartSlider3\SmartSlider3Info;

class PluginInstallerSmartSlider3 extends JPlugin {

    public function onInstallerBeforePackageDownload(&$url, &$headers) {

        if (in_array(parse_url($url, PHP_URL_HOST), array(
                'secure.nextendweb.com',
                'api.nextendweb.com'
            )) && strpos($url, 'smartslider3')) {

            $license  = ModelLicense::getInstance();
            $isActive = $license->isActive() == 'OK';

            if (!$isActive) {
                JFactory::getApplication()
                        ->enqueueMessage('Update error: Smart Slider 3 Pro is not activated on your site!', 'error');

                $url = SmartSlider3Info::api(array(
                    'action' => 'joomla_fail'
                ), true);

                return false;
            }

            $url = SmartSlider3Info::api(array(
                'action'  => 'joomla_update',
                'channel' => SmartSlider3Info::$channel
            ), true);
        }

        return true;
    }
}