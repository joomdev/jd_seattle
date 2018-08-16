<?php

class N2LESS {

    public static function addFile($pathToFile, $group, $context = array(), $importDir = null) {
        N2AssetsManager::$less->addFile(array(
            'file'      => $pathToFile,
            'context'   => $context,
            'importDir' => $importDir
        ), $group);
    }

    public static function build() {
        foreach (N2AssetsManager::$less->getFiles() AS $group => $file) {
            if (substr($file, 0, 2) == '//') {
                N2CSS::addUrl($file);
            } else if (!realpath($file)) {
                // For database cache the $file contains the content of the generated CSS file
                N2CSS::addCode($file, $group, true);
            } else {
                N2CSS::addFile($file, $group);
            }
        }
    }
}