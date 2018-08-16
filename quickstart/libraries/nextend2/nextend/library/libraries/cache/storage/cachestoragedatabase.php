<?php
N2Loader::import('libraries.cache.storage.cachestorage');

class N2CacheStorageDatabase extends N2CacheStorage {

    protected $db;

    public function __construct() {

        parent::__construct();

        $this->db = new  N2StorageSection('cache');
    }

    public function clearAll($scope = 'notweb') {

    }

    public function clear($group, $scope = 'notweb') {

        $this->db->delete($scope . '/' . $group);
    }

    public function exists($group, $key, $scope = 'notweb') {

        if ($this->db->get($scope . '/' . $group, $key)) {
            return true;
        }

        return false;
    }

    public function set($group, $key, $value, $scope = 'notweb') {

        $this->db->set($scope . '/' . $group, $key, $value);
    }

    public function get($group, $key, $scope = 'notweb') {
        return $this->db->get($scope . '/' . $group, $key);
    }

    public function remove($group, $key, $scope = 'notweb') {
        $this->db->delete($scope . '/' . $group, $key);
    }

    public function getPath($group, $key, $scope = 'notweb') {

        return N2Platform::getSiteUrl() . '?nextendcache=1&g=' . urlencode($group) . '&k=' . urlencode($key);
    }
}