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

JHtml::_('script', 'com_sellacious/util.float-val.js', array('version' => S_VERSION_CORE, 'relative' => true));

/** @var  JLayoutFile  $this */
/** @var  stdClass     $displayData */
$field  = $displayData;
$helper = SellaciousHelper::getInstance();
$credit = $helper->config->get('allow_credit_limit');

$disable  = $field->readonly || $field->disabled;
$readonly = $disable ? ' disabled ' : '';
?>
<?php $prefix = 'COM_SELLACIOUS_USERGRID_FIELD_GRID_HINT'; ?>
<div class="jff-ug-wrapper <?php echo $field->class ?>" data-name="<?php echo $field->name ?>">
	<table id="<?php echo $field->id; ?>" class="table table-striped table-hover table-noborder">
		<thead>
		<tr class="jff-ug-inputs">
			<th class="center" style="padding: 6px 0"><?php echo JText::_($prefix . '_EMAIL') ?></th>
			<th class="center"><?php echo JText::_($prefix . '_NAME') ?></th>
			<?php if ($credit == 2): ?>
			<th class="center" style="width: 110px;"><?php echo JText::_($prefix . '_CREDIT_LIMIT') ?> (<?php echo $field->currency ?>)</th>
			<?php endif; ?>
			<?php if (!$disable): ?>
			<th class="center" style="width: 40px;"></th>
			<?php endif; ?>
		</tr>
		<?php if (!$disable): ?>
		<tr class="jff-ug-inputs">
			<td>
				<input type="hidden" class="inputbox jff-ug-input-id novalidate"/>
				<input type="email" class="inputbox jff-ug-input-email novalidate"
				       placeholder="<?php echo JText::_($prefix . '_EMAIL') ?>" <?php echo $readonly ?>/>
			</td>
			<td><input type="text" class="inputbox jff-ug-input-name novalidate"
			           placeholder="<?php echo JText::_($prefix . '_NAME') ?>" <?php echo $readonly ?>/></td>
			<?php if ($credit == 2): ?>
			<td style="width: 110px;"><input type="text" class="inputbox jff-ug-input-cl novalidate" data-float="2"
			           placeholder="<?php echo JText::_($prefix . '_CREDIT_LIMIT') ?>" <?php echo $readonly ?>/></td>
			<?php endif; ?>
			<td>
				<a class="btn btn-primary jff-ug-add disabled"><i class="fa fa-check"></i> <?php echo JText::_('COM_SELLACIOUS_ADD_LABEL') ?></a>
			</td>
		</tr>
		<?php endif; ?>
		</thead>
		<tbody class="jff-ug-items">
		<?php foreach ($field->lists as $i => $item): ?>
			<tr>
				<td>
					<input type="hidden" class="inputbox" name="<?php echo $field->name ?>[email][]" value="<?php echo $item['email'] ?>" <?php echo $readonly ?>/>
					<div class="input"><?php echo $item['email'] ?></div>
				</td>
				<td>
					<input type="hidden" class="inputbox" name="<?php echo $field->name ?>[name][]" value="<?php echo $item['name'] ?>" <?php echo $readonly ?>/>
					<div class="input"><?php echo $item['name'] ?></div>
				</td>
				<?php if ($credit): ?>
				<td>
					<input type="text" class="inputbox" name="<?php echo $field->name ?>[credit_limit][]" data-float="2"
					       value="<?php echo $item['credit_limit'] ?>" title="Credit Limit" <?php echo $readonly ?>/>
				</td>
				<?php endif; ?>
				<?php if (!$disable): ?>
				<td>
					<input type="hidden" class="inputbox" name="<?php echo $field->name ?>[id][]" value="<?php echo $item['id'] ?>" <?php echo $readonly ?>/>
					<a class="btn btn-danger jff-ug-remove"><i class="fa fa-minus"></i> </a>
				</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
