<?php

use Nextend\SmartSlider3\Platform\Joomla\Plugin\PluginInstallerSmartSlider3;

defined('_JEXEC') or die;

jimport("smartslider3.joomla");

if (class_exists('\Nextend\SmartSlider3\Platform\Joomla\Plugin\PluginInstallerSmartSlider3')) {
    class_alias(PluginInstallerSmartSlider3::class, 'plgInstallerSmartslider3');
}