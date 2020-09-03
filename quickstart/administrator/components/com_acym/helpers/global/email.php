<?php
defined('_JEXEC') or die('Restricted access');
?><?php

function acym_getEmailRegex($secureJS = false, $forceRegex = false)
{
    $config = acym_config();
    if ($forceRegex || $config->get('special_chars', 0) == 0) {
        $regex = '[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*\@([a-z0-9-]+\.)+[a-z0-9]{2,20}';
    } else {
        $regex = '.+\@(.+\.)+.{2,20}';
    }

    if ($secureJS) {
        $regex = str_replace(['"', "'"], ['\"', "\'"], $regex);
    }

    return $regex;
}

function acym_isValidEmail($email, $extended = false)
{
    if (empty($email) || !is_string($email)) {
        return false;
    }

    if (!preg_match('/^'.acym_getEmailRegex().'$/i', $email)) {
        return false;
    }

    if (!$extended) {
        return true;
    }


    $config = acym_config();

    if ($config->get('email_checkdomain', false) && function_exists('getmxrr')) {
        $domain = substr($email, strrpos($email, '@') + 1);
        $mxhosts = [];
        $checkDomain = getmxrr($domain, $mxhosts);
        if (!empty($mxhosts) && strpos($mxhosts[0], 'hostnamedoesnotexist')) {
            array_shift($mxhosts);
        }
        if (!$checkDomain || empty($mxhosts)) {
            $dns = @dns_get_record($domain, DNS_A);
            $domainChanged = true;
            foreach ($dns as $oneRes) {
                if (strtolower($oneRes['host']) == strtolower($domain)) {
                    $domainChanged = false;
                }
            }
            if (empty($dns) || $domainChanged) {
                return false;
            }
        }
    }

    $object = new stdClass();
    $object->IP = acym_getIP();
    $object->emailAddress = $email;

    if ($config->get('email_iptimecheck', 0)) {
        $lapseTime = time() - 7200;
        $nbUsers = acym_loadResult('SELECT COUNT(*) FROM #__acym_user WHERE creation_date > '.intval($lapseTime).' AND ip = '.acym_escapeDB($object->IP));
        if ($nbUsers >= 3) {
            return false;
        }
    }

    return true;
}

