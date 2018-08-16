<?php
defined('N2LIBRARY') or die();

require_once(dirname(__FILE__) . '/imagefallback.php');

$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
foreach (N2Filesystem::folders($dir) AS $folder) {
    $file = $dir . $folder . DIRECTORY_SEPARATOR . $folder . '.php';
    if (N2Filesystem::fileexists($file)) {
        require_once($file);
    }
}
