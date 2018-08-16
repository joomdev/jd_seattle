<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php
$resultUsers = acymailing_loadResult('SELECT count(id) FROM '.acymailing_table('acajoom_subscribers', false));
$resultLists = acymailing_loadResult('SELECT count(id) FROM '.acymailing_table('acajoom_lists', false));

echo acymailing_translation_sprintf('USERS_IN_COMP', $resultUsers, 'Acajoom');

if(!empty($resultLists)){
	echo '<div class="acyblockoptions"><span class="acyblocktitle">'.acymailing_translation_sprintf('LISTS_IN_COMP', $resultLists, 'Acajoom').'</span>';
	echo acymailing_translation_sprintf('IMPORT_X_LISTS', $resultLists).'<br />';
	echo acymailing_translation_sprintf('IMPORT_LIST_TOO', 'Acajoom').acymailing_boolean("acajoom_lists");
	echo '</div>';
}
