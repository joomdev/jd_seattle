<?php

N2Loader::import('libraries.form.elements.list');

class N2ElementTmpList extends N2ElementList {

    public function __construct($parent, $name = '', $label = '', $default = '', $extension = '', $parameters = array()) {
        parent::__construct($parent, $name, $label, $default, $parameters);

        $dir             = N2Platform::getPublicDir();
        $files           = scandir($dir);
        $validated_files = array();

        foreach ($files as $file) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == $extension) {
                $validated_files[] = $file;
            }
        }

        $this->options[''] = n2_('Choose a file to import');

        foreach ($validated_files AS $f) {
            $this->options[$f] = $f;
        }
    }
}
