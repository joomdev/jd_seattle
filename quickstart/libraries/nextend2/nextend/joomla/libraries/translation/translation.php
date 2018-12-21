<?php


class N2Translation extends N2TranslationAbstract {


    public static function getCurrentLocale() {
        return JFactory::getLanguage()
                       ->getTag();
    }
}