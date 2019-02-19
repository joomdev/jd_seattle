<?php

class N2SmartsliderUpdateModel {

    private static $version = false;
    private static $lastCheck = false;

    private $storage;

    public function __construct() {
        $this->storage = N2Base::getApplication('smartslider')->storage;
    }

    public static function getInstance() {
        static $ins;
        if (!$ins) {
            $ins = new N2SmartsliderUpdateModel();
        }

        return $ins;
    }

    public function getVersion() {
        if (self::$version === false) {
            self::$version = $this->storage->get('update', 'version');
        }

        return self::$version;
    }

    public function setVersion($version) {
        $this->storage->set('update', 'version', $version);
        self::$version = $version;

        $this->setLastCheck(time());
    }

    public function getLastCheck() {
        if (self::$lastCheck === false) {
            self::$lastCheck = $this->storage->get('update', 'lastcheck');
        }

        return self::$lastCheck;
    }

    public function setLastCheck($lastCheck) {
        self::$lastCheck = $lastCheck;
        $this->storage->set('update', 'lastcheck', $lastCheck);
    }

    public function hasUpdate() {
        $this->autoCheck();
        if (version_compare(N2SS3::$version, $this->getVersion()) == -1) {
            return true;
        }

        return false;
    }

    private function autoCheck() {
        if (intval(N2SmartSliderSettings::get('autoupdatecheck', 1))) {
            $time = $this->getLastCheck();
            if (!$time || strtotime("+1 week", $time) < time()) {
                $this->check();
            }
        }
    }

    public function check() {

        $posts    = array(
            'action' => 'version'
        );
        $response = N2SS3::api($posts);
        if ($response['status'] == 'OK') {
            $this->setVersion($response['data']['latestVersion']);
        }

        return $response['status'];
    }

    public function lastCheck() {
        $time = $this->getLastCheck();
        if (empty($time)) {
            return n2_('never');
        }

        return date("Y-m-d H:i", $time);
    }

    public function update() {

        $posts = array(
            'action' => 'update'
        );

        $response = N2SS3::api($posts);
        if (is_string($response)) {
            $updateStatus = N2Platform::updateFromZip($response, N2SS3::getUpdateInfo());
            if ($updateStatus === true) {
                return 'OK';
            } else if ($updateStatus != false) {
                return $updateStatus;
            }

            return 'UPDATE_ERROR';
        }

        return $response['status'];
    }
}