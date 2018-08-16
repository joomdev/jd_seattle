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
$resultUsers = acymailing_loadResult('SELECT count(id) FROM '.acymailing_table('jnews_subscribers', false));
$resultLists = acymailing_loadResult('SELECT count(id) FROM '.acymailing_table('jnews_lists', false));
$resultNews = acymailing_loadResult('SELECT count(id) FROM '.acymailing_table('jnews_mailings', false));

echo acymailing_translation_sprintf('USERS_IN_COMP', $resultUsers, 'jNews');
if(!empty($resultLists)){
	echo '<div class="acyblockoptions"><span class="acyblocktitle">'.acymailing_translation_sprintf('LISTS_IN_COMP', $resultLists, 'jNews').'</span>';
	echo acymailing_translation_sprintf('IMPORT_X_LISTS', $resultLists).'<br />';
	echo acymailing_translation_sprintf('IMPORT_LIST_TOO', 'jNews').acymailing_boolean("jnews_lists");
	echo '</div>';
}
if(!empty($resultNews)){
	echo '<div class="acyblockoptions"><span class="acyblocktitle">'.acymailing_translation_sprintf('LISTS_IN_COMP', $resultLists, 'jNews').'</span>';
	echo acymailing_translation_sprintf('IMPORT_NEWSLETTERS_TOO', 'jNews').acymailing_boolean("jnews_news");
}
