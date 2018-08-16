<?php

function n2CharCodeAt($data, $char) {
    return ord(substr($data, $char, 1));
}

function n2CharAt($data, $char) {
    return substr($data, $char, 1);
}

if (function_exists('base64_decode')) {
    function n2_base64_decode($data) {
        return base64_decode($data);
    }
} else {
    function n2_base64_decode($input) {
        $keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        $chr1   = $chr2 = $chr3 = "";
        $enc1   = $enc2 = $enc3 = $enc4 = "";
        $i      = 0;
        $output = "";

        // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
        $filter = $input;
        $input  = preg_replace("[^A-Za-z0-9\+\/\=]", "", $input);
        if ($filter != $input) {
            return false;
        }

        do {
            $enc1   = strpos($keyStr, substr($input, $i++, 1));
            $enc2   = strpos($keyStr, substr($input, $i++, 1));
            $enc3   = strpos($keyStr, substr($input, $i++, 1));
            $enc4   = strpos($keyStr, substr($input, $i++, 1));
            $chr1   = ($enc1 << 2) | ($enc2 >> 4);
            $chr2   = (($enc2 & 15) << 4) | ($enc3 >> 2);
            $chr3   = (($enc3 & 3) << 6) | $enc4;
            $output = $output . chr((int)$chr1);
            if ($enc3 != 64) {
                $output = $output . chr((int)$chr2);
            }
            if ($enc4 != 64) {
                $output = $output . chr((int)$chr3);
            }
            $chr1 = $chr2 = $chr3 = "";
            $enc1 = $enc2 = $enc3 = $enc4 = "";
        } while ($i < strlen($input));

        return urldecode($output);
    }
}

if (function_exists('base64_encode')) {
    function n2_base64_encode($data) {
        return base64_encode($data);
    }
} else {
    function n2_base64_encode($data) {
        $b64     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
        $o1      = $o2 = $o3 = $h1 = $h2 = $h3 = $h4 = $bits = $i = 0;
        $ac      = 0;
        $enc     = '';
        $tmp_arr = array();
        if (!$data) {
            return data;
        }
        do {
            // pack three octets into four hexets
            $o1   = n2CharCodeAt($data, $i++);
            $o2   = n2CharCodeAt($data, $i++);
            $o3   = n2CharCodeAt($data, $i++);
            $bits = $o1 << 16 | $o2 << 8 | $o3;
            $h1   = $bits >> 18 & 0x3f;
            $h2   = $bits >> 12 & 0x3f;
            $h3   = $bits >> 6 & 0x3f;
            $h4   = $bits & 0x3f;
            // use hexets to index into b64, and append result to encoded string
            $tmp_arr[$ac++] = n2CharAt($b64, $h1) . n2CharAt($b64, $h2) . n2CharAt($b64, $h3) . n2CharAt($b64, $h4);
        } while ($i < strlen($data));
        $enc = implode($tmp_arr, '');
        $r   = (strlen($data) % 3);

        return ($r ? substr($enc, 0, ($r - 3)) . substr('===', $r) : $enc);
    }
}