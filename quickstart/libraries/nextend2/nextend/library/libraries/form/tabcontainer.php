<?php

interface N2FormTabContainer {

    /**
     * @param string $tab
     *
     * @return N2Tab
     */
    public function getTab($tab);

    /**
     * @param N2Tab $tab
     */
    public function addTab($tab);

    /**
     * @return N2FormAbstract
     */
    public function getForm();
}