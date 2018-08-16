<?php
N2Loader::import('libraries.form.elements.text');

class N2ElementDisabled extends N2ElementText {

    protected $attributes = array('disabled' => 'disabled');
}