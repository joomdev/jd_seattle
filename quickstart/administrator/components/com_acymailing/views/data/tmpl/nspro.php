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
$resultUsers = acymailing_loadResult('SELECT count(id) FROM '.acymailing_table('nspro_subs', false));
$resultLists = acymailing_loadResult('SELECT count(id) FROM '.acymailing_table('nspro_lists', false));
?>

<table <?php echo $this->isAdmin ? 'class="acymailing_table"' : 'class="admintable table" cellspacing="1"' ?>>
	<tr>
		<td colspan="2">
			<?php echo acymailing_translation_sprintf('USERS_IN_COMP', $resultUsers, 'NS Pro'); ?>
			<br/>
			<?php echo acymailing_translation_sprintf('LISTS_IN_COMP', $resultLists, 'NS Pro'); ?>
			<br/>
			<?php echo acymailing_translation_sprintf('IMPORT_X_LISTS', $resultLists); ?>
		</td>
	</tr>
	<tr>
		<td class="acykey">
			<?php echo acymailing_translation_sprintf('IMPORT_LIST_TOO', 'NS Pro'); ?>
		</td>
		<td>
			<?php echo acymailing_boolean("nspro_lists"); ?>
		</td>
	</tr>
</table>
