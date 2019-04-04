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

class ListsViewLists extends acymView
{
    public function __construct()
    {
        parent::__construct();

        $this->steps = array(
            'settings' => 'ACYM_LIST_SETTINGS',
            'subscribers' => 'ACYM_SUBSCRIBERS',
            'welcome' => 'ACYM_WELCOME_MAIL',
            'unsubscribe' => 'ACYM_UNSUBSCRIBE_MAIL',
        );
    }
}
