<?php

class N2Base {

    private static $isReady = false;


    /** @var N2ApplicationInfo[] */
    private static $applicationInfo = array();

    /** @var N2Application[] */
    private static $applications = array();

    /**
     * @var N2ApplicationType
     */
    public static $currentApplicationType;

    private static function init() {
        if (!self::$isReady) {
            N2Loader::importAll('libraries.mvc.application');
            N2Loader::importAll('libraries.mvc');
            N2Loader::import('libraries.mvc.controllers.backend');
            N2Loader::importAll('libraries.mvc.controllers');
            N2Loader::importAll('libraries.cache');
            N2Loader::importAll('libraries.assets');
            N2Loader::importAll('libraries.google');
            N2Loader::importAll('libraries.assets.css');
            N2Loader::importAll('libraries.assets.js');
            N2Loader::importAll('libraries.assets.less');
            N2Loader::importAll('libraries.assets.google');
            N2Loader::importAll('libraries.assets.image');
            N2Loader::importAll('libraries.uri');
            N2Loader::import('libraries.acl.acl');
            N2Loader::import('libraries.message.message');

            N2Loader::import('libraries.image.helper');

            self::$isReady = true;

            foreach (self::$applicationInfo AS $applicationInfo) {
                $applicationInfo->onReady();
            }
        }
    }

    public static function registerApplication($infoPath) {
        /**
         * @var $info N2ApplicationInfo
         */
        $info = require_once($infoPath);
        if (is_object($info)) {
            if (self::$isReady) {
                $info->onReady();
            }
            self::$applicationInfo[$info->getName()] = $info;
        }
    }

    /**
     * @param $name
     *
     * @return bool|N2ApplicationInfo
     */
    public static function getApplicationInfo($name) {
        if (!isset(self::$applicationInfo[$name])) {
            return false;
        }

        return self::$applicationInfo[$name];
    }

    /**
     * @param $name
     *
     * @return N2Application
     * @throws Exception
     */
    public static function getApplication($name) {
        if (!isset(self::$applications[$name])) {
            self::createApplication($name);

            N2Pluggable::doAction('applicationLoaded', array($name));
        }

        return self::$applications[$name];
    }

    private static function createApplication($name) {
        if (isset(self::$applicationInfo[$name])) {
            self::init();
            /**
             * @var $nextendApp N2Application
             */
            self::$applications[$name] = self::$applicationInfo[$name]->getInstance();

        } else {
            throw new Exception("Application not available: {$name}");
        }
    }
}