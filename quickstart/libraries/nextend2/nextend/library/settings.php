<?php

class N2Settings {

    private static $data;

    public static function init() {

        $config = array(
            'jquery'                   => 1,
            'gsap'                     => 1,
            'async'                    => 0,
            'combine-js'               => 0,
            'minify-js'                => 0,
            'scriptattributes'         => '',
            'javascript-inline'        => 'head',
            'api-secondary'            => 0,
            'protocol-relative'        => 1,
            'force-english-backend'    => 0,
            'show-joomla-admin-footer' => 0,
            'frontend-accessibility'   => 1,
            'curl'                     => 1,
            'curl-clean-proxy'         => 0,
            'css-mode'                 => 'normal',
            'icon-fa'                  => 1,
        );

        if (!defined('NEXTEND_INSTALL')) {
            foreach (N2StorageSectionAdmin::getAll('system', 'global') AS $data) {
                $config[$data['referencekey']] = $data['value'];
            }
        }

        self::$data = new N2Data();
        self::$data->loadArray($config);
    }

    public static function get($key, $default = '') {
        return self::$data->get($key, $default);
    }

    public static function getAll() {
        return self::$data->toArray();
    }

    public static function set($key, $value) {
        self::$data->set($key, $value);
        N2StorageSectionAdmin::set('system', 'global', $key, $value, 1, 1);
    }

    public static function setAll($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (self::$data->get($key, null) !== null) {
                    self::set($key, $value);
                }
            }

            return true;
        }

        return false;
    }

}

N2Settings::init();