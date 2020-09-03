<?php

namespace Nextend\Framework\Localization\Joomla;

use JFactory;
use Nextend\Framework\Localization\AbstractLocalization;

class JoomlaLocalization extends AbstractLocalization {

    public function getLocale() {

        $lang = JFactory::getLanguage();

        return str_replace('-', '_', $lang->getTag());
    }
}