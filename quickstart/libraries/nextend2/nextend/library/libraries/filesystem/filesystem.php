<?php

define('N2_DS_INV', DIRECTORY_SEPARATOR == '/' ? '\\' : '/');

if (!defined('NEXTEND_RELATIVE_CACHE_WEB')) {
    define('NEXTEND_RELATIVE_CACHE_WEB', '/cache/nextend/web');
    define('NEXTEND_CUSTOM_CACHE', 0);
} else {
    define('NEXTEND_CUSTOM_CACHE', 1);
}
if (!defined('NEXTEND_RELATIVE_CACHE_NOTWEB')) {
    define('NEXTEND_RELATIVE_CACHE_NOTWEB', '/cache/nextend/notweb');
}

/**
 * Class N2FilesystemAbstract
 */
abstract class N2FilesystemAbstract {

    /**
     * @var string Absolute path which match to the baseuri. It must not end with /
     * @example /asd/xyz/wordpress
     */
    public $_basepath;

    public $_librarypath;

    public static $dirPermission = 0777;

    public static $filePermission = 0666;

    public static function getInstance() {
        static $instance;
        if (!is_object($instance)) {
            $instance = new N2Filesystem();
        }

        return $instance;
    }

    public static function check($base, $folder) {
        static $checked = array();
        if (!isset($checked[$base . '/' . $folder])) {
            $cacheFolder = $base . '/' . $folder;
            if (!self::existsFolder($cacheFolder)) {
                if (self::is_writable($base)) {
                    self::createFolder($cacheFolder);
                } else {
                    die('<div style="position:fixed;background:#fff;width:100%;height:100%;top:0;left:0;z-index:100000;">' . sprintf('<h2><b>%s</b> is not writable.</h2>', $base) . '<br><br><iframe style="width:100%;max-width:760px;height:100%;" src="https://smartslider3.helpscoutdocs.com/article/482-cache-folder-is-not-writable"></iframe></div>');
                }
            } else if (!self::is_writable($cacheFolder)) {
                die('<div style="position:fixed;background:#fff;width:100%;height:100%;top:0;left:0;z-index:100000;">' . sprintf('<h2><b>%s</b> is not writable.</h2>', $cacheFolder) . '<br><br><iframe style="width:100%;max-width:760px;height:100%;" src="https://smartslider3.helpscoutdocs.com/article/482-cache-folder-is-not-writable"></iframe></div>');
            }
            $checked[$base . '/' . $folder] = true;
        }
    }

