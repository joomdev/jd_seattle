<?php

class N2SystemBackendFontControllerAjax extends N2SystemBackendVisualManagerControllerAjax
{
    protected $type = 'font';

    public function getModel() {
        return new N2SystemFontModel();
    }
}