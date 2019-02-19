<?php

class N2Data {

    /** @var array */
    public $_data;

    public function __construct($data = null, $json = false) {
        $this->_data = array();

        if ($data) {
            if ($json) {
                $this->loadJSON($data);
            } else {
                $this->loadArray($data);
            }
        }
    }

    public static function json_encode($data) {

        if (version_compare(phpversion(), '5.4.0', '<')) {
            return json_encode($data);
        } else {
            return json_encode($data, JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * @param $json
     */
    public function loadJSON($json) {
        $array = json_decode($json, true);
        if (is_array($array)) $this->_data = array_merge($this->_data, $array);
    }

    /**
     * @param $array
     */
    public function loadArray($array) {
        if (!$this->_data) $this->_data = array();
        if (is_array($array)) $this->_data = array_merge($this->_data, $array);
    }

    /**
     * @return mixed|string
     */
    public function toJSON() {
        return json_encode($this->_data);
    }

    /**
     * @return array
     */
    public function toArray() {
        return (array)$this->_data;
    }

    public function has($key) {

        return isset($this->_data[$key]);
    }

    /**
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function get($key, $default = '') {
        if (isset($this->_data[$key])) return $this->_data[$key];

        return $default;
    }

    public function getIfEmpty($key, $default = '') {
        if (isset($this->_data[$key]) && !empty($this->_data[$key])) return $this->_data[$key];

        return $default;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value) {
        $this->_data[$key] = $value;
    }

    public function un_set($key) {
        if (isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
    }

    public function fillDefault($defaults) {
        $this->_data = array_merge($defaults, $this->_data);
    }
}
