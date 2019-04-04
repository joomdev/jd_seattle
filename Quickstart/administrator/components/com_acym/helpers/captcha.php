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

class acymcaptchaHelper
{
    function display($formName = '')
    {
        $config = acym_config();
        $pubkey = $config->get('recaptcha_sitekey', '');
        if ($config->get('captcha', '') == '' || empty($pubkey)) {
            return '';
        }

        acym_addScript(false, 'https://www.google.com/recaptcha/api.js?render=explicit&hl='.acym_getLanguageTag(), "text/javascript", true, true);

        $id = empty($formName) ? 'acym-captcha' : $formName.'-captcha';

        return '<div id="'.$id.'" data-size="invisible" class="g-recaptcha" data-sitekey="'.$pubkey.'"></div>';
    }

    function check()
    {
        $config = acym_config();
        $secKey = acym_getVar('string', 'seckey', 'none');
        if ($secKey == $config->get('security_key')) {
            return true;
        }

        $privatekey = $config->get('recaptcha_secretkey', '');
        $response = acym_getVar('string', 'g-recaptcha-response', '');
        $remoteip = acym_getVar('string', 'REMOTE_ADDR', '', 'SERVER');
        if (empty($privatekey) || $response === '' || empty($remoteip)) {
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify?secret='.urlencode(stripslashes($privatekey));
        $url .= '&remoteip='.urlencode(stripslashes($remoteip));
        $url .= '&response='.urlencode(stripslashes($response));
        $getResponse = acym_fileGetContent($url);

        $answers = json_decode($getResponse, true);

        return (is_array($answers) && !empty($answers['success']) && trim($answers['success']) !== '');
    }
}
