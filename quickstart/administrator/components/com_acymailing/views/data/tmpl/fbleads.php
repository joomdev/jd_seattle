<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><table class="acymailing_table">
	<tr>
		<td class="acykey">
			<label for="fbleads_token"><?php echo acymailing_tooltip(acymailing_translation('ACY_FBLEADS_TOKEN_DESC'), acymailing_translation('ACY_FBLEADS_TOKEN'), '', acymailing_translation('ACY_FBLEADS_TOKEN')); ?></label>
		</td>
		<td>
			<input type="text" style="width:160px" name="fbleads_token" id="fbleads_token" value="<?php echo $this->escape($this->config->get('fbleads_token')); ?>"/>
		</td>
	</tr>
	<tr>
		<td class="acykey">
			<label for="fbleads_adid"><?php echo acymailing_tooltip(acymailing_translation('ACY_FBLEADS_AD_FORM_ID_DESC'), 'Ad ID', '', 'Ad ID'); ?></label>
		</td>
		<td>
			<input type="text" style="width:160px" name="fbleads_adid" id="fbleads_adid" value="<?php echo $this->escape($this->config->get('fbleads_adid')); ?>"/>
		</td>
	</tr>
	<tr>
		<td class="acykey">
			<label for="fbleads_formid"><?php echo acymailing_tooltip(acymailing_translation('ACY_FBLEADS_AD_FORM_ID_DESC'), 'Form ID', '', 'Form ID'); ?></label>
		</td>
		<td>
			<input type="text" style="width:160px" name="fbleads_formid" id="fbleads_formid" value="<?php echo $this->escape($this->config->get('fbleads_formid')); ?>"/>
		</td>
	</tr>
	<tr>
		<td class="acykey">
			<label for="fbleads_mincreated"><?php echo acymailing_translation('ACY_FBLEADS_MINCREATED'); ?></label>
		</td>
		<td>
			<?php echo acymailing_calendar($this->config->get('fbleads_mincreated'), 'fbleads_mincreated', 'fbleads_mincreated', '%Y-%m-%d', array('style' => 'width:100px')); ?>
		</td>
	</tr>
	<tr>
		<td class="acykey">
			<label for="fbleads_maxcreated"><?php echo acymailing_translation('ACY_FBLEADS_MAXCREATED'); ?></label>
		</td>
		<td>
			<?php echo acymailing_calendar($this->config->get('fbleads_maxcreated'), 'fbleads_maxcreated', 'fbleads_maxcreated', '%Y-%m-%d', array('style' => 'width:100px')); ?>
		</td>
	</tr>
	<tr>
		<td class="acykey">
			<label for="fbleads_email"><?php echo acymailing_translation('EMAILCAPTION'); ?></label>
		</td>
		<td>
			<input type="text" style="width:160px" placeholder="email" name="fbleads_email" id="fbleads_email" value="<?php echo $this->escape($this->config->get('fbleads_email', 'email')); ?>"/>
		</td>
	</tr>
	<tr>
		<td class="acykey">
			<label for="fbleads_name"><?php echo acymailing_translation('NAMECAPTION'); ?></label>
		</td>
		<td>
			<input type="text" style="width:160px" placeholder="full_name" name="fbleads_name" id="fbleads_name" value="<?php echo $this->escape($this->config->get('fbleads_name', 'full_name')); ?>"/>
		</td>
	</tr>
</table>
