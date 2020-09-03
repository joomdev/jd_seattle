<?php

namespace Nextend\SmartSlider3\Platform\Joomla;

use JURI;
use Nextend\Framework\Filesystem\Filesystem;

class ImageFallback {

    static public function fallback($imageVars, $textVars = array(), $root = '') {
        $root   = self::fixRoot($root);
        $return = '';

        foreach ($imageVars as $image) {
            if (!empty($image)) {
                $return = self::getImage($image, $root);
                if (!empty($return)) {
                    break;
                }
            }
        }

        if ($return == '' && !empty($textVars)) {
            foreach ($textVars as $text) {
                $imageInText = self::findImage($text);

                if (!empty($imageInText)) {
                    $return = self::getImage($imageInText, $root);

                    if ($return != '$/') {
                        break;
                    } else {
                        $return = '';
                    }
                }
            }
        }

        return $return;
    }

    static public function fixRoot($root) {
        if (substr($root, 0, 5) != 'http:' && substr($root, 0, 6) != 'https:') {
            $root = self::siteURL();
        }

        return self::removeSlashes($root);
    }

    static public function getImage($image, $root) {
        $imageUrl = self::httpLink($image, $root);
        if (self::isExternal($imageUrl) || self::imageUrlExists($imageUrl)) {
            return $imageUrl;
        } else {
            return '';
        }
    }

    static public function findImage($s) {
        preg_match_all('/(<img.*?src=[\'"](.*?)[\'"][^>]*>)|(background(-image)??\s*?:.*?url\((["|\']?)?(.+?)(["|\']?)?\))/i', $s, $r);
        if (isset($r[2]) && !empty($r[2][0])) {
            $s = $r[2][0];
        } else if (isset($r[6]) && !empty($r[6][0])) {
            $s = trim($r[6][0], "'\" \t\n\r\0\x0B");
        } else {
            $s = '';
        }

        return $s;
    }

    static public function removeSlashes($text, $right = true) {
        if ($right) {
            return rtrim($text, '/\\');
        } else {
            return ltrim($text, '/\\');
        }
    }

    static public function siteURL() {
        return JURI::root(false);
    }

    static public function isExternal($url) {
        $url = str_replace(array(
            'http:',
            'https:',
            '//',
            '\\\\'
        ), '', $url);

        $domain = $_SERVER['HTTP_HOST'];

        return !(substr($url, 0, strlen($domain)) === $domain);
    }

    static public function httpLink($image, $root) {
        if (substr($image, 0, 5) != 'http:' && substr($image, 0, 6) != 'https:' && substr($image, 0, 2) != '//' && substr($image, 0, 2) != '\\\\') {
            return $root . '/' . self::removeSlashes($image, false);
        } else {
            return $image;
        }
    }

    static public function imageUrlExists($imageUrl) {
        if (substr($imageUrl, 0, 2) == '//' || substr($imageUrl, 0, 2) == '\\\\') {
            $imageUrl = (empty($_SERVER['HTTPS']) ? "http:" : "https:") . $imageUrl;
        }

        return Filesystem::existsFile(Filesystem::absoluteURLToPath($imageUrl));
    }
}