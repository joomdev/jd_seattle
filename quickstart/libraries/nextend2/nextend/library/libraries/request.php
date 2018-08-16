<?php

class N2Request {

    public static $originalStorage, $storage, $_requestUri;

    public static function init() {
        self::$originalStorage = $_REQUEST;
        self::$storage         = array();
    }

    static function set($var, $val) {
        self::$storage[$var] = $val;
    }

    protected static function get($var, $default = false) {
        if (isset(self::$storage[$var])) {
            return self::$storage[$var];
        } else if (isset(self::$originalStorage[$var])) {
            self::$storage[$var] = is_array(self::$originalStorage[$var]) ? self::stripslashesRecursive(self::$originalStorage[$var]) : stripslashes(self::$originalStorage[$var]);

            return self::$storage[$var];
        }

        return $default;
    }

    static function getVar($var, $default = null) {
        return self::get($var, $default);
    }

    static function getInt($var, $default = 0) {
        return intval(self::get($var, $default));
    }

    static function getCmd($var, $default = '') {
        return preg_replace("/[^\w_]/", "", self::get($var, $default));
    }

    protected static function _isset($var) {
        if (isset(self::$storage[$var])) {
            return true;
        } else if (isset(self::$originalStorage[$var])) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function getIsAjaxRequest() {

        if (self::_isset("nextendajax")) {
            return true;
        }

        return false;
    }

    /**
     * @param array|string $url
     * @param integer      $statusCode
     * @param bool         $terminate
     */
    public static function redirect($url, $statusCode = 302, $terminate = true) {

        header('Location: ' . $url, true, $statusCode);
        if ($terminate) {
            n2_exit(true);
        }
    }

    public static function getUrlReferrer() {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }

    /**
     * @return mixed|string
     * @throws Exception
     */
    public static function getRequestUri() {
        if (self::$_requestUri === null) {
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
                self::$_requestUri = $_SERVER['HTTP_X_REWRITE_URL']; elseif (isset($_SERVER['REQUEST_URI'])) {
                self::$_requestUri = $_SERVER['REQUEST_URI'];
                if (!empty($_SERVER['HTTP_HOST'])) {
                    if (strpos(self::$_requestUri, $_SERVER['HTTP_HOST']) !== false) self::$_requestUri = preg_replace('/^\w+:\/\/[^\/]+/', '', self::$_requestUri);
                } else
                    self::$_requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', self::$_requestUri);
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) // IIS 5.0 CGI
            {
                self::$_requestUri = $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) self::$_requestUri .= '?' . $_SERVER['QUERY_STRING'];
            } else
                throw new Exception(__CLASS__ . ' is unable to determine the request URI.');
        }

        return self::$_requestUri;
    }

    public static function stripslashesRecursive($array) {
        foreach ($array as $key => $value) {
            $array[$key] = is_array($value) ? self::stripslashesRecursive($value) : stripslashes($value);
        }

        return $array;
    }
}

class N2Get {

    public static $originalStorage, $storage;

    public static function init() {
        self::$originalStorage = $_GET;
        self::$storage         = array();
    }

    static function set($var, $val) {
        self::$storage[$var] = $val;
    }

    protected static function get($var, $default = false) {
        if (isset(self::$storage[$var])) {
            return self::$storage[$var];
        } else if (isset(self::$originalStorage[$var])) {
            self::$storage[$var] = is_array(self::$originalStorage[$var]) ? N2Request::stripslashesRecursive(self::$originalStorage[$var]) : stripslashes(self::$originalStorage[$var]);

            return self::$storage[$var];
        }

        return $default;
    }

    static function getVar($var, $default = null) {
        return self::get($var, $default);
    }

    static function getInt($var, $default = 0) {
        return intval(self::get($var, $default));
    }

    static function getCmd($var, $default = '') {
        return preg_replace("/[^\w_]/", "", self::get($var, $default));
    }
}

class N2Post {

    public static $originalStorage, $storage;

    public static function init() {
        self::$originalStorage = $_POST;
        self::$storage         = array();
    }

    static function set($var, $val) {
        self::$storage[$var] = $val;
    }

    protected static function get($var, $default = false) {
        if (isset(self::$storage[$var])) {
            return self::$storage[$var];
        } else if (isset(self::$originalStorage[$var])) {
            self::$storage[$var] = is_array(self::$originalStorage[$var]) ? N2Request::stripslashesRecursive(self::$originalStorage[$var]) : stripslashes(self::$originalStorage[$var]);

            return self::$storage[$var];
        }

        return $default;
    }

    static function getVar($var, $default = null) {
        return self::get($var, $default);
    }

    static function getInt($var, $default = 0) {
        return intval(self::get($var, $default));
    }

    static function getCmd($var, $default = '') {
        return preg_replace("/[^\w_]/", "", self::get($var, $default));
    }
}

class N2Cookie {

    public static $originalStorage, $storage;

    public static function init() {
        self::$originalStorage = $_COOKIE;
        self::$storage         = array();
    }

    static function set($var, $val) {
        self::$storage[$var] = $val;
    }

    protected static function get($var, $default = false) {
        if (isset(self::$storage[$var])) {
            return self::$storage[$var];
        } else if (isset(self::$originalStorage[$var])) {
            self::$storage[$var] = is_array(self::$originalStorage[$var]) ? N2Request::stripslashesRecursive(self::$originalStorage[$var]) : stripslashes(self::$originalStorage[$var]);

            return self::$storage[$var];
        }

        return $default;
    }

    static function getVar($var, $default = null) {
        return self::get($var, $default);
    }

    static function getInt($var, $default = 0) {
        return intval(self::get($var, $default));
    }

    static function getCmd($var, $default = '') {
        return preg_replace("/[^\w_]/", "", self::get($var, $default));
    }
}

N2Request::init();
N2Get::init();
N2Post::init();
N2Cookie::init();