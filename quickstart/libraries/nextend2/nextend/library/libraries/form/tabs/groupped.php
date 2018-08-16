<?php
N2Loader::import('libraries.form.tab');
N2Loader::import('libraries.form.tabs.tabbed');

class N2TabGroupped extends N2TabTabbed {

    public function render($control_name) {
        foreach ($this->tabs AS $tabname => $tab) {
            $tab->render($control_name);
        }
    }
}