<?php
$sliderId = intval($params->get('slider'));

if (defined('LITESPEED_ESI_SUPPORT')) {
    nextend_smartslider3($sliderId);
} else {
    echo 'smartslider3[' . $sliderId . ']';
}