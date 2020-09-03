<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class plgSystemJceacym extends JPlugin
{
    public function onBeforeWfEditorRender(&$settings)
    {
        if (empty($_REQUEST['option']) || $_REQUEST['option'] != 'com_acym') {
            return;
        }

        if (!empty($_REQUEST['acycssfile'])) {
            $settings['content_css'] = $_REQUEST['acycssfile'];
        }
    }
}

