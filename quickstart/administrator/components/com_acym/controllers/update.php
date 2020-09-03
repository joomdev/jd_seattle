<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class updateController extends acymController
{
    public function checkForNewVersion()
    {
        $lastlicensecheck = acym_checkVersion(true);

        $headerHelper = acym_get('helper.header');
        $myAcyArea = $headerHelper->checkVersionArea();

        echo json_encode(['content' => $myAcyArea, 'lastcheck' => acym_date($lastlicensecheck, 'Y/m/d H:i')]);
        exit;
    }
}

