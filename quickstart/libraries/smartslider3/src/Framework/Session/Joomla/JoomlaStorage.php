<?php

namespace Nextend\Framework\Session\Joomla;

use JFactory;
use Nextend\Framework\Session\AbstractStorage;

class JoomlaStorage extends AbstractStorage {

    public function __construct() {

        parent::__construct(JFactory::getUser()->id);
    }

    /**
     * Load the whole session
     */
    protected function load() {
        $stored = JFactory::getSession()
                          ->get($this->hash);

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