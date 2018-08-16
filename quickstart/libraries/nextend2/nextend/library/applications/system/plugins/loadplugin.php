<?php
defined('N2LIBRARY') or die();

N2Loader::import('libraries.fonts.fonts');

$mdir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
foreach (N2Filesystem::folders($mdir) AS $mfolder) {
    $mfile = $mdir . $mfolder . DIRECTORY_SEPARATOR . 'loadplugin.php';
    if (N2Filesystem::fileexists($mfile)) {
        require_once($mfile);
    }
}
