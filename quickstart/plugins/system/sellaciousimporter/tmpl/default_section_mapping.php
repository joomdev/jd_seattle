<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Sellacious\Import\AbstractImporter;

/** @var  PlgSystemSellaciousImporter  $this */
/** @var  stdClass  $displayData */
$template = $displayData;

/** @var  AbstractImporter  $importer */
$importer = $this->getImporter();
$import   = $this->getActiveImport();
$aliases  = $import ? $import->mapping->toArray() : array();
$headers  = $importer->getHeaders();
$require  = array();

if ($template->id && count($template->mapping))
{
	$colKeys   = array_keys($template->mapping);
	$colValues = array_values($template->mapping);
}
else
{
	$columns   = $importer->getColumns();
	$colKeys   = $columns;
	$colValues = $columns;
}

$countC = count($colKeys);
$countH = count($headers);

JHtml::_('jquery.framework');
JHtml::_('script', 'com_importer/jquery-ui-sortable.min.js', false, true);
?>
<table class="w100p sortable-area table-column-map table-bordered table-drag bg-color-white">
	<thead>
	<tr>
		<th><?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_HEADING_COLUMNS_IMPORTABLE')?></th>
		<th style="min-width: 200px;"><?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_HEADING_COLUMNS_UPLOADED_MAPPING')?></th>
		<th><?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_HEADING_COLUMNS_UPLOADED')?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	for ($row = 0; $row < $countC; $row++)
	{
		$colKey   = $colKeys[$row];
		$colValue = $colValues[$row];
		$required = in_array($colKey, $require);
		?>
		<tr class="column-map">
			<td data-column="<?php echo htmlspecialchars($colKey, ENT_COMPAT, 'UTF-8'); ?>"><?php
				echo htmlspecialchars($colValue, ENT_COMPAT, 'UTF-8'); ?>
				<?php echo $required ? '<span class="red">*</span>' : ''; ?>
			</td>
			<td>
				<ul class="sortable-group alias-drop" data-column="<?php echo htmlspecialchars($colKey, ENT_COMPAT, 'UTF-8'); ?>">
					<?php if (count($aliases)): ?>
						<?php if (isset($aliases[$colKey])): ?>
							<li class="sortable-item" data-alias="<?php
								echo htmlspecialchars($aliases[$colKey], ENT_COMPAT, 'UTF-8'); ?>"><?php
								echo htmlspecialchars($aliases[$colKey], ENT_COMPAT, 'UTF-8'); ?></li>
						<?php endif; ?>
					<?php else: ?>
						<?php if (in_array($colValue, $headers)): ?>
							<li class="sortable-item" data-alias="<?php
								echo htmlspecialchars($colValue, ENT_COMPAT, 'UTF-8'); ?>"><?php
								echo htmlspecialchars($colValue, ENT_COMPAT, 'UTF-8'); ?></li>
						<?php endif; ?>
					<?php endif; ?>
				</ul>
			</td>
			<?php if ($row == 0): ?>
				<td rowspan="<?php echo max($countH, $countC) ?>" class="v-top no-hover headers-cell">
					<div class="headers-container">
						<ul class="sortable-group headerList">
							<?php for ($index = 0; $index < $countH; $index++): ?>
								<?php $header = $headers[$index]; ?>
								<?php if (count($aliases)): ?>
									<?php if (!in_array($header, $aliases)): ?>
										<li class="sortable-item" data-alias="<?php
											echo htmlspecialchars($header, ENT_COMPAT, 'UTF-8'); ?>"><?php
												echo htmlspecialchars($header, ENT_COMPAT, 'UTF-8'); ?></li>
									<?php endif; ?>
								<?php else: ?>
									<?php if (!in_array($header, $colValues)): ?>
										<li class="sortable-item" data-alias="<?php
											echo htmlspecialchars($header, ENT_COMPAT, 'UTF-8'); ?>"><?php
												echo htmlspecialchars($header, ENT_COMPAT, 'UTF-8'); ?></li>
									<?php endif; ?>
								<?php endif; ?>
							<?php endfor; ?>
						</ul>
					</div>
				</td>
			<?php endif; ?>
		</tr>
		<?php
	}

	if ($countC < $countH)
	{
		?><tr><td colspan="2" rowspan="<?php echo $countH - $countC ?>">&nbsp;</td></tr><?php
	}
	?>
	</tbody>
</table>

<div class="clearfix"></div>
<br>
<?php if ($this->helper->access->check('template.create', null, 'com_importer')): ?>
<table class="w100p bg-color-white">
	<tr>
		<td><label class="input-label"><?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_TEMPLATE_SAVE_AS_LABEL'); ?> </label></td>
		<td colspan="2">
			<div class="input-group w100p">
				<input type="text" class="txt-save-mapping form-control" placeholder="Enter template name&hellip;">
				<span class="input-group-btn">
					<button type="button" class="btn btn-primary btn-save-mapping">
						<i class="fa fa-save"></i><i class="fa fa-spin fa-spinner hidden"></i>&nbsp;
						<?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_BUTTON_SAVE_MAPPING'); ?></button>
				</span>
			</div>
		</td>
	</tr>
</table>
<?php endif; ?>
