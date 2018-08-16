<?php

class N2JoomlaImageFallBack {

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

    static public function siteURL() {
        $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];

        return $protocol . $domainName;
    }

    static public function fallback($root, $imageVars, $textVars = array()) {
        $return = '';
        if (is_array($imageVars)) {
            foreach ($imageVars as $image) {
                if (!empty($image)) {
                    $return = N2ImageHelper::dynamic($root . $image);
                    break;
                }
            }
            if ($return == '' && !empty($textVars)) {
                foreach ($textVars as $text) {
                    $imageInText = self::findImage($text);
                    if (!empty($imageInText)) {
                        $file = $root . $imageInText;
                        if (N2Filesystem::existsFile($file)) {
                            $return = N2ImageHelper::dynamic($root . $imageInText);
                        } else {
                            $slashes = array(
                                '/',
                                '\\'
                            );
                            if (in_array(substr(self::siteURL(), -1), $slashes) || in_array(substr($imageInText, 0, 1), $slashes)) {
                                $return = N2ImageHelper::dynamic(self::siteURL() . $imageInText);
                            } else if( strpos($imageInText, self::siteURL()) === 0 ) {
                                $return = $imageInText;
                            } else {
                                $return = N2ImageHelper::dynamic(self::siteURL() . '/' . $imageInText);
                            }
                        }
                        if ($return != '$/') {
                            break;
                        } else {
                            $return = '';
                        }
                    }
                }
            }
            if ($return != '') {
                if (strpos($return, '$/http:') !== false || strpos($return, '$/https:') !== false) {
                    $return = substr($return, 2);
                } else if (strpos($return, '$http:') !== false || strpos($return, '$https:') !== false || strpos($return, '$//') !== false) {
                    $return = substr($return, 1);
                }
            }
        }

        return $return;
    }
}