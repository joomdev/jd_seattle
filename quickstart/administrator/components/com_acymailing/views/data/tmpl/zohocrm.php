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
$listClass = acymailing_get('class.list');
$this->data = $listClass->getLists('listid');
$this->values = array();
$this->values[] = acymailing_selectOption('0', '- - -');
foreach($this->data as $onelist){
	$this->values[] = acymailing_selectOption($onelist->listid, $onelist->name);
}
$zohoFields = $this->config->get('zoho_fields');
$value['zoho_fields'] = empty($zohoFields) ? array() : unserialize($zohoFields);
$zohoList = $this->config->get('zoho_list');
$value['zoho_list'] = empty($zohoList) ? 'Leads' : $zohoList;

if(empty($value['zoho_fields'])) $value['zoho_fields'] = array('First Name' => 'name');
?>
<span class="acyblocktitle"><?php echo acymailing_translation('Options'); ?></span>
<table <?php echo $this->isAdmin ? 'class="acymailing_table"' : 'class="admintable table" cellspacing="1"' ?>>
	<?php if($this->config->get('require_confirmation')){ ?>
		<tr id="trfileconfirm">
			<td class="acykey">
				<?php echo acymailing_translation('IMPORT_CONFIRMED'); ?>
			</td>
			<td>
				<?php
				echo acymailing_boolean("zoho_confirmed", '', $this->config->get('zoho_confirmed'), acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO'));
				?>
			</td>
		</tr>
	<?php } ?>
	<tr id="trfileoverwrite">
		<td class="acykey">
			<?php echo acymailing_translation('OVERWRITE_EXISTING'); ?>
		</td>
		<td>
			<?php
			echo acymailing_boolean("zoho_overwrite", '', $this->config->get('zoho_overwrite'), acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<tr id="trzohodelete">
		<td class="acykey">
			<?php echo acymailing_translation('DELETE_USERS'); ?>
		</td>
		<td>
			<?php
			echo acymailing_boolean("zoho_delete", '', $this->config->get('zoho_delete'), acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<tr id="trzohoimportnew">
		<td class="acykey">
			<?php echo acymailing_translation('ACY_ZOHO_IMPORT_NEW'); ?>
		</td>
		<td>
			<?php
			echo acymailing_boolean("zoho_importnew", '', $this->config->get('zoho_importnew'), acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO').' : '.acymailing_translation('ALL_USERS')); ?>
		</td>
	</tr>
	<tr id="trzohogeneratename">
		<td class="acykey">
			<?php echo acymailing_tooltip(acymailing_translation('ACY_ZOHO_GENERATE_NAME_DESC'), acymailing_translation('ACY_ZOHO_GENERATE_NAME'), '', acymailing_translation('ACY_ZOHO_GENERATE_NAME')); ?>
		</td>
		<td>
			<?php $generateFrom = array();
			$generateFrom[] = acymailing_selectOption('fromemail', acymailing_translation('ACY_ZOHO_GENERATE_NAME_FROM_EMAIL'));
			$generateFrom[] = acymailing_selectOption('fromconcat', acymailing_translation('ACY_ZOHO_GENERATE_NAME_FROM_FIELDS'));
			echo acymailing_radio($generateFrom, "zoho_generate_name", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('zoho_generate_name', 'fromemail')); ?>
		</td>
	</tr>
	<tr id="trzohoapikey">
		<td class="acykey">
			<?php echo 'Auth Token'; ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="zoho_apikey" size="35" value="<?php echo $this->escape($this->config->get('zoho_apikey')); ?>">
		</td>
	</tr>
	<tr id="trzoholist">
		<td class="acykey">
			<?php echo acymailing_translation('ACY_ZOHOLIST'); ?>
		</td>
		<td>
			<?php $lists = array();
			$lists[] = acymailing_selectOption('Leads', 'Leads');
			$lists[] = acymailing_selectOption('Contacts', 'Contacts');
			$lists[] = acymailing_selectOption('Vendors', 'Vendors');
			echo acymailing_select($lists, "zoho_list", 'class="inputbox" size="1"', 'value', 'text', $value['zoho_list']); ?>
		</td>
	</tr>
	<tr id="trzohocv">
		<td class="acykey">
			<?php echo acymailing_tooltip(acymailing_translation('CUSTOM_VIEW_DESC'), acymailing_translation('CUSTOM_VIEW'), '', acymailing_translation('CUSTOM_VIEW')); ?>
		</td>
		<td>
			<input class="inputbox" type="text" name="zoho_cv" size="35" value="<?php echo $this->escape($this->config->get('zoho_cv')); ?>">
		</td>
	</tr>
</table>


<span class="acyblocktitle" style="margin-top: 20px;"><?php echo acymailing_translation('FIELD'); ?></span>
<?php
$subfields = acymailing_getColumns('#__acymailing_subscriber');
$acyfields = array();
$acyfields[] = acymailing_selectOption('', ' - - - ');
if(!empty($subfields)){
	foreach($subfields as $oneField => $typefield){
		if(in_array($oneField, array('subid', 'confirmed', 'enabled', 'key', 'userid', 'accept', 'html', 'created', 'zohoid', 'zoholist', 'email'))) continue;
		$acyfields[] = acymailing_selectOption($oneField, $oneField);
	}
}
?>
<table <?php echo $this->isAdmin ? 'class="acymailing_table"' : 'class="admintable table" cellspacing="1"' ?>>
	<?php
	echo '<tr><td class="acykey">'.acymailing_translation('ACY_LOADZOHOFIELDS').'</td><td>';
	echo '<input type="submit" class="btn" onclick="acymailing.submitbutton(\'loadZohoFields\')" value="'.acymailing_translation('ACY_LOADFIELDS').'"></td></tr>';

	$fields = explode(',', $config->get('zoho_fieldsname', 'First Name,Last Name,Date of Birth'));

	foreach($fields as $oneField){
		$fieldValue = '';
		if(!empty($value['zoho_fields'][$oneField])) $fieldValue = $value['zoho_fields'][$oneField];
		echo '<tr><td class="acykey">'.$oneField.'</td><td><div id="zoho_fields">'.acymailing_select($acyfields, "zoho_fields[".$oneField."]", 'class="inputbox" size="1"', 'value', 'text', $fieldValue).'</div></td></tr>';
	}
	?>
</table>


