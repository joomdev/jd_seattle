<?php

class N2Form extends N2FormAbstract
{

    public static function tokenize() {
        return '<input type="hidden" name="' . JSession::getFormToken() . '" value="1" />';
    }

    public static function tokenizeUrl() {
        $a                           = array();
        $a[JSession::getFormToken()] = 1;
        return $a;
    }

    public static function checkToken() {
        return JSession::checkToken() || JSession::checkToken('get');
    }
}
