<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="config_plugins">

	<div class="acyblockoptions" style="width: 42%;min-width: 480px;">
		<span class="acyblocktitle"><?php echo acymailing_translation('PLUG_TAG') ?></span>
		<table class="acymailing_table" cellpadding="1">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo acymailing_translation('ACY_NUM'); ?>
				</th>
				<th class="title">
					<?php echo acymailing_translation('ACY_NAME'); ?>
				</th>
				<th class="title titleid">
					<?php echo acymailing_translation('ACY_IS_UPDATE') ?>
				</th>
				<th class="title titletoggle">
					<?php echo acymailing_translation('ENABLED'); ?>
				</th>
				<th class="title titleid">
					<?php echo acymailing_translation('ACY_ID'); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
			$k = 0;

			for($i = 0, $a = count($this->plugins); $i < $a; $i++){
				$row =& $this->plugins[$i];

				$publishedid = 'published_'.$row->id;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center" style="text-align:center">
						<?php echo $i + 1 ?>
					</td>
					<td>
						<a target="_blank" href="<?php echo !ACYMAILING_J16 ? 'index.php?option=com_plugins&amp;view=plugin&amp;client=site&amp;task=edit&amp;cid[]=' : 'index.php?option=com_plugins&amp;task=plugin.edit&amp;extension_id=';
						echo $row->id ?>"><?php echo $row->name; ?></a>
					</td>
					<td style="text-align: center">
						<?php if(empty($row->needUpDate)){
							echo '<a href="#" class="acyicon-apply" onclick="return false;"></a>';
						}else{
							echo '<a href="https://www.acyba.com/acymailing/plugins.html#'.$row->element.'" class="acyicon-cancel" target="_blank"></a>';
						} ?>
					</td>
					<td align="center" style="text-align:center">
						<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid, $row->published, 'plugins') ?></span>
					</td>
					<td align="center" style="text-align:center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
	</div>
	<div class="acyblockoptions" style="width: 42%;min-width: 480px;">
		<span class="acyblocktitle"><?php echo acymailing_translation('PLUG_INTE') ?></span>
		<table class="acymailing_table" cellpadding="1">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo acymailing_translation('ACY_NUM'); ?>
				</th>
				<th class="title">
					<?php echo acymailing_translation('ACY_NAME'); ?>
				</th>
				<th class="title">
					<?php echo acymailing_translation('ACY_IS_UPDATE') ?>
				</th>
				<th class="title titletoggle">
					<?php echo acymailing_translation('ENABLED'); ?>
				</th>
				<th class="title titleid">
					<?php echo acymailing_translation('ACY_ID'); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
			$k = 0;

			for($i = 0, $a = count($this->integrationplugins); $i < $a; $i++){
				$row =& $this->integrationplugins[$i];

				$publishedid = 'published_'.$row->id;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center" style="text-align:center">
						<?php echo $i + 1 ?>
					</td>
					<td>
						<a target="_blank" href="<?php echo !ACYMAILING_J16 ? 'index.php?option=com_plugins&amp;view=plugin&amp;client=site&amp;task=edit&amp;cid[]=' : 'index.php?option=com_plugins&amp;task=plugin.edit&amp;extension_id=';
						echo $row->id ?>"><?php echo $row->name; ?></a>
					</td>
					<td style="text-align: center">
						<?php if(empty($row->needUpDate)){
							echo '<a href="#" class="acyicon-apply" target="blank"></a>';
						}else{
							echo '<a href="https://www.acyba.com/acymailing/plugins.html#'.$row->element.'" class="acyicon-cancel" target="_blank"></a>';
						} ?>
					</td>
					<td align="center" style="text-align:center">
						<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid, $row->published, 'plugins') ?></span>
					</td>
					<td align="center" style="text-align:center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
	</div>
	<span class="acymailing_button" style="margin:15px;">
		<i class="acyicon-import"></i>
		<a style="margin-left:5px;color:#fff;text-decoration: none;" href="https://www.acyba.com/acymailing/plugins.html" target="_blank"><?php echo acymailing_translation('MORE_PLUGINS'); ?></a>
	</span>
</div>
