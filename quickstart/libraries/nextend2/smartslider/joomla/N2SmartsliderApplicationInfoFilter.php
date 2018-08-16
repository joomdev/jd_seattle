<?php

if (!defined('N2SSPRO')) {
    define('N2SSPRO', 0);

}

class N2SmartsliderApplicationInfoFilter {

    /**
     * @param $info NextendApplicationInfo
     */
    public static function filter($info) {
        $info->setUrl(JUri::root() . 'administrator/index.php?option=com_smartslider3');

        $info->setAssetsPath(JPATH_SITE . '/media/n2/ss3');

        $info->setAcl('com_smartslider3');
    }
}