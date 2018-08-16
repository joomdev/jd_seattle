<?php

interface N2FormElementContainer {

    /**
     * @param N2Element $element
     */
    public function addElement($element);

    /**
     * @return N2Form
     */
    public function getForm();
}