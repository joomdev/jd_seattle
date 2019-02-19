<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$data = (object)$displayData;
?>
<h1>DON'T USE THIS OLD CURRENCY STORAGE FORMAT</h1>
<div class="input-group">
	<input type="text"
		   name="<?php echo $data->name ?>[a]"
		   id="<?php echo $data->id ?>_a" <?php echo $data->dirname ?>
		   class="<?php echo $data->class ?> combobox-input-sm"
		   value="<?php echo htmlspecialchars($data->value['a'], ENT_COMPAT, 'UTF-8') ?>"
		   title="" <?php echo $data->size . $data->disabled . $data->readonly . $data->hint . $data->onchange . $data->maxLength .
							   $data->required . $data->autocomplete . $data->autofocus . $data->spellcheck . $data->inputmode . $data->pattern ?>/>

	<?php echo JHtml::_('select.genericlist', $data->options, $data->name . '[c]', 'class="' . $data->class . ' combobox-list-md" ' . $data->disabled . $data->readonly . $data->onchange . $data->required, 'id', 'title', $data->value['c']); ?>
</div>
