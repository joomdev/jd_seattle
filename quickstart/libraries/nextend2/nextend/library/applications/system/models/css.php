<?php

class N2SystemCssModel extends N2Model {

    public $storage;

    public function __construct($tableName = null) {

        $this->storage = N2Base::getApplication('system')->storage;
        parent::__construct($tableName);
    }

    protected function getPath() {
        return dirname(__FILE__);
    }

    public function addVisual($type, $visual) {

        $visualId = $this->storage->add($type, '', $visual);

        $visual = $this->storage->getById($visualId, $type);
        if (!empty($visual) && $visual['section'] == $type) {
            return $visual;
        }
        return false;
    }

    public function deleteVisual($type, $id) {
        $visual = $this->storage->getById($id, $type);
        if (!empty($visual) && $visual['section'] == $type) {
            $this->storage->deleteById($id);
            return $visual;
        }
        return false;
    }

    public function changeVisual($type, $id, $value) {
        if ($this->storage->setById($id, $value)) {
            return $this->storage->getById($id, $type);
        }
        return false;
    }

    public function getVisuals($type) {
        return $this->storage->getAll($type);
    }

}