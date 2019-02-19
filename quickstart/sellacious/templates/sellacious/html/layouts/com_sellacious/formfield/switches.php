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

/** @var  array $displayData */
$field   = (object) $displayData;
$helper	 = SellaciousHelper::getInstance();
?>
<div id="<?php echo $field->id; ?>">
	<div class="row">
		<?php foreach ($field->options as $i => $option) : ?>
			<div class="col-xs-6 col-sm-6 col-md-4 col-lg-3" style="margin:0 0px 5px 0;">
				<div class="pull-left nowrap" style="padding-right: 15px;">
					<?php echo JText::_($option->text); ?>
				</div>
				<div class="pull-right nowrap" width="110px">
					<div class="btn-group" data-toggle="buttons">
						<label for="<?php echo $field->id ?>_0" class="btn btn-default btn-xs <?php if (empty($field->value[$i])) echo ' active' ?>">
							<input type="radio" id="<?php echo $field->id ?>_0" name="<?php echo $field->name ?>[<?php $i ?>]" value="0" <?php if (empty($field->value[$i])) echo ' checked="checked"' ?> />
							<span><?php echo JText::_('JHIDE') ?></span>
						</label>
						<label for="<?php echo $field->id ?>_1" class="btn btn-default btn-xs <?php if (!empty($field->value[$i])) echo ' active' ?>">
							<input type="radio" id="<?php echo $field->id ?>_1" name="<?php echo $field->name ?>[<?php $i ?>]" value="1" <?php if (!empty($field->value[$i])) echo ' checked="checked"' ?> />
							<span><?php echo JText::_('JSHOW') ?></span>
						</label>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
