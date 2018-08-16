<?php
defined('_JEXEC') or die;

class plgInstallerSmartslider3 extends JPlugin {

    public function onInstallerBeforePackageDownload(&$url, &$headers) {
        if (parse_url($url, PHP_URL_HOST) == 'secure.nextendweb.com' && strpos($url, 'smartslider3')) {

            jimport("nextend2.nextend.joomla.library");
            N2Base::getApplication("smartslider")
                  ->getApplicationType('backend');
            $isActive = true;
        

            if (!$isActive) {
                JFactory::getApplication()
                        ->enqueueMessage('Update error, your Smart Slider 3 license key invalid, please enter again!', 'error');
            }

            $url = N2SS3::api(array(
                'action' => 'joomla_update'
            ), true);
        }

        return true;
    }
}