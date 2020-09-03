<?php

namespace Nextend\Framework\Database\Joomla;

use JDatabaseDriver;
use JFactory;
use Nextend\Framework\Database\AbstractPlatformConnector;

class JoomlaConnector extends AbstractPlatformConnector {

    /**
     * @var JDatabaseDriver
     */
    private $db;


    public function __construct() {
        $this->db      = JFactory::getDbo();
        $this->_prefix = $this->db->getPrefix();

        JoomlaConnectorTable::init($this, $this->db);
    }

    public function insertId() {
        return $this->db->insertid();
    }

    public function query($query, $attributes = false) {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $replaceTo = is_numeric($value) ? $value : $this->db->quote($value);
                $query     = str_replace($key, $replaceTo, $query);
            }
        }
        $this->db->setQuery($query);

        return $this->db->execute();
    }


    public function queryRow($query, $attributes = false) {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $replaceTo = is_numeric($value) ? $value : $this->db->quote($value);
                $query     = str_replace($key, $replaceTo, $query);
            }
        }
        $nextend = $this->db->setQuery($query);

        return $nextend->loadAssoc();
    }

    public function queryAll($query, $attributes = false, $type = "assoc", $key = null) {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $replaceTo = is_numeric($value) ? $value : $this->db->quote($value);
                $query     = str_replace($key, $replaceTo, $query);
            }
        }

        $nextend = $this->db->setQuery($query);

        if ($type == "assoc") {
            return $nextend->loadAssocList($key);
        } else {
            return $nextend->loadObjectList($key);
        }

    }

    /**
     * @param string $text
     * @param bool   $escape
     *
     * @return string
     */
    public function quote($text, $escape = true) {
        return $this->db->quote($text, $escape);
    }

    /**
     * @param string $name
     * @param null   $as
     *
     * @return mixed
     */
    public function quoteName($name, $as = null) {
        return $this->db->quoteName($name, $as);
    }

    public function getCharsetCollate() {

        if ($this->db->hasUTF8mb4Support()) {

            return 'DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci';
        }

        return 'DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_unicode_ci';
    }
}