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

use Joomla\Utilities\ArrayHelper;

/** @var  stdClass $displayData */
$field = (object)$displayData;

$class     = !empty($field->class) ? ' btn-group ' . $field->class : ' btn-group';
$required  = $field->required ? ' required aria-required="true"' : '';
$autofocus = $field->autofocus ? ' autofocus' : '';
$disabled  = $field->disabled ? ' disabled' : '';
$readonly  = $field->readonly;

if (!$field->categories)
{
	return;
}
?>
<div id="<?php echo $field->id ?>_wrap" class="w100p bg-color-white padding-5">
<table class="table-commission table-minpadding table-hover">
	<?php if ($field->context): ?>
	<thead>
		<tr>
			<th class="text-left"><?php
				echo JText::_('COM_SELLACIOUS_CATEGORY_FIELD_COMMISSIONS_HEADING_' . strtoupper($field->context) . '_CATEGORY'); ?></th>
			<th class="text-right"><?php
				echo JText::_('COM_SELLACIOUS_CATEGORY_FIELD_COMMISSIONS_HEADING_COMMISSION'); ?></th>
		</tr>
	</thead>
	<?php endif; ?>
	<tbody>
<?php foreach ($field->categories as $i => $category): ?>
	<tr class="commission-row-<?php echo $category->id > 1 ? $i % 2 + 1 : 0; ?>">
		<td style="padding-right: 20px">
			<?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $category->level)); ?>
			<?php echo $category->title ?>
		</td>
		<td>
			<?php $value = ArrayHelper::getValue($field->value, $category->id); ?>
			<div class="input-control <?php echo $class ?>" data-toggle="buttons">
				<input type="hidden" name="<?php echo $field->name ?>[<?php echo $category->id ?>]" class="input-h" value="<?php echo $value ?>"/>
				<input type="text" value="<?php echo $value ?>"
					   class="form-control w100px pull-left input-ui <?php echo $required ?>" <?php echo $required . $autofocus ?>
					   placeholder="<?php echo JText::_('COM_SELLACIOUS_INPUT_PLACEHOLDER_AMOUNT'); ?>"/>
				<?php
				// sprintf: class, disabled, value, checked, required, disabled, label
				$choice = <<<HTML
				<label class="btn btn-default %s" %s>
					<input type="radio" name="{$field->id}_radio" value="%s" %s %s/>
					<span>%s</span>
				</label>
HTML;

				$percent = substr($value, -1) == '%';

				$val        = '%';
				$label      = '%';
				$checked    = $percent ? ' checked="checked"' : '';
				$o_class    = $percent ? ' active' : '';
				$o_disabled = $disabled || ($readonly && !$checked) ? ' disabled' : '';

				echo sprintf($choice, $o_class, $o_disabled, $val, $checked, $o_disabled, $label);

				$val        = '';
				$label      = $field->currency;
				$checked    = $percent ? '' : ' checked="checked"';
				$o_class    = $percent ? '' : ' active';
				$o_disabled = $disabled || ($readonly && !$checked) ? ' disabled' : '';

				echo sprintf($choice, $o_class, $o_disabled, $val, $checked, $o_disabled, $label);
				?>
			</div>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
</table>
</div>
