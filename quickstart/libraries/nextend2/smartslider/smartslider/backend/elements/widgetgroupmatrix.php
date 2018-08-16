<?php

class N2ElementWidgetGroupMatrix extends N2ElementPluginMatrix {


    protected function getPlugins() {

        $this->plugins = N2SmartSliderWidgets::getGroups();
        uasort($this->plugins, 'N2ElementPluginMatrix::sort');

        return $this->plugins;
    }
}