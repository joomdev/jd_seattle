<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="page-tracking">
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('ACY_CONFIDENTIALITY'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('ACY_GDPR_EXPORT_BUTTON_DESC'), '', '', acymailing_translation('ACY_GDPR_EXPORT_BUTTON')); ?>
				</td>
				<td>
					<?php
						echo acymailing_boolean("config[gdpr_export]", '', $this->config->get('gdpr_export', 0));
					?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('ACY_GDPR_DELETE_BUTTON_DESC'), '', '', acymailing_translation('ACY_GDPR_DELETE_BUTTON')); ?>
				</td>
				<td>
					<?php
						echo acymailing_boolean("config[gdpr_delete]", '', $this->config->get('gdpr_delete', 0));
					?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('ACY_ANONYMOUS_TRACKING_DESC'), '', '', acymailing_translation('ACY_ANONYMOUS_TRACKING')); ?>
				</td>
				<td>
					<?php
						echo acymailing_boolean("config[anonymous_tracking]", '', $this->config->get('anonymous_tracking', 0));
					?>
				</td>
			</tr>
		</table>
	</div>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('TRACKING'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('TRACKINGSYSTEM'); ?>
				</td>
				<td>
					<?php echo $this->elements->tracking_system; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_TRACKINGSYSTEM_EXTERNAL_LINKS'); ?>
				</td>
				<td>
					<?php echo $this->elements->tracking_system_external_website; ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('GEOLOCATION'); ?></span>
		<script language="JavaScript" type="text/javascript">
			function testAPI(id, newvalue){
				window.document.getElementById(id).className = 'onload';

				var xhr = new XMLHttpRequest();
				xhr.open('GET', '<?php echo acymailing_prepareAjaxURL('toggle'); ?>&task=' + id + '&value=' + newvalue);
				xhr.onload = function(){
					window.document.getElementById(id).innerHTML = xhr.responseText;
					window.document.getElementById(id).className = 'loading';
				};
				xhr.send();
			}
		</script>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('GEOLOCATION_TYPE_DESC'), acymailing_translation('GEOLOCATION_TYPE'), '', acymailing_translation('GEOLOCATION_TYPE')); ?>
				</td>
				<td>
					<?php echo $this->elements->geolocation; ?>
				</td>
			</tr>
			<?php if($this->elements->geoloc_api_key){ ?>
				<tr>
					<td class="acykey">
						<a href="http://ipinfodb.com/register.php" target="_blank"><?php echo acymailing_tooltip(acymailing_translation('GEOLOCATION_API_KEY_DESC'), 'IPInfoDB API key', '', 'IPInfoDB API key'); ?></a>
					</td>
					<td>
						<?php echo $this->elements->geoloc_api_key; ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">

						<span id="testApiKey" class="acymailing_button_grey">
							<i class="acyicon-location"></i>
							<a style="color:#666;text-decoration:none;" href="javascript:void(0);" onclick="testAPI('testApiKey',window.document.getElementById('geoloc_api_key').value)"><?php echo acymailing_translation('GEOLOC_TEST_API_KEY'); ?></a>
						</span>
					</td>
				</tr>
				<tr>
					<td class="acykey">
						<a href="https://www.acyba.com/acymailing/350-acymailing-geolocation.html#accountsetup" target="_blank"><?php echo acymailing_tooltip(acymailing_translation('ACY_GOOGLE_MAP_KEY_DESC'), acymailing_translation('ACY_GOOGLE_MAP_KEY'), '', acymailing_translation('ACY_GOOGLE_MAP_KEY')) ?></a>
					</td>
					<td>
						<?php echo $this->elements->google_map_api_key; ?>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
</div>
