<?php
N2Loader::import('libraries.form.elements.pluginmatrix');

class N2ElementFontServices extends N2ElementPluginMatrix {

    protected function getPlugins() {
        return N2Fonts::getFontServices();
    }

}