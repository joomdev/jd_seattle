<?php

class N2SmartsliderUpdateModel {

    private $storage, $version;

    public function __construct() {
        $this->storage = N2Base::getApplication('smartslider')->storage;
        $this->version = $this->storage->get('update', 'version');
    }

    public static function getInstance() {
        static $ins;
        if (!$ins) {
            $ins = new N2SmartsliderUpdateModel();
        }
        return $ins;
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->storage->set('update', 'version', $version);
        $this->storage->set('update', 'lastcheck', time());
        $this->version = $version;
    }

    public function hasUpdate() {
        $this->autoCheck();
        if (version_compare(N2SS3::$version, $this->version) == -1) {
            return true;
        }
        return false;
    }

    private function autoCheck() {
        if (intval(N2SmartSliderSettings::get('autoupdatecheck', 1))) {
            $time = $this->storage->get('update', 'lastcheck');
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
        $time = $this->storage->get('update', 'lastcheck');
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