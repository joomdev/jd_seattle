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

/** @var  PlgSystemSellaciousImporter  $this */
/** @var  stdClass  $displayData */
$template = $displayData;
?>
<?php if (count($template->mapping)): ?>
	<p class="red"><?php echo JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_FORM_NOTE_INLINE'); ?></p>

	<div class="sample-table bg-color-white">
		<div class="scroll-x">
			<table class="table table-bordered">
				<tbody>
				<tr>
					<?php foreach ($template->mapping as $col => $alias): ?>
						<td class="center"> <?php echo htmlspecialchars($alias, ENT_COMPAT, 'UTF-8'); ?> </td>
					<?php endforeach; ?>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
<?php endif; ?>
