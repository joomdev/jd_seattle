<?php

class N2Message {

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

    private static function loadSessionError() {
        if (self::$error === false) {
            if (N2Platform::$isAdmin) {
                self::$error = N2Session::get('error', array());
            } else {
                self::$error = array();
            }
        }
    }

    private static function loadSessionSuccess() {
        if (self::$success === false) {
            if (N2Platform::$isAdmin) {
                self::$success = N2Session::get('success', array());
            } else {
                self::$error = array();
            }
        }
    }

    private static function loadSessionNotice() {
        if (self::$notice === false) {
            if (N2Platform::$isAdmin) {
                self::$notice = N2Session::get('notice', array());
            } else {
                self::$error = array();
            }
        }
    }

    public static function error($message = '', $parameters = array()) {
        self::loadSessionError();
        self::$error[] = array(
            $message,
            $parameters
        );
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
        N2Localization::addJS(array(
            'Show only errors',
            'There are no messages to display.',
            'Got it!',
            'error',
            'success',
            'notice'
        ));

        self::loadSessionError();

        if (is_array(self::$error) && count(self::$error)) {
            foreach (self::$error AS $error) {
                N2JS::addInline("N2Classes.Notification.error(" . json_encode($error[0]) . ", " . json_encode($error[1]) . ");");
            }
            self::$error = array();
        }

        self::loadSessionSuccess();

        if (is_array(self::$success) && count(self::$success)) {
            foreach (self::$success AS $success) {

                N2JS::addInline("N2Classes.Notification.success(" . json_encode($success[0]) . ", " . json_encode($success[1]) . ");");
            }
            self::$success = array();
        }

        self::loadSessionNotice();

        if (is_array(self::$notice) && count(self::$notice)) {
            foreach (self::$notice AS $notice) {

                N2JS::addInline("N2Classes.Notification.notice(" . json_encode($notice[0]) . ", " . json_encode($notice[1]) . ");");
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
            foreach (self::$error AS $error) {
                $messages['error'][] = $error;
            }
            self::$error = array();
        }

        self::loadSessionSuccess();

        if (is_array(self::$success) && count(self::$success)) {
            $messages['success'] = array();
            foreach (self::$success AS $success) {
                $messages['success'][] = $success;
            }
            self::$success = array();
        }

        self::loadSessionNotice();

        if (is_array(self::$notice) && count(self::$notice)) {
            $messages['notice'] = array();
            foreach (self::$notice AS $notice) {
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
            N2Session::delete('error');
            N2Session::delete('success');
            N2Session::delete('notice');
        } else {
            N2Session::set('error', self::$error);
            N2Session::set('success', self::$success);
            N2Session::set('notice', self::$notice);
        }
    }
}

N2Pluggable::addAction('beforeSessionSave', 'N2Message::storeInSession');