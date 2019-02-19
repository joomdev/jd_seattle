<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  array  $displayData */
$field = (object) $displayData;
JHtml::_('script', 'com_sellacious/util.float-val.js', array('relative' => true, 'version' => S_VERSION_CORE));
?>
<div class="input-group" id="<?php echo $field->id ?>">
	<input type="text" name="<?php echo $field->name ?>[m]" id="<?php echo $field->id ?>_m" <?php echo $field->dirname ?>
		   class="<?php echo $field->class ?> combobox-input-sm" data-float="2" style="margin-right: -1px;"
		   value="<?php echo htmlspecialchars($field->value['m'], ENT_COMPAT, 'UTF-8') ?>" title=""
		<?php echo $field->size . $field->disabled . $field->readonly . $field->hint . $field->onchange . $field->maxLength .
			$field->required . $field->autocomplete . $field->autofocus . $field->spellcheck . $field->inputmode . $field->pattern ?>
	/><?php echo JHtml::_('select.genericlist', $field->options, $field->name.'[u]', 'class="'.$field->class.' combobox-list-md" ' .
		$field->disabled . $field->readonly .$field->onchange . $field->required, 'id', 'title', $field->value['u']); ?>
</div>

