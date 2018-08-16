<?php
N2Loader::import('libraries.cache.storage.cachestorage');

class N2CacheStorageFilesystem extends N2CacheStorage {

    public function __construct() {
        $this->paths['web']    = N2Filesystem::getWebCachePath();
        $this->paths['notweb'] = N2Filesystem::getNotWebCachePath();
        $this->paths['image']  = N2Filesystem::getImagesFolder();
    }

    public function isFilesystem() {
        return true;
    }

    public function clearAll($scope = 'notweb') {
        if (N2Filesystem::existsFolder($this->paths[$scope])) {
            N2Filesystem::deleteFolder($this->paths[$scope]);
        }
    }

    public function clear($group, $scope = 'notweb') {

        if (N2Filesystem::existsFolder($this->paths[$scope] . NDS . $group)) {
            N2Filesystem::deleteFolder($this->paths[$scope] . NDS . $group);
        }
    }

    public function exists($group, $key, $scope = 'notweb') {
        if (N2Filesystem::existsFile($this->paths[$scope] . NDS . $group . NDS . $key)) {
            return true;
        }

        return false;
    }

    public function set($group, $key, $value, $scope = 'notweb') {
        $path = $this->paths[$scope] . NDS . $group . NDS . $key;
        $dir  = dirname($path);
        if (!N2Filesystem::existsFolder($dir)) {
            N2Filesystem::createFolder($dir);
        }
        N2Filesystem::createFile($path, $value);
    }

    public function get($group, $key, $scope = 'notweb') {
        return N2Filesystem::readFile($this->paths[$scope] . NDS . $group . NDS . $key);
    }

    public function remove($group, $key, $scope = 'notweb') {
        if ($this->exists($group, $key, $scope)) {
            @unlink($this->paths[$scope] . NDS . $group . NDS . $key);
        }
    }

    public function getPath($group, $key, $scope = 'notweb') {
        return $this->paths[$scope] . NDS . $group . NDS . $key;
    }
}