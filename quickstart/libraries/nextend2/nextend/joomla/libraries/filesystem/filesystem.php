<?php

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Class N2Filesystem
 */
class N2Filesystem extends N2FilesystemAbstract {

    public function __construct() {
        $this->_basepath = realpath(JPATH_SITE == '' ? NDS : JPATH_SITE . NDS);
        if ($this->_basepath == NDS) {
            $this->_basepath = '';
        }
        $this->_cachepath   = realpath(JPATH_CACHE);
        $this->_librarypath = str_replace($this->_basepath, '', N2LIBRARY);

        self::measurePermission($this->_basepath . '/media/');
    }

    public static function getWebCachePath() {
        return self::getBasePath() . '/media/nextend';
    }

    public static function getNotWebCachePath() {
        return JPATH_CACHE . '/nextend';
    }

    public static function getImagesFolder() {
        $i = N2Filesystem::getInstance();
        if (defined('JPATH_NEXTEND_IMAGES')) {
            return $i->_basepath . JPATH_NEXTEND_IMAGES;
        }

        return $i->_basepath . '/images';
    }

    /**
     * Calling JFile:exists() method
     *
     * @param $file
     *
     * @return bool
     */
    static function fileexists($file) {
        return JFile::exists($file);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    static function folders($path) {
        return JFolder::folders($path);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    static function is_writable($path) {
        return true;
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    static function createFolder($path) {
        return JFolder::create($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    static function deleteFolder($path) {
        return JFolder::delete($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    static function existsFolder($path) {
        return JFolder::exists($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    static function files($path) {
        return JFolder::files($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    static function existsFile($path) {
        return JFile::exists($path);
    }

    /**
     * @param $path
     * @param $buffer
     *
     * @return mixed
     */
    static function createFile($path, $buffer) {
        return JFile::write($path, $buffer);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    static function readFile($path) {
        return JFile::read($path);
    }


}