<?php

abstract class N2FontServiceAbstract {

    protected $name;

    public abstract function getLabel();

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function onFontManagerLoad($force = false) {

    }

    public function onFontManagerLoadBackend() {
    }
}