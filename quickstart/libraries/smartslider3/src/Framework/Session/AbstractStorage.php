<?php

namespace Nextend\Framework\Session;

use Nextend\Framework\Plugin;

abstract class AbstractStorage {

    protected static $expire = 86400; // 1 day

    protected static $salt = 'nextendSalt';

    protected $hash;

    protected $storage = array();

    public $storageChanged = false;

    public function __construct($userIdentifier) {

        $this->register();
        if (!isset($_COOKIE['nextendsession']) || substr($_COOKIE['nextendsession'], 0, 2) != 'n2' || !preg_match('/^[a-f0-9]{32}$/', substr($_COOKIE['nextendsession'], 2))) {
            $this->hash = 'n2' . md5(self::$salt . $userIdentifier);
            setcookie('nextendsession', $this->hash, time() + self::$expire, $_SERVER["HTTP_HOST"]);
            $_COOKIE['nextendsession'] = $this->hash;
        } else {
            $this->hash = $_COOKIE['nextendsession'];
        }

        $this->load();
    }

    /**
     * Load the whole session
     * $this->storage = json_decode(result for $this->hash);
     */
    protected abstract function load();

    /**
     * Store the whole session
     * $this->hash json_encode($this->storage);
     */
    protected abstract function store();

    public function get($key, $default = '') {
        return isset($this->storage[$key]) ? $this->storage[$key] : $default;
    }

    public function set($key, $value) {
        $this->storageChanged = true;

        $this->storage[$key] = $value;
    }

    public function delete($key) {
        $this->storageChanged = true;
        unset($this->storage[$key]);
    }

    /**
     * Register our method for PHP shut down
     */
    protected function register() {
        Plugin::addAction('exit', array(
            $this,
            'shutdown'
        ));
    }

    /**
     * When PHP shuts down, we have to save our session's data if the data changed
     */
    public function shutdown() {
        Plugin::doAction('beforeSessionSave');
        if ($this->storageChanged) {
            $this->store();
        }
    }
}