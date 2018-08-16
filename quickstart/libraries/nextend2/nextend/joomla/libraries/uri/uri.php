<?php

class N2Uri extends N2UriAbstract {

    private $fullUri;

    function __construct() {
        $this->fullUri  = rtrim(JURI::root(), '/');
        $this->_baseuri = rtrim(JURI::root(true), '/');

        $this->_currentbase = $this->fullUri;

        self::$scheme = parse_url($this->fullUri, PHP_URL_SCHEME);
    }

    static function getFullUri() {
        $i = N2Uri::getInstance();

        return $i->fullUri;
    }

    static function ajaxUri($query = '', $magento = 'nextendlibrary') {
        return JUri::current();
    }

}