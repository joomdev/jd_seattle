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
acymailing_cmsLoaded();
?>
<table class="adminlist table table-striped table-hover" cellpadding="1">
	<thead>
		<tr>
			<th class="title">
				<?php echo acymailing_translation('ACY_FILTER'); ?>
			</th>
			<th class="title titletoggle">
				<?php echo acymailing_translation('PUBLISHED'); ?>
			</th>
			<th class="title titletoggle" >
				<?php echo acymailing_translation( 'DELETE' ); ?>
			</th>
			<th class="title titleid">
				<?php echo acymailing_translation( 'ACY_ID' ); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$k = 0;
			foreach($this->filters as $row){
				$publishedid = 'published_'.$row->filid;
				$id = 'filter_'.$row->filid;
		?>
			<tr class="<?php echo "row$k"; ?>" id="<?php echo $id; ?>">
				<td style="cursor:pointer" onclick="window.top.location.href = '<?php echo acymailing_completeLink('filter&task=edit&filid='.$row->filid); ?>';">
					<?php
						echo acymailing_tooltip($row->description, $row->name, '', $row->name);
					?>
				</td>
				<td align="center" style="text-align:center" >
						<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->published,'filter') ?></span>
				</td>
				<td align="center" style="text-align:center" >
					<?php echo $this->toggleClass->delete($id,$row->filid.'_'.$row->filid,'filter',true); ?>
				</td>
				<td width="1%" align="center" style="cursor:pointer" onclick="window.top.location.href = '<?php echo acymailing_completeLink('filter&task=edit&filid='.$row->filid); ?>';">
					<?php echo $row->filid; ?>
				</td>
			</tr>
		<?php
				$k = 1-$k;
			}
		?>
	</tbody>
</table>

