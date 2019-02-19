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

/** @var  JLayoutFile  $this */
/** @var  stdClass     $displayData */
$field = $displayData;
$views = $field->views;
?>
<div class="bg-color-white sef-alias-wrapper w100p" id="<?php echo $field->id ?>_wrapper">
	<input type="hidden" name="<?php echo $field->name ?>" id="<?php echo $field->id ?>"/>

	<table class="table table-striped table-hover table-noborder sef-alias-table" style="width: auto; min-width: 300px;">
		<thead>
		<tr role="row" class="cursor-pointer v-top">
			<th class="nowrap text-center">
				<?php echo JText::_('COM_SELLACIOUS_FIELD_SEF_ALIAS_VIEW_TITLE'); ?>
			</th>
			<th class="nowrap text-center" style="width: 150px;">
				<?php echo JText::_('COM_SELLACIOUS_FIELD_SEF_ALIAS_VIEW_ALIAS') ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($views as $view): ?>
		<tr>
			<td><label for="<?php echo $view->field_id ?>"><?php echo $view->label ?></label></td>
			<td><input type="text"
				   id="<?php echo $view->field_id ?>"
				   name="<?php echo $view->field_name ?>"
				   value="<?php echo $view->value ?: $view->default ?>"
				   <?php echo $view->disabled ? ' disabled="disabled"' : '' ?>
				   <?php echo $view->readonly ? ' readonly="readonly"' : '' ?>
			/></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
