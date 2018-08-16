<?php

class N2SystemApplicationInfoFilter {

    /**
     * @param $info NextendApplicationInfo
     */
    public static function filter($info) {
        $info->setUrl(JUri::root() . 'administrator/index.php?option=com_nextend2');

        $info->setAssetsPath(JPATH_SITE . '/media/n2/n');

        $info->setAcl('com_nextend2');
    }
}