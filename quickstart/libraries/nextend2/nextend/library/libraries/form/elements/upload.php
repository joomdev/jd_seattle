<?php

N2Loader::import('libraries.form.elements.text');

class N2ElementUpload extends N2ElementText {

    protected $class = 'n2-form-element-file ';

    public $fieldType = 'file';
}