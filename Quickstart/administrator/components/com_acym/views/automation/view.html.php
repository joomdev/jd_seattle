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

class AutomationViewAutomation extends acymView
{
    public function __construct()
    {
        parent::__construct();

        if (empty(acym_getVar('int', 'id')) && acym_getVar('string', 'layout') != 'info') {
            $this->steps = array(
                'filter' => 'ACYM_FILTERS',
                'action' => 'ACYM_ACTIONS',
                'summary' => 'ACYM_SUMMARY',
            );
        } else {
            $this->steps = array(
                'info' => 'ACYM_INFORMATION',
                'filter' => 'ACYM_FILTERS',
                'action' => 'ACYM_ACTIONS',
                'summary' => 'ACYM_SUMMARY',
            );
        }
    }
}
