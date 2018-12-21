<?php

abstract class N2SmartsliderConflictsModelAbstract extends N2Model {

    protected $conflicts = array();

    protected $debugConflicts = array();

    public $curlLog = false;


    public function __construct() {
        parent::__construct();

        $this->testPHPINIMaxInputVars();
        $this->testApiConnection();
        $this->testDatabaseTables();
    }

    private function testPHPINIMaxInputVars() {
        if (function_exists('ini_get')) {
            $max_input_vars = intval(ini_get('max_input_vars'));
            if ($max_input_vars < 1000) {
                $this->conflicts[] = $this->displayConflict('PHP', sprintf(n2_('Increase <b>%1$s</b> in php.ini to 1000 or more. Current value: %2$s'), 'max_input_vars', $max_input_vars), 'https://smartslider3.helpscoutdocs.com/article/55-wordpress-installation');
            }
        }
    }

    private function testApiConnection() {
        $log = N2Base::getApplication('smartslider')->storage->get('log', 'api');
        if (!empty($log)) {
            if (strpos($log, 'ACTION_MISSING') === false) {
                $this->conflicts[] = $this->displayConflict(n2_('Unable to connect to the API'), n2_('See <b>Debug Information</b> for more details!'));

                $this->curlLog = json_decode($log, true);
            }
        }
    }

    private function testDatabaseTables() {
        $tables = array(
            '#__nextend2_image_storage',
            '#__nextend2_section_storage',
            '#__nextend2_smartslider3_generators',
            '#__nextend2_smartslider3_sliders',
            '#__nextend2_smartslider3_sliders_xref',
            '#__nextend2_smartslider3_slides'
        );

        foreach ($tables AS $table) {
            $table = $this->db->parsePrefix($table);
            $result = $this->db->queryRow('SHOW TABLES LIKE :table', array(
                ":table" => $table
            ));

            if (empty($result)) {
                $this->conflicts[]      = n2_('MySQL table missing') . ': ' . $table;
                $this->debugConflicts[] = n2_('MySQL table missing') . ': ' . $table;
            }
        }
    }

    public function getConflicts() {
        return $this->conflicts;
    }

    protected function displayConflict($title, $description, $url = '') {
        $this->conflicts[]      = '<b>' . $title . '</b> - ' . $description . (!empty($url) ? ' <a href="' . $url . '" target="_blank">' . n2_('Learn more') . '</a>' : '');
        $this->debugConflicts[] = $title;
    }

    public function getDebugConflicts() {

        return $this->debugConflicts;
    }
}