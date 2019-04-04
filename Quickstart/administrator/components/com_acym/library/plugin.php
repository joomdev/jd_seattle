<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class acymPlugin
{
    var $cms = 'all';
    var $installed = true;
    var $pluginsPath = '';
    public function __construct()
    {
        $this->acympluginHelper = acym_get('helper.plugin');

        if('Joomla' == 'WordPress') $this->pluginsPath = substr(plugin_dir_path(__FILE__), 0, strpos(plugin_dir_path(__FILE__), plugin_basename(__DIR__)));
    }
}
