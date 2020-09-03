<?php

use Nextend\SmartSlider3\Platform\Joomla\JoomlaModule;

defined('_JEXEC') or die;

jimport("smartslider3.joomla");

if (class_exists('\Nextend\SmartSlider3\Platform\Joomla\JoomlaModule')) {
    new JoomlaModule($params);
}