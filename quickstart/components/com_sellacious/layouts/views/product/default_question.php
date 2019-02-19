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

/** @var   SellaciousViewProduct $this */
$form = $this->getQuestionForm();

// Only proceed if it is a valid JForm
if (!$form)
{
	return;
}
?>
<div id="questionBox">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="questionForm"
		  id="questionForm" class="form-validate form-vertical" enctype="multipart/form-data">

		<fieldset>
			<?php
			echo $form->getInput('p_id');
			echo $form->getInput('v_id');
			echo $form->getInput('s_uid');

			$questioner_name  = $form->getField('questioner_name');
			$questioner_email = $form->getField('questioner_email');

			?>

			<div class="questionformarea">
				<?php if ($questioner_name || $questioner_email): ?>
					<div class="sell-row">
						<?php if ($field = $questioner_name): ?>
							<div class="sell-col-xs-12 <?php echo $questioner_name ? 'sell-col-sm-6' : ''; ?>">
								<div class="formfield">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($field = $questioner_email): ?>
							<div class="sell-col-xs-12 <?php echo $questioner_email ? 'sell-col-sm-6' : ''; ?>">
								<div class="formfield">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ($field = $form->getField('question')): ?>
					<div class="formfield">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>
				<?php if ($field = $form->getField('captcha')): ?>
					<div class="formfieldcaptcha">
						<?php echo $field->input; ?>
					</div>
				<?php endif; ?>

				<button type="button" class="btn btn-primary questionbtn" onclick="Joomla.submitform('product.saveQuestion', this.form);">
					<i class="fa fa-location-arrow"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_SUBMIT'); ?></button>
		</fieldset>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
