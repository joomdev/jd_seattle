<?php

use Nextend\SmartSlider3\Platform\Joomla\Plugin\PluginSmartSlider3;

defined('_JEXEC') or die;

jimport("smartslider3.joomla");

if (class_exists('\Nextend\SmartSlider3\Platform\Joomla\Plugin\PluginSmartSlider3')) {
    class_alias(PluginSmartSlider3::class, 'plgSystemSmartSlider3');
}