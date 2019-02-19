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

/** @var  SellaciousViewProduct  $this */
$choices = $this->getVariantChoices();

if (!$choices)
{
	return;
}
?>
<hr class="isolate"/>
<form action="<?php echo JUri::getInstance()->toString() ?>" method="post" id="varForm" name="varForm">

	<div class="variant-picker">
		<?php foreach ($choices as $choice): ?>
			<div class="variant-choice">
				<h5><?php echo $choice->title ?></h5>

				<div class="radio">
					<?php
					foreach ($choice->options as $option):
						$o_value  = htmlspecialchars($option);
						$o_text   = $this->helper->field->renderValue($option, $choice->type);

						$selected = $choice->selected == $option ? ' checked' : '';

						if (in_array($option, $choice->available)):
							$availability = 'available-option';
							$disabled     = '';
						else:
							$availability = 'unavailable-option';
							$disabled     = 'disabled';
						endif;

						if($choice->type == 'color'): ?>
							<label class="colors-option <?php echo $availability; echo ($selected) ? ' selected' : ''; ?>">
								<input type="radio" class="variant_spec" name="jform[variant_spec][<?php echo $choice->id ?>]"
									   value="<?php echo $o_value ?>" <?php echo $selected . ' ' . $disabled ?>>
								<span class="variant_spec" style="background: <?php echo $option ?>"></span>
							</label>
							<?php
						else: ?>
							<label class="varaint-options <?php echo $availability; echo ($selected) ? ' selected' : ''; ?>">
								<input type="radio" class="variant_spec" name="jform[variant_spec][<?php echo $choice->id ?>]"
									   value="<?php echo $o_value ?>" <?php echo $selected . ' ' . $disabled ?>><?php echo $o_text ?>
							</label><?php
						endif; ?>
						<?php
					endforeach;
					?>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="clearfix"></div>
	</div>

	<input type="hidden" name="option" value="com_sellacious"/>
	<input type="hidden" name="view" value="product"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="p" value="<?php echo $this->state->get('product.code') ?>"/>

	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clearfix"></div>

