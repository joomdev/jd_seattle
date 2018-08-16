<?php

class N2Loader {

    public static $paths = array(
        'core'            => N2LIBRARY,
        'platform'        => N2_PLATFORM_LIBRARY,
        'system.platform' => N2_PLATFORM_LIBRARY
    );

    public static function addPath($app, $path) {
        self::$paths[$app] = $path;
    }

    public static function getPath($pathIdentifier, $app = 'core') {
        return self::$paths[$app] . NDS . self::dotToSlash($pathIdentifier);
    }

    public static function import($pathIdentifiers, $app = 'core') {
        $filePath        = self::$paths[$app] . NDS;
        $pathIdentifiers = (array)$pathIdentifiers;
        foreach ($pathIdentifiers AS $pathIdentifier) {
            self::importPath($filePath . self::dotToSlash($pathIdentifier));
        }
    }

    public static function importAll($pathIdentifier, $app = 'core') {
        $dirName    = self::$paths[$app] . NDS . self::dotToSlash($pathIdentifier);
        $dirContent = scandir($dirName);

        if ($dirContent) {
            foreach ($dirContent as $file) {
                if (is_file($dirName . NDS . $file) && substr($file, -4) == '.php') {
                    self::importPath($dirName . NDS . $file, true);
                }
            }
        }
    }

    public static function importPathAll($path) {
        $dirContent = scandir($path);

        if ($dirContent) {
            foreach ($dirContent as $file) {
                if (is_file($path . NDS . $file) && substr($file, -4) == '.php') {
                    self::importPath($path . NDS . $file, true);
                }
            }
        }
    }

    public static function importPath($file, $hasExtension = false) {
        if (!$hasExtension) {
            $file .= '.php';
        }
        if (file_exists($file)) {
            require_once($file);

            return true;
        }

        return false;
    }

    public static function toPath($pathIdentifier, $app = 'core') {
        return self::$paths[$app] . NDS . self::dotToSlash($pathIdentifier);
    }

    private static function dotToSlash($pathIdentifier) {
        return str_replace(".", NDS, $pathIdentifier);
    }

}
