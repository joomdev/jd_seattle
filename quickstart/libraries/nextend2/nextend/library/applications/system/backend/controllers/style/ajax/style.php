<?php

class N2SystemBackendStyleControllerAjax extends N2SystemBackendVisualManagerControllerAjax
{

    protected $type = 'style';

    public function getModel() {
        return new N2SystemStyleModel();
    }
}