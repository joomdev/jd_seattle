<?php
N2Loader::import('libraries.form.tab');

class N2TabPlaceholder extends N2Tab {

    protected $id = '';

    protected function decorateTitle() {
        echo "<div id='" . $this->id . "' class='nextend-tab " . $this->class . "'>";
        if (isset($GLOBALS[$this->id])) {
            echo $GLOBALS[$this->id];
        }
    }

    protected function decorateGroupStart() {

    }

    protected function decorateGroupEnd() {
        echo "</div>";
    }

    /**
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }


}