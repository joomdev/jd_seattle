<?php
N2Loader::import("libraries.mvc.db");

class N2Model {

    /**
     * @var N2DBConnectorAbstract
     */
    public $db;

    public function __construct($tableName = null) {

        if (is_null($tableName)) {
            $tableName = get_class();
        }
        $this->db = new N2DBConnector($tableName);

    }

    public function getTable() {
        return $this->db->tableName;
    }

}