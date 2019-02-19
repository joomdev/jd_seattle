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

/** @var   SellaciousViewProduct  $this */
$form = $this->getReviewForm();

// Only proceed if it is a valid JForm
if (!$form)
{
	return;
}
?>
<div class="reviewform" id="reviewBox">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="ratingForm"
		  id="ratingForm" class="form-validate form-vertical" enctype="multipart/form-data">

		<fieldset>
			<?php
			echo $form->getInput('product_id');
			echo $form->getInput('variant_id');
			echo $form->getInput('seller_uid');

			$author_name  = $form->getField('author_name');
			$author_email = $form->getField('author_email');

			?>

			<div class="revformarea">
				<?php if ($author_name || $author_email): ?>
					<div class="sell-row">
						<?php if ($field = $author_name): ?>
							<div class="sell-col-xs-12 <?php echo $author_name ? 'sell-col-sm-6' : ''; ?>">
								<div class="formfield">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($field = $author_email): ?>
							<div class="sell-col-xs-12 <?php echo $author_email ? 'sell-col-sm-6' : ''; ?>">
								<div class="formfield">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ($field = $form->getField('rating', 'product')): ?>
					<div class="formfieldstar">
						<?php echo $field->label; ?>
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>
				<?php if ($field = $form->getField('title', 'product')): ?>
					<div class="formfield">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>
				<?php if ($field = $form->getField('comment', 'product')): ?>
					<div class="formfield">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>

				<?php
				$fieldSR[] = $form->getField('rating', 'seller');
				$fieldSR[] = $form->getField('rating', 'packaging');
				$fieldSR[] = $form->getField('rating', 'shipment');
				$fieldSR   = array_filter($fieldSR);

				if (count($fieldSR)): ?>
					<div class="formfieldstar">
						<?php foreach ($fieldSR as $field): ?>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ($field = $form->getField('title', 'seller')): ?>
					<div class="formfield">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>

				<?php if ($field = $form->getField('comment', 'seller')): ?>
					<div class="formfield">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>

				<?php if ($field = $form->getField('title', 'packaging')): ?>
					<div class="formfield">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>

				<?php if ($field = $form->getField('comment', 'packaging')): ?>
					<div class="formfield">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>

				<?php if ($field = $form->getField('title', 'shipment')): ?>
					<div class="formfield">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>

				<?php if ($field = $form->getField('comment', 'shipment')): ?>
					<div class="formfield">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>

				<button type="button" class="btn btn-primary reviewbtn" onclick="Joomla.submitform('product.saveRating', this.form);"><i class="fa fa-edit"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_RATING_SUBMIT'); ?></button>
			</div>
		</fieldset>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
