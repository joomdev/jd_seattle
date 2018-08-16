<?php
if (!defined("N2_PLATFORM_LIBRARY")) define('N2_PLATFORM_LIBRARY', dirname(__FILE__));

define('N2WORDPRESS', 0);
define('N2JOOMLA', 1);
define('N2MAGENTO', 0);
define('N2NATIVE', 0);

if (!defined('N2PRO')) {
    define('N2PRO', 0);

}

if (!defined('N2LIBRARYASSETS')) define('N2LIBRARYASSETS', JPATH_SITE . '/media/n2/n');


// Load required UTF-8 config from Joomla
jimport('joomla.utilities.string');
class_exists('JString');

if (!defined('JPATH_NEXTEND_IMAGES')) {
    define('JPATH_NEXTEND_IMAGES', '/' . trim(JComponentHelper::getParams('com_media')
                                                              ->get('image_path', 'images'), "/"));
}

require_once N2_PLATFORM_LIBRARY . '/../library/library.php';
N2Base::registerApplication(N2_PLATFORM_LIBRARY . '/../library/applications/system/N2SystemApplicationInfo.php');


function N2JoomlaExit() {
    if (N2Platform::$isAdmin) {
        $lifetime = JFactory::getConfig()
                            ->get('lifetime');
        if (empty($lifetime)) {
            $lifetime = 60;
        };
        $lifetime = min(max(intval($lifetime) - 1, 9), 60 * 24);
        N2JS::addInline('setInterval(function(){$.ajax({url: "' . JURI::current() . '", cache: false});}, ' . ($lifetime * 60 * 1000) . ');');
    }
}

N2Pluggable::addAction('exit', 'N2JoomlaExit');