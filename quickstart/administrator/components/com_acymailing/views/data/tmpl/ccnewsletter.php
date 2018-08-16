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
$resultUsers = acymailing_loadResult('SELECT count(*) FROM '.acymailing_table('ccnewsletter_subscribers', false));

$resultLists = array();
$resultNews = array();

if(in_array(acymailing_getPrefix().'ccnewsletter_groups', $this->tables)){
	$resultLists = acymailing_loadResult('SELECT count(id) FROM '.acymailing_table('ccnewsletter_groups', false));

	$resultNews = acymailing_loadResult('SELECT count(id) FROM '.acymailing_table('ccnewsletter_newsletters', false));
}

echo acymailing_translation_sprintf('USERS_IN_COMP', $resultUsers, 'ccNewsletter');

if(!empty($resultLists)){
	echo '<div class="acyblockoptions"><span class="acyblocktitle">'.acymailing_translation_sprintf('LISTS_IN_COMP', $resultLists, 'ccNewsletter').'</span>';
	echo acymailing_translation_sprintf('IMPORT_X_LISTS', $resultLists).'<br />';
	echo acymailing_translation_sprintf('IMPORT_LIST_TOO', 'ccNewsletter').acymailing_boolean("ccNewsletter_lists");
	echo '</div>';
}
if(!empty($resultNews)){
	echo '<div class="acyblockoptions"><span class="acyblocktitle">'.acymailing_translation_sprintf('LISTS_IN_COMP', $resultLists, 'ccNewsletter').'</span>';
	echo acymailing_translation_sprintf('IMPORT_NEWSLETTERS_TOO', 'ccNewsletter').acymailing_boolean("ccNewsletter_news");
}
