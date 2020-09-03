<?php


namespace Nextend\SmartSlider3\Application\Admin\Help;


use Nextend\Framework\Api;
use Nextend\Framework\Model\StorageSectionManager;
use Nextend\Framework\Notification\Notification;
use Nextend\SmartSlider3\Application\Admin\AbstractControllerAdmin;

class ControllerHelp extends AbstractControllerAdmin {

    public function actionIndex() {

        $view = new ViewHelpIndex($this);
        $view->display();

    }

    public function actionBrowserIncompatible() {

        $view = new ViewHelpBrowserIncompatible($this);
        $view->display();
    }

    public function actionTestApi() {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Api::getApiUrl());

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $errorFile = dirname(__FILE__) . '/curl_error.txt';
        $out       = fopen($errorFile, "w");
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, $out);

        $output = curl_exec($ch);

        curl_close($ch);
        fclose($out);
        $log   = array("API Connection Test");
        $log[] = htmlspecialchars(file_get_contents($errorFile));
        unlink($errorFile);

        if (!empty($output)) {
            $log[] = "RESPONSE: " . htmlspecialchars($output);
        }

        if (strpos($output, 'ACTION_MISSING') === false) {
            Notification::error(sprintf(n2_('Unable to connect to the API (%s).') . '<br>' . n2_('See <b>Debug Information</b> for more details!'), Api::getApiUrl()));
        } else {
            Notification::notice(n2_('Successful connection with the API.'));
        }

        $log[] = '------------------------------------------';
        $log[] = '';

        StorageSectionManager::getStorage('smartslider')
                             ->set('log', 'api', json_encode($log));

        $this->redirect($this->getUrlHelp());

    }
}