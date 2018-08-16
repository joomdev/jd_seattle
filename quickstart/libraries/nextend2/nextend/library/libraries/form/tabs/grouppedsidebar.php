<?php
N2Loader::import('libraries.form.tab');
N2Loader::import('libraries.form.tabs.tabbedsidebar');

class N2TabGrouppedSidebar extends N2TabTabbedSidebar {

    protected $icon = false;

    protected $tip = false;

    public function render($control_name) {
        foreach ($this->tabs AS $tab) {
            $tab->render($control_name);
        }
    }

    /**
     * @param bool|string $icon
     */
    public function setIcon($icon) {
        $this->icon = $icon;
    }

    /**
     * @return bool|string
     */
    public function getIcon() {
        return $this->icon;
    }


    /**
     * @param bool|string $tip
     */
    public function setTip($tip) {
        $this->tip = $tip;
    }

    /**
     * @return bool|string
     */
    public function getTip() {
        return $this->tip;
    }


}