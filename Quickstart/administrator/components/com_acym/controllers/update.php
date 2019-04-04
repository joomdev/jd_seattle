<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class updateController extends acymController
{


    function checkForNewVersion()
    {

        $config = acym_config();
        ob_start();

        $url = ACYM_UPDATEURL.'loadUserInformation&component=acymailing&level='.strtolower($config->get('level', 'starter')).'&version='.$config->get('version');

        if (acym_level(1)) {
            $url .= '&domain='.urlencode(rtrim(ACYM_LIVE, '/'));
        }
        $userInformation = acym_fileGetContent($url, 30);
        $warnings = ob_get_clean();
        $result = (!empty($warnings) && acym_isDebug()) ? $warnings : '';

        if (empty($userInformation) || $userInformation === false) {
            echo json_encode(array('content' => '<br/><span style="color:#C10000;">Could not load your information from our server</span><br/>'.$result));
            exit;
        }

        $decodedInformation = json_decode($userInformation, true);

        $newConfig = new stdClass();

        $newConfig->latestversion = $decodedInformation['latestversion'];
        $newConfig->expirationdate = $decodedInformation['expiration'];
        $newConfig->lastlicensecheck = time();
        $config->save($newConfig);

        $headerHelper = acym_get('helper.header');
        $myAcyArea = $headerHelper->checkVersionArea();

        echo json_encode(array('content' => $myAcyArea, 'lastcheck' => acym_date($newConfig->lastlicensecheck, 'Y/m/d H:i')));
        exit;
    }
}
