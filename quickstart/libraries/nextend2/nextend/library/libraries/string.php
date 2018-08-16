<?php

class N2StringAbstract {

    public static function strpos($haystack, $needle, $offset = 0) {
        return strpos($haystack, $needle, $offset);
    }

    public static function substr($string, $start, $length = null) {
        return substr($string, $start, $length);
    }

    public static function strlen($string) {
        return strlen($string);
    }
}

if (function_exists('mb_strpos')) {
    class N2String extends N2StringAbstract {

        public static function strpos($haystack, $needle, $offset = 0) {
            return mb_strpos($haystack, $needle, $offset);
        }

        public static function substr($string, $start, $length = null) {
            return mb_substr($string, $start, $length);
        }

        public static function strlen($string) {
            return mb_strlen($string);
        }
    }
} else {
    class N2String extends N2StringAbstract {

    }
}