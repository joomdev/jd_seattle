<?php


namespace Nextend\Framework\Platform\Joomla;


use JComponentHelper;
use JDocument;
use JFactory;
use JURI;
use Nextend\Framework\Asset\Js\Js;
use Nextend\Framework\Platform\AbstractPlatform;
use Nextend\Framework\Plugin;

class PlatformJoomla extends AbstractPlatform {

    protected $hasPosts = true;

    public function __construct() {

        if (JFactory::getApplication()
                    ->isAdmin()) {

            $this->isAdmin = true;
        }

        // Load required UTF-8 config from Joomla
        jimport('joomla.utilities.string');
        class_exists('JString');

        if (!defined('JPATH_NEXTEND_IMAGES')) {
            define('JPATH_NEXTEND_IMAGES', '/' . trim(JComponentHelper::getParams('com_media')
                                                                      ->get('image_path', 'images'), "/"));
        }

        Plugin::addAction('exit', array(
            $this,
            'addKeepAlive'
        ));
    }

    public function getName() {

        return 'joomla';
    }

    public function getLabel() {

        return 'Joomla';
    }

    public function getVersion() {

        return JVERSION;
    }

    public function getSiteUrl() {

        return JURI::root();
    }

    public function getCharset() {

        return JDocument::getInstance()
                        ->getCharset();
    }

    public function getMysqlDate() {

        $config = JFactory::getConfig();

        return JFactory::getDate('now', $config->get('offset'))
                       ->toSql(true);
    }

    public function getTimestamp() {

        return strtotime($this->getMysqlDate());
    }

    public function getPublicDirectory() {

        if (defined('JPATH_MEDIA')) {
            return rtrim(JPATH_SITE, '\\/') . JPATH_MEDIA;
        }

        return rtrim(JPATH_SITE, '\\/') . '/media';
    }

    public function getUserEmail() {

        return JFactory::getUser()->email;
    }

    public function getDebug() {
        $debug = array();

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array(
            'template',
            'title'
        )))
              ->from($db->quoteName('#__template_styles'))
              ->where('client_id = 0 AND home = 1');

        $db->setQuery($query);
        $result = $db->loadObject();
        if (isset($result->template)) {
            $debug[] = '';
            $debug[] = 'Template: ' . $result->template . ' - ' . $result->title;
        }

        $query = $db->getQuery(true);
        $query->select($db->quoteName(array(
            'name',
            'manifest_cache'
        )))
              ->from($db->quoteName('#__extensions'));

        $db->setQuery($query);
        $result = $db->loadObjectList();

        $debug[] = '';
        $debug[] = 'Extensions:';
        foreach ($result as $extension) {
            $decode = json_decode($extension->manifest_cache);
            if (isset($extension->name) && isset($decode->version)) {
                $debug[] = $extension->name . ' : ' . $decode->version;
            } else if (isset($extension->name)) {
                $debug[] = $extension->name;
            }
        }

        return $debug;
    }

    public function filterAssetsPath($assetsPath) {
        /**
         * Fix the error when Joomla installed in the system root / and Joomla sets JPATH_* to //
         */
        $jpath_libraries = JPATH_LIBRARIES;
        if (strpos(JPATH_LIBRARIES, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR) === 0) {
            $jpath_libraries = substr(JPATH_LIBRARIES, 1);
        }
        if (strpos($assetsPath, $jpath_libraries) === 0) {

            $jpath_root = JPATH_ROOT;
            if (JPATH_ROOT === DIRECTORY_SEPARATOR) {
                $jpath_root = '';
            }

            return str_replace('/', DIRECTORY_SEPARATOR, $jpath_root . '/media/' . ltrim(substr($assetsPath, strlen($jpath_libraries)), '/\\'));
        }

        return $assetsPath;
    }

    public function addKeepAlive() {
        if ($this->isAdmin) {
            $lifetime = JFactory::getConfig()
                                ->get('lifetime');
            if (empty($lifetime)) {
                $lifetime = 60;
            }
            $lifetime = min(max(intval($lifetime) - 1, 9), 60 * 24);
            Js::addInline('setInterval(function(){$.ajax({url: "' . JURI::current() . '", cache: false});}, ' . ($lifetime * 60 * 1000) . ');');
        }
    }
}