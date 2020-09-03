<?php

namespace Nextend\Framework\Filesystem\Joomla;

use JFile;
use JFolder;
use Nextend\Framework\Filesystem\AbstractPlatformFilesystem;

class JoomlaFilesystem extends AbstractPlatformFilesystem {

    public function init() {
        $this->_basepath = realpath(JPATH_SITE == '' ? DIRECTORY_SEPARATOR : JPATH_SITE . DIRECTORY_SEPARATOR);
        if ($this->_basepath == DIRECTORY_SEPARATOR) {
            $this->_basepath = '';
        }
        $this->_cachepath = realpath(JPATH_CACHE);

        $this->measurePermission($this->_basepath . '/media/');
    }

    public function getWebCachePath() {
        return $this->getBasePath() . '/media/nextend';
    }

    public function getNotWebCachePath() {
        return JPATH_CACHE . '/nextend';
    }

    public function getImagesFolder() {
        if (defined('JPATH_NEXTEND_IMAGES')) {
            return $this->_basepath . JPATH_NEXTEND_IMAGES;
        }

        return $this->_basepath . '/images';
    }

    /**
     * Calling JFile:exists() method
     *
     * @param $file
     *
     * @return bool
     */
    public function fileexists($file) {
        return JFile::exists($file);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function folders($path) {
        return JFolder::folders($path);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function is_writable($path) {
        return true;
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function createFolder($path) {
        return JFolder::create($path, $this->dirPermission);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function deleteFolder($path) {
        return JFolder::delete($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function existsFolder($path) {
        return JFolder::exists($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function files($path) {
        return JFolder::files($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function existsFile($path) {
        return JFile::exists($path);
    }

    /**
     * @param $path
     * @param $buffer
     *
     * @return mixed
     */
    public function createFile($path, $buffer) {
        return JFile::write($path, $buffer);
    }

}