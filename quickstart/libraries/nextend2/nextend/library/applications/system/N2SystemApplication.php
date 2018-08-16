<?php

class N2SystemApplication extends N2Application {

    public $name = "system";

    protected function autoload() {
        N2Loader::import('libraries.form.form');
    }

}
