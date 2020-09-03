<?php

namespace Nextend\Framework\Url\Joomla;

use JUri;
use Nextend\Framework\Url\AbstractPlatformUrl;

class JoomlaUrl extends AbstractPlatformUrl {

    private $fullUri;

    function __construct() {

        $this->siteUrl = JURI::root();

        $this->fullUri  = rtrim(JURI::root(), '/');
        $this->_baseuri = rtrim(JURI::root(true), '/');

        $this->_currentbase = $this->fullUri;

        $this->scheme = parse_url($this->fullUri, PHP_URL_SCHEME);
    }

    public function getFullUri() {

        return $this->fullUri;
    }

    public function ajaxUri($query = '') {
        return JUri::current();
    }
}