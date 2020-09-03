<?php
defined('_JEXEC') or die('Restricted access');
?><?php

function acym_absoluteURL($text)
{
    static $mainurl = '';
    if (empty($mainurl)) {
        $urls = parse_url(ACYM_LIVE);
        if (!empty($urls['path'])) {
            $mainurl = substr(ACYM_LIVE, 0, strrpos(ACYM_LIVE, $urls['path'])).'/';
        } else {
            $mainurl = ACYM_LIVE;
        }
    }

    $text = str_replace(
        [
            'href="../undefined/',
            'href="../../undefined/',
            'href="../../../undefined//',
            'href="undefined/',
            ACYM_LIVE.'http://',
            ACYM_LIVE.'https://',
        ],
        [
            'href="'.$mainurl,
            'href="'.$mainurl,
            'href="'.$mainurl,
            'href="'.ACYM_LIVE,
            'http://',
            'https://',
        ],
        $text
    );
    $text = preg_replace('#href="(/?administrator)?/({|%7B)#Ui', 'href="$2', $text);

    $text = preg_replace('#href="http:/([^/])#Ui', 'href="http://$1', $text);

    $text = preg_replace(
        '#href="'.preg_quote(str_replace(['http://', 'https://'], '', $mainurl), '#').'#Ui',
        'href="'.$mainurl,
        $text
    );

    $replace = [];
    $replaceBy = [];
    if ($mainurl !== ACYM_LIVE) {

        $replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\[|\#|\\\\|[a-z]{3,15}:|/))(?:\.\./)#i';
        $replaceBy[] = '$1="'.substr(ACYM_LIVE, 0, strrpos(rtrim(ACYM_LIVE, '/'), '/') + 1);


        $subfolder = substr(ACYM_LIVE, strrpos(rtrim(ACYM_LIVE, '/'), '/'));
        $replace[] = '#(href|src|action|background)[ ]*=[ ]*\"'.preg_quote($subfolder, '#').'(\{|%7B)#i';
        $replaceBy[] = '$1="$2';
    }

    $replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\[|\#|\\\\|[a-z]{3,15}:|/))(?:\.\./|\./)?#i';
    $replaceBy[] = '$1="'.ACYM_LIVE;
    $replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\[|\#|\\\\|[a-z]{3,15}:))/#i';
    $replaceBy[] = '$1="'.$mainurl;

    $replace[] = '#((?:background-image|background)[ ]*:[ ]*url\((?:\'|"|&quot;)?(?!(\\\\|[a-z]{3,15}:|/|\'|"|&quot;))(?:\.\./|\./)?)#i';
    $replaceBy[] = '$1'.ACYM_LIVE;

    return preg_replace($replace, $replaceBy, $text);
}

function acym_mainURL(&$link)
{
    static $mainurl = '';
    static $otherarguments = false;
    if (empty($mainurl)) {
        $urls = parse_url(ACYM_LIVE);
        if (isset($urls['path']) && strlen($urls['path']) > 0) {
            $mainurl = substr(ACYM_LIVE, 0, strrpos(ACYM_LIVE, $urls['path'])).'/';
            $otherarguments = trim(str_replace($mainurl, '', ACYM_LIVE), '/');
            if (strlen($otherarguments) > 0) {
                $otherarguments .= '/';
            }
        } else {
            $mainurl = ACYM_LIVE;
        }
    }

    if ($otherarguments && strpos($link, $otherarguments) === false) {
        $link = $otherarguments.$link;
    }

    return $mainurl;
}

function acym_currentURL()
{
    $url = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $url .= '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    return $url;
}

function acym_isLocalWebsite()
{
    return strpos(ACYM_LIVE, 'localhost') !== false || strpos(ACYM_LIVE, '127.0.0.1') !== false;
}

