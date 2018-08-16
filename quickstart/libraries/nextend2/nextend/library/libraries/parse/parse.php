<?php

class N2Parse {

    function fromMixed($s) {

        return explode('|*|', $s);
    }

    static function fromArray($s) {

        return explode('||', $s);
    }

    /**
     * @param      $str
     * @param bool $concat
     *
     * @return array
     */
    static function parse($str, $concat = false) {

        $v = explode("|*|", $str);
        for ($i = 0; $i < count($v); $i++) {
            if (strpos($v[$i], "||") !== false) {
                if ($concat === false) $v[$i] = explode("||", $v[$i]); else $v[$i] = str_replace("||", $concat, $v[$i]);
            }
        }

        //if ($v[count($v) - 1] == '') unset($v[count($v) - 1]);
        return count($v) == 1 ? $v[0] : $v;
    }

    static function parseUnit($value, $concat = '') {

        if (!is_array($value)) $value = self::parse($value);
        $unit = $value[count($value) - 1];
        unset($value[count($value) - 1]);
        $r = '';
        foreach ($value AS $m) {
            $r .= $m . $unit . $concat;
        }

        return $r;
    }
}
