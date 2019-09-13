<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class acymencodingHelper
{
    function change($data, $inputCharset, $outputCharset)
    {
        $inputCharset = strtoupper(trim($inputCharset));
        $outputCharset = strtoupper(trim($outputCharset));

        $supportedEncodings = ["BIG5", "ISO-8859-1", "ISO-8859-2", "ISO-8859-3", "ISO-8859-4", "ISO-8859-5", "ISO-8859-6", "ISO-8859-7", "ISO-8859-8", "ISO-8859-9", "ISO-8859-10", "ISO-8859-13", "ISO-8859-14", "ISO-8859-15", "ISO-2022-JP", "US-ASCII", "UTF-7", "UTF-8", "UTF-16", "WINDOWS-1251", "WINDOWS-1252", "ARMSCII-8", "ISO-8859-16"];
        if (!in_array($inputCharset, $supportedEncodings)) {
            acym_enqueueNotification('Encoding not supported: '.$inputCharset, 'error', 0);
        } elseif (!in_array($outputCharset, $supportedEncodings)) {
            acym_enqueueNotification('Encoding not supported: '.$outputCharset, 'error', 0);
        }

        if ($inputCharset == $outputCharset) {
            return $data;
        }

        if ($inputCharset == 'UTF-8' && $outputCharset == 'ISO-8859-1') {
            $data = str_replace(['€', '„', '“'], ['EUR', '"', '"'], $data);
        }

        if (function_exists('iconv')) {
            set_error_handler('acym_error_handler_encoding');
            $encodedData = iconv($inputCharset, $outputCharset."//IGNORE", $data);
            restore_error_handler();
            if (!empty($encodedData) && !acym_error_handler_encoding('result')) {
                return $encodedData;
            }
        }

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($data, $outputCharset, $inputCharset);
        }

        if ($inputCharset == 'UTF-8' && $outputCharset == 'ISO-8859-1') {
            return utf8_decode($data);
        }

        if ($inputCharset == 'ISO-8859-1' && $outputCharset == 'UTF-8') {
            return utf8_encode($data);
        }

        return $data;
    }

    function detectEncoding(&$content)
    {
        if (!function_exists('mb_check_encoding')) {
            return '';
        }

        $toTest = ['UTF-8'];

        $tag = acym_getLanguageTag();

        if ($tag == 'el-GR') {
            $toTest[] = 'ISO-8859-7';
        }
        $toTest[] = 'ISO-8859-1';
        $toTest[] = 'ISO-8859-2';
        $toTest[] = 'Windows-1252';

        foreach ($toTest as $oneEncoding) {
            if (mb_check_encoding($content, $oneEncoding)) {
                return $oneEncoding;
            }
        }

        return '';
    }

    function encodingField($name, $selected, $attribs = 'style="max-width:200px;"')
    {
        $encodings = [
            'binary' => 'Binary',
            'quoted' => 'Quoted-printable',
            '7bit' => '7 Bit',
            '8bit' => '8 Bit',
            'base64' => 'Base 64',
        ];
        $attribs .= 'style="max-width:200px;"';
        echo acym_select($encodings, $name, $selected, $attribs, '', '', 'config_encoding');
    }

    function charsetField($name, $selected, $attribs = null)
    {
        $charsets = [
            'BIG5' => 'BIG5',//Iconv,mbstring
            'ISO-8859-1' => 'ISO-8859-1',//Iconv,mbstring
            'ISO-8859-2' => 'ISO-8859-2',//Iconv,mbstring
            'ISO-8859-3' => 'ISO-8859-3',//Iconv,mbstring
            'ISO-8859-4' => 'ISO-8859-4',//Iconv,mbstring
            'ISO-8859-5' => 'ISO-8859-5',//Iconv,mbstring
            'ISO-8859-6' => 'ISO-8859-6',//Iconv,mbstring
            'ISO-8859-7' => 'ISO-8859-7',//Iconv,mbstring
            'ISO-8859-8' => 'ISO-8859-8',//Iconv,mbstring
            'ISO-8859-9' => 'ISO-8859-9',//Iconv,mbstring
            'ISO-8859-10' => 'ISO-8859-10',//Iconv,mbstring
            'ISO-8859-13' => 'ISO-8859-13',//Iconv,mbstring
            'ISO-8859-14' => 'ISO-8859-14',//Iconv,mbstring
            'ISO-8859-15' => 'ISO-8859-15',//Iconv,mbstring
            'ISO-2022-JP' => 'ISO-2022-JP',//mbstring for sure... not sure about Iconv
            'US-ASCII' => 'US-ASCII', //Iconv,mbstring
            'UTF-7' => 'UTF-7',//Iconv,mbstring
            'UTF-8' => 'UTF-8',//Iconv,mbstring
            'UTF-16' => 'UTF-16',//Iconv,mbstring
            'Windows-1251' => 'Windows-1251', //Iconv,mbstring
            'Windows-1252' => 'Windows-1252' //Iconv,mbstring
        ];

        if (function_exists('iconv')) {
            $charsets['ARMSCII-8'] = 'ARMSCII-8';
            $charsets['ISO-8859-16'] = 'ISO-8859-16';
        }

        echo acym_select($charsets, $name, $selected, $attribs, '', '');
    }
}

function acym_error_handler_encoding($errno, $errstr = '')
{
    static $error = false;
    if (is_string($errno) && $errno == 'result') {
        $currentError = $error;
        $error = false;

        return $currentError;
    }
    $error = true;

    return true;
}

