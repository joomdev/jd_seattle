<?php

class N2Platform {

    public static $isAdmin = false;

    public static $hasPosts = true, $isJoomla = false, $isWordpress = false, $isMagento = false, $isNative = false;

    public static $name;

    public static function init() {
        self::$isJoomla = JVERSION;
        if (JFactory::getApplication()
                    ->isAdmin()) {
            self::$isAdmin = true;
        }
    }

    public static function getPlatform() {
        return 'joomla';
    }

    public static function getPlatformName() {
        return 'Joomla';
    }

    public static function getPlatformVersion() {

        return JVERSION;
    }

    public static function getCharset() {
        return JDocument::getInstance()->getCharset();
    }

    public static function getSiteUrl() {

        return JURI::root();
    }

    public static function getDate() {
        $config = JFactory::getConfig();

        return JFactory::getDate('now', $config->get('offset'))
                       ->toSql(true);
    }

    public static function getTime() {
        return strtotime(N2Platform::getDate());
    }

    public static function getPublicDir() {
        if (defined('JPATH_MEDIA')) {
            return rtrim(JPATH_SITE, '\\/') . JPATH_MEDIA;
        }

        return rtrim(JPATH_SITE, '\\/') . '/media';
    }

    public static function getDebug() {
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

    public static function getUserEmail() {
        return JFactory::getUser()->email;
    }

    public static function adminHideCSS() {
        echo '
            /*
            Joomla 3
            */

            .navbar{
                display: none;
            }

            .container-fluid{
                padding: 0;
            }

            .admin #content{
                margin: 0;
            }

            /**
            Joomla 2.5
            */
            body,
            #element-box,
            div#element-box div.m{
              margin: 0;
              padding: 0;
            }
            #border-top,
            #header-box{
                display: none;
            }

            #content-box{
              border: 0;
              width: 100%;
            }

            #element-box div.m{
                border: 0;
                background: transparent;
            }
        ';
    }

    public static function updateFromZip($fileRaw, $updateInfo) {
        N2Loader::import('libraries.zip.reader');

        $tmpHandle = tmpfile();
        fwrite($tmpHandle, $fileRaw);
        $metaData     = stream_get_meta_data($tmpHandle);
        $tmpFilename  = $metaData['uri'];
        $files        = N2ZipReader::read($tmpFilename);
        $updateFolder = N2Filesystem::getNotWebCachePath() . '/update/';
        self::recursive_extract($files, $updateFolder);
        fclose($tmpHandle);

        $installer = JInstaller::getInstance();
        $installer->setOverwrite(true);
        if (!$installer->install($updateFolder)) {
            N2Filesystem::deleteFolder($updateFolder);

            return false;
        }
        N2Filesystem::deleteFolder($updateFolder);

        return true;
    }

    private static function recursive_extract($files, $targetFolder) {
        foreach ($files AS $fileName => $file) {
            if (empty($fileName) || $fileName == '.' || $fileName == '..') continue;
            if (is_array($file)) {
                if (N2Filesystem::createFolder($targetFolder . $fileName . '/')) {
                    self::recursive_extract($file, $targetFolder . $fileName . '/');
                } else {
                    return false;
                }
            } else {
                if (!N2Filesystem::createFile($targetFolder . $fileName, $file)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function needStrongerCSS() {

        return false;
    }

}

N2Platform::init();
