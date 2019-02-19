<?php

N2Loader::import('libraries.form.elements.list');

class N2ElementLinkRel extends N2ElementList {

    protected $options = array(
        ''           => '',
        'nofollow'   => 'nofollow',
        'noreferrer' => 'noreferrer',
        'author'     => 'author',
        'external'   => 'external',
        'help'       => 'help'
    );
}
