<?php

abstract class N2SmartsliderConflictsModelAbstract {

    protected $conflicts = array();

    protected $debugConflicts = array();

    public function __construct() {
        $this->testPHPINIMaxInputVars();
    }

    private function testPHPINIMaxInputVars() {
        if (function_exists('ini_get')) {
            $max_input_vars = intval(ini_get('max_input_vars'));
            if ($max_input_vars < 1000) {
                $this->conflicts[] = $this->displayConflict('PHP', sprintf(n2_('Increase <b>%1$s</b> in php.ini to 1000 or more. Current value: %2$s'), 'max_input_vars', $max_input_vars), 'https://smartslider3.helpscoutdocs.com/article/55-wordpress-installation');
            }
        }
    }

    public function getConflicts() {
        return $this->conflicts;
    }

    protected function displayConflict($title, $description, $url) {
        $this->conflicts[]      = '<b>' . $title . '</b> - ' . $description . ' <a href="' . $url . '" target="_blank">' . n2_('Learn more') . '</a>';
        $this->debugConflicts[] = $title;
    }

    public function getDebugConflicts() {

        return $this->debugConflicts;
    }
}