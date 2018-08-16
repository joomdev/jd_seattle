<?php

class N2SessionStorage extends N2SessionStorageAbstract
{

    public function __construct() {
        $user = JFactory::getUser();
        parent::__construct($user->id);
    }

    /**
     * Load the whole session
     */
    protected function load() {
        $session = JFactory::getSession();
        $stored  = $session->get($this->hash);

        if (!is_array($stored)) {
            $stored = array();
        }
        $this->storage = $stored;
    }

    /**
     * Store the whole session
     */
    protected function store() {
        $session = JFactory::getSession();
        if (count($this->storage) > 0) {
            $session->set($this->hash, $this->storage);
        } else {
            $session->set($this->hash, null);
        }
    }

}