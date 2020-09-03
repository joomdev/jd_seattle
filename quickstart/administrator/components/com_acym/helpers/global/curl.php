<?php
defined('_JEXEC') or die('Restricted access');
?><?php

function acym_makeCurlCall($url, $fields)
{
    $urlPost = '';
    foreach ($fields as $key => $value) {
        $urlPost .= $key.'='.urlencode($value).'&';
    }

    $urlPost = trim($urlPost, '&');

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $urlPost);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);

        curl_close($ch);

        return ['error' => $error];
    }

    curl_close($ch);

    return json_decode($result, true);
}