    public static function measurePermission($testDir) {
        while ('.' != $testDir && !is_dir($testDir)) {
            $testDir = dirname($testDir);
        }

        if ($stat = @stat($testDir)) {
            self::$dirPermission  = $stat['mode'] & 0007777;
            self::$filePermission = self::$dirPermission & 0000666;
        }
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public static function toLinux($path) {
        return str_replace(DIRECTORY_SEPARATOR, '/', $path);
    }

    /**
     * @return string
     */
    public static function getBasePath() {
        $i = N2Filesystem::getInstance();

        return $i->_basepath;
    }

    public static function getWebCachePath() {

        return self::getBasePath() . NEXTEND_RELATIVE_CACHE_WEB;
    }

    public static function getNotWebCachePath() {
        return self::getBasePath() . NEXTEND_RELATIVE_CACHE_NOTWEB;
    }

    /**
     * @param $path
     */
    public static function setBasePath($path) {
        $i            = N2Filesystem::getInstance();
        $i->_basepath = $path;
    }

    /**
     * @return mixed
     */
    public static function getLibraryPath() {
        $i = N2Filesystem::getInstance();

        return $i->_librarypath;
    }

    /**
     * @param $path
     */
    public static function setLibraryPath($path) {
        $i               = N2Filesystem::getInstance();
        $i->_librarypath = $path;
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public static function pathToAbsoluteURL($path) {
        return N2Uri::pathToUri($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public static function pathToRelativePath($path) {
        $i = N2Filesystem::getInstance();

        return preg_replace('/^' . preg_quote($i->_basepath, '/') . '/', '', str_replace('/', DIRECTORY_SEPARATOR, $path));
    }

    /**
     * @param $path
     *
     * @return string
     */
    public static function pathToAbsolutePath($path) {
        $i = N2Filesystem::getInstance();

        return $i->_basepath . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public static function absoluteURLToPath($url) {
        $fullUri = N2Uri::getFullUri();
        if (substr($url, 0, strlen($fullUri)) == $fullUri) {
            $i = N2Filesystem::getInstance();

            return str_replace($fullUri, $i->_basepath, $url);
        }

        return $url;
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public static function fileexists($file) {
        return is_file($file);
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public static function safefileexists($file) {
        return realpath($file) && is_file($file);
    }

    /**
     * @param $dir
     *
     * @return array|bool
     */
    public static function folders($dir) {
        if (!is_dir($dir)) return false;
        $folders = array();
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') continue;
            if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) $folders[] = $file;
        }

        return $folders;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function is_writable($path) {
        return is_writable($path);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function createFolder($path) {
        return mkdir($path, self::$dirPermission, true);
    }

    /**
     * @param $dir
     *
     * @return bool
     */
    public static function deleteFolder($dir) {
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') continue;
            if (!self::deleteFolder($dir . DIRECTORY_SEPARATOR . $file)) {
                chmod($dir . DIRECTORY_SEPARATOR . $file, self::$dirPermission);
                if (!self::deleteFolder($dir . DIRECTORY_SEPARATOR . $file)) return false;
            };
        }

        return rmdir($dir);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function existsFolder($path) {
        return is_dir($path);
    }

    /**
     * @param $path
     *
     * @return array
     */
    public static function files($path) {
        $files = array();
        if (is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file[0] != ".") {
                        $files[] = $file;
                    }
                }
                closedir($dh);
            }
        }

        return $files;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function existsFile($path) {
        return file_exists($path);
    }

    /**
     * @param $path
     * @param $buffer
     *
     * @return int
     */
    public static function createFile($path, $buffer) {
        return file_put_contents($path, $buffer);
    }

    /**
     * @param $path
     *
     * @return string
     */
    public static function readFile($path) {
        return file_get_contents($path);
    }

    /**
     * convert dir alias to normal format
     *
     * @param $pathName
     *
     * @return mixed
     */
    public static function dirFormat($pathName) {
        return str_replace(".", NDS, $pathName);
    }

    public static function getImagesFolder() {
        return '';
    }

    public static function realpath($path) {
        return rtrim(realpath($path), '/\\');
    }

    private static $translate = array();

    public static function registerTranslate($from, $to) {
        self::$translate[$from] = $to;
    }

    public static function translate($path) {
        $path = self::fixPathSeparator($path);
        foreach (self::$translate AS $k => $v) {
            if (strpos($path, $k) === 0) {
                return str_replace($k, $v, $path);
            }
        }

        return $path;
    }

    private static function trailingslashit($string) {
        return self::untrailingslashit($string) . '/';
    }

    private static function untrailingslashit($string) {
        return rtrim($string, '/\\');
    }

    public static function fixPathSeparator($path) {
        return str_replace(N2_DS_INV, DIRECTORY_SEPARATOR, $path);
    }

    public static function get_temp_dir() {
        static $temp = '';
        if (defined('SS_TEMP_DIR')) return self::trailingslashit(SS_TEMP_DIR);

        if ($temp) return self::trailingslashit($temp);

        if (function_exists('sys_get_temp_dir')) {
            $temp = sys_get_temp_dir();
            if (@is_dir($temp) && self::is_writable($temp)) return self::trailingslashit($temp);
        }

        $temp = ini_get('upload_tmp_dir');
        if (@is_dir($temp) && self::is_writable($temp)) return self::trailingslashit($temp);

        $temp = N2Filesystem::getNotWebCachePath() . '/';
        if (is_dir($temp) && self::is_writable($temp)) return $temp;

        return '/tmp/';
    }

    public static function tempnam($filename = '', $dir = '') {
        if (empty($dir)) {
            $dir = N2Filesystem::get_temp_dir();
        }

        if (empty($filename) || '.' == $filename || '/' == $filename || '\\' == $filename) {
            $filename = time();
        }

        // Use the basename of the given file without the extension as the name for the temporary directory
        $temp_filename = basename($filename);
        $temp_filename = preg_replace('|\.[^.]*$|', '', $temp_filename);

        // If the folder is falsey, use its parent directory name instead.
        if (!$temp_filename) {
            return self::tempnam(dirname($filename), $dir);
        }

        // Suffix some random data to avoid filename conflicts
        $temp_filename .= '-' . md5(uniqid(rand() . time()));
        $temp_filename .= '.tmp';
        $temp_filename = $dir . $temp_filename;

        $fp = @fopen($temp_filename, 'x');
        if (!$fp && is_writable($dir) && file_exists($temp_filename)) {
            return self::tempnam($filename, $dir);
        }
        if ($fp) {
            fclose($fp);
        }

        return $temp_filename;
    }
}

N2Loader::import('libraries.filesystem.filesystem', 'platform');
