<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$table_css = $this->Fields->styles($table_css);
	$tr_css = $this->Fields->styles($tr_css);
	$td_css = $this->Fields->styles($td_css);
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" <?php echo $table_align; ?> <?php echo $bgcolor_attr; ?> style="<?php echo $table_css; ?>">
	<tbody>
		<tr <?php echo $bgcolor_attr; ?> style="<?php echo $tr_css; ?>">
			<td width="100%" <?php echo $bgcolor_attr; ?> <?php echo $td_align; ?> style="<?php echo $td_css; ?>">
				<section>
					<?php foreach($elements as $subelement): ?>
						<?php if(($subelement['parent_id'] == $element['id'])): ?>
							<?php $this->view('views.emails.element', ['element' => $subelement, 'elements' => $elements]); ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</section>
			</td>
		</tr>
	</tbody>
</table>