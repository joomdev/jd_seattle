<?php

namespace Nextend\Framework\Form\Joomla;

use JSession;
use Nextend\Framework\Form\Base\PlatformFormBase;

class PlatformForm extends PlatformFormBase {

    public function tokenize() {
        return '<input type="hidden" name="' . JSession::getFormToken() . '" value="1" />';
    }

    public function tokenizeUrl() {
        $a                           = array();
        $a[JSession::getFormToken()] = 1;

        return $a;
    }

    public function checkToken() {
        return JSession::checkToken() || JSession::checkToken('get');
    }
}