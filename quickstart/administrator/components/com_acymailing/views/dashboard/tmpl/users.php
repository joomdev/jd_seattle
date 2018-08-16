<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><br style="font-size:1px;"/>
<div id="dash_users">

	<h1 class="acy_graphtitle"> <?php echo acymailing_translation('ACY_LAST_TEN_SUBSCRIBERS') ?> </h1>
	<table class="acymailing_table" cellpadding="1">
		<thead>
		<tr>
			<th class="title">
				<?php echo acymailing_translation('JOOMEXT_NAME'); ?>
			</th>
			<th class="title">
				<?php echo acymailing_translation('JOOMEXT_EMAIL'); ?>
			</th>
			<th class="title titledate">
				<?php echo acymailing_translation('CREATED_DATE'); ?>
			</th>
			<th class="title titletoggle">
				<?php echo acymailing_translation('RECEIVE_HTML'); ?>
			</th>
			<?php if($this->config->get('require_confirmation', 1)){ ?>
				<th class="title titletoggle">
					<?php echo acymailing_translation('CONFIRMED'); ?>
				</th>
			<?php } ?>
		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		foreach($this->users as $oneUser){
			$row =& $oneUser;

			$confirmedid = 'confirmed_'.$row->subid;
			$htmlid = 'html_'.$row->subid;

			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $this->escape($row->name); ?>
				</td>
				<td>
					<a href="<?php echo acymailing_completeLink('subscriber&task=edit&subid='.$row->subid) ?>"><?php echo $this->escape($row->email); ?></a>
				</td>
				<td align="center" style="text-align:center">
					<?php echo acymailing_getDate($row->created); ?>
				</td>
				<td align="center" style="text-align:center">
					<span id="<?php echo $htmlid ?>" class="loading"><?php echo $this->toggleClass->toggle($htmlid, $row->html, 'subscriber') ?></span>
				</td>
				<?php if($this->config->get('require_confirmation', 1)){ ?>
					<td align="center" style="text-align:center">
						<span id="<?php echo $confirmedid ?>" class="loading"><?php echo $this->toggleClass->toggle($confirmedid, $row->confirmed, 'subscriber') ?></span>
					</td>
				<?php } ?>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
	</table>
</div>
