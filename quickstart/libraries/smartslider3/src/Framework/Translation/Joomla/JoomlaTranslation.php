<?php

namespace Nextend\Framework\Translation\Joomla;

use JFactory;
use Nextend\Framework\Translation\AbstractTranslation;

class JoomlaTranslation extends AbstractTranslation {

    public function getLocale() {
        return JFactory::getLanguage()
                       ->getTag();
    }
}