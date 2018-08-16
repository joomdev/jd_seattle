<?php

class N2Localization extends N2LocalizationAbstract
{

    static function getLocale() {
        $lang = JFactory::getLanguage();
        return str_replace('-', '_', $lang->getTag());
    }
}