<?php

class N2AnimationRenderer {

    public static $sets = array();

    public static $mode;
}

N2AnimationRenderer::$mode = array(
    'solo' => array(
        'id'    => 'solo',
        'label' => n2_('Solo')
    ),
    '0'    => array(
        'id'    => '0',
        'label' => n2_('Chain')
    )
);