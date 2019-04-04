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

class acymbounceClass extends acymClass
{
    var $table = "bounce";
    var $pkey = "id";

    public function getMatchingRules()
    {
        $query = 'SELECT * FROM #__acym_rule ORDER BY '.acym_secureDBColumn('ordering').' '.strtoupper('ASC');

        return acym_loadObjectList($query);
    }

    public function getOrderingNumber()
    {
        $query = 'SELECT COUNT(id) FROM #__acym_rule';

        return acym_loadResult($query);
    }

    public function cleanTable()
    {
        acym_query('TRUNCATE TABLE `#__acym_rule`');
    }

}
