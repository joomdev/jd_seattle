<?php
defined('_JEXEC') or die('Restricted access');
?><?php

function acym_escape($text, $isURL = false)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function acym_arrayToInteger(&$array)
{
    if (is_array($array)) {
        $array = array_map('intval', $array);
    } else {
        $array = [];
    }
}

function acym_getIP()
{
    $map = [
        'HTTP_X_FORWARDED_IP',
        'X_FORWARDED_FOR',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ];

    $ipAddress = '';
    foreach ($map as $oneAttribute) {
        if (empty($_SERVER[$oneAttribute]) || strlen($_SERVER[$oneAttribute]) < 7) continue;

        $ipAddress = $_SERVER[$oneAttribute];
        break;
    }

    if (strstr($ipAddress, ',') !== false) {
        $addresses = explode(',', $ipAddress);
        $ipAddress = trim(end($addresses));
    }

    return strip_tags($ipAddress);
}

function acym_generateKey($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    $max = strlen($characters) - 1;
    for ($i = 0 ; $i < $length ; $i++) {
        $randstring .= $characters[mt_rand(0, $max)];
    }

    return $randstring;
}

function acym_isRobot()
{
    if (empty($_SERVER)) {
        return false;
    }
    if (!empty($_SERVER['HTTP_USER_AGENT']) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'spambayes') !== false) {
        return true;
    }
    if (!empty($_SERVER['REMOTE_ADDR']) && version_compare($_SERVER['REMOTE_ADDR'], '64.235.144.0', '>=') && version_compare($_SERVER['REMOTE_ADDR'], '64.235.159.255', '<=')) {
        return true;
    }

    return false;
}

function acym_displayErrors()
{
    error_reporting(E_ALL);
    @ini_set("display_errors", 1);
}

function acym_checkRobots()
{
    if (preg_match('#(libwww-perl|python|googlebot)#i', @$_SERVER['HTTP_USER_AGENT'])) {
        die('Not allowed for robots. Please contact us if you are not a robot');
    }
}

function acym_noCache()
{
    acym_header('Cache-Control: no-store, no-cache, must-revalidate');
    acym_header('Cache-Control: post-check=0, pre-check=0', false);
    acym_header('Pragma: no-cache');
    acym_header('Expires: Wed, 17 Sep 1975 21:32:10 GMT');
}

