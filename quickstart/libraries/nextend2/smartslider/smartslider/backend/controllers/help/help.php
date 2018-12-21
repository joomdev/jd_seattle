<?php

class N2SmartsliderBackendHelpController extends N2SmartSliderController {

    public $layoutName = 'default1c';

    public function actionIndex() {

        N2Loader::import('models.Conflicts', 'smartslider.platform');

        $this->addView('index');
        $this->render();

    }

    public function actionTestApi() {

        $storage = $this->appType->app->storage;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, N2::getApiUrl());

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
            N2Message::error(sprintf(n2_('Unable to connect to the API (%s).') . '<br>' . n2_('See <b>Debug Information</b> for more details!'), N2::getApiUrl()));
        } else {
            N2Message::notice(n2_('Successful connection with the API.'));
        }

        $log[] = '------------------------------------------';
        $log[] = '';

        $storage->set('log', 'api', json_encode($log));

        $this->redirect('help/index');

    }
}