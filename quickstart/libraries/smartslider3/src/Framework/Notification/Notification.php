<?php

namespace Nextend\Framework\Notification;

use Nextend\Framework\Asset\Js\Js;
use Nextend\Framework\Platform\Platform;
use Nextend\Framework\Plugin;
use Nextend\Framework\Session\Session;

class Notification {

    /**
     * @var bool|array
     */
    private static $error = false;
    /**
     * @var bool|array
     */
    private static $success = false;
    /**
     * @var bool|array
     */
    private static $notice = false;

    private static $flushed = false;

    public function __construct() {

        Plugin::addAction('beforeSessionSave', array(
            '\\Nextend\\Framework\\Notification\\Notification',
            'storeInSession'
        ));
    }


    private static function loadSessionError() {
        if (self::$error === false) {
            if (Platform::isAdmin()) {
                self::$error = Session::get('error', array());
            } else {
                self::$error = array();
            }
        }
    }

    private static function loadSessionSuccess() {
        if (self::$success === false) {
            if (Platform::isAdmin()) {
                self::$success = Session::get('success', array());
            } else {
                self::$success = array();
            }
        }
    }

    private static function loadSessionNotice() {
        if (self::$notice === false) {
            if (Platform::isAdmin()) {
                self::$notice = Session::get('notice', array());
            } else {
                self::$notice = array();
            }
        }
    }

    public static function hasErrors() {

        self::loadSessionError();

        return !empty(self::$error);
    }

    public static function error($message = '', $parameters = array()) {
        self::loadSessionError();
        self::$error[] = array(
            $message,
            $parameters
        );
    }

    public static function displayPlainErrors() {

        if (Platform::isAdmin() && is_array(self::$error) && count(self::$error)) {
            foreach (self::$error as $error) {
                echo '<div style="border: 1px solid red; margin-bottom: 20px; padding: 10px 20px; max-width: 400px;">' . $error[0] . '</div>';
            }
            self::$error = array();
        }
    }

    public static function success($message = '', $parameters = array()) {
        self::loadSessionSuccess();
        self::$success[] = array(
            $message,
            $parameters
        );
    }

    public static function notice($message = '', $parameters = array()) {
        self::loadSessionNotice();
        self::$notice[] = array(
            $message,
            $parameters
        );
    }

    public static function show() {

        self::loadSessionError();

        if (is_array(self::$error) && count(self::$error)) {
            foreach (self::$error as $error) {
                Js::addInline("N2Classes.Notification.error(" . json_encode($error[0]) . ", " . json_encode($error[1]) . ");");
            }
            self::$error = array();
        }

        self::loadSessionSuccess();

        if (is_array(self::$success) && count(self::$success)) {
            foreach (self::$success as $success) {

                Js::addInline("N2Classes.Notification.success(" . json_encode($success[0]) . ", " . json_encode($success[1]) . ");");
            }
            self::$success = array();
        }

        self::loadSessionNotice();

        if (is_array(self::$notice) && count(self::$notice)) {
            foreach (self::$notice as $notice) {

                Js::addInline("N2Classes.Notification.notice(" . json_encode($notice[0]) . ", " . json_encode($notice[1]) . ");");
            }
            self::$notice = array();
        }

        self::$flushed = true;

    }

    public static function showAjax() {

        self::loadSessionError();
        $messages = array();

        if (is_array(self::$error) && count(self::$error)) {
            $messages['error'] = array();
            foreach (self::$error as $error) {
                $messages['error'][] = $error;
            }
            self::$error = array();
        }

        self::loadSessionSuccess();

        if (is_array(self::$success) && count(self::$success)) {
            $messages['success'] = array();
            foreach (self::$success as $success) {
                $messages['success'][] = $success;
            }
            self::$success = array();
        }

        self::loadSessionNotice();

        if (is_array(self::$notice) && count(self::$notice)) {
            $messages['notice'] = array();
            foreach (self::$notice as $notice) {
                $messages['notice'][] = $notice;
            }
            self::$notice = array();
        }

        self::$flushed = true;
        if (count($messages)) {
            return $messages;
        }

        return false;
    }

    public static function storeInSession() {
        if (self::$flushed) {
            Session::delete('error');
            Session::delete('success');
            Session::delete('notice');
        } else {
            Session::set('error', self::$error);
            Session::set('success', self::$success);
            Session::set('notice', self::$notice);
        }
    }
}

new Notification();