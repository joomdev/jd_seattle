<?php
defined('_JEXEC') or die('Restricted access');
?><?php
if (!acym_level(1)) {
    $data['version'] = 'essential';
    include acym_getView('configuration', 'upgrade_license', true);
}

