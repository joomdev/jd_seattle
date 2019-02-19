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

JHtml::_('jquery.framework');

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

JHtml::_('script', 'media/com_sellacious/js/plugin/select2/select2.min.js', array('version' => S_VERSION_CORE));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');
/** @var array $displayData */
?>
<script type="text/javascript">
jQuery(document).ready(function ($) {
	// Skip already converted select2
	$('select').not('.select2-offscreen').select2();
});

Joomla.submitbutton = function (task) {
	var form = document.getElementById('adminForm');
	var task2 = task.split('.')[1] || '';
	if (task2 == 'setType' || task2 == 'cancel' || document.formvalidator.isValid(form)) {
		Joomla.submitform(task, form);
	} else {
		alert(Joomla.JText._('COM_SELLACIOUS_VALIDATION_FORM_FAILED'));
	}
}
</script>
<div class="row editboxes">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" enctype="multipart/form-data">
		<?php
		/** @var  JForm  $form */
		$form      = $displayData['form'];
		$fieldsets = $form->getFieldsets();

		foreach ($fieldsets as $fs_key => $fieldset)
		{
			$visible = array();
			$fields  = $form->getFieldset($fieldset->name);

			// echo hidden input right away, and collect others for the box
			foreach ($fields as $field)
			{
				if ($field->hidden)
				{
					echo $field->input;
				}
				else
				{
					$visible[] = $field;
				}
			}

			if (count($visible))
			{
				?>
				<article class="col-sm-12 col-md-12 col-lg-<?php echo isset($fieldset->width) ? $fieldset->width : '6' ?>">
					<!-- Widget ID (each widget will need unique ID)-->
					<div class="jarviswidget" id="wid-id-<?php echo $fs_key ?>">
						<?php if ($fieldset->label): ?>
						<header><span class="widget-icon"><i class="fa fa-tasks"></i></span>
							<h2><?php echo JText::_($fieldset->label, true) ?></h2></header>
						<?php endif; ?>
						<!-- widget content -->
						<div class="widget-body edittabs">
							<fieldset>
								<?php
								foreach ($visible as $field)
								{
									?>
									<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
										<?php
										if ($field->type == 'Note')
										{
											echo '<div class="controls col-md-12">' . $field->label . '</div>';
										}
										elseif ($field->label == '' || (isset($fieldset->width) && $fieldset->width == 12))
										{
											echo '<div class="controls col-md-12">' . $field->input . '</div>';
										}
										else
										{
											echo '<div class="form-label col-sm-4 col-md-4 col-lg-3">' . $field->label . '</div>';
											echo '<div class="controls col-sm-8 col-md-8 col-lg-9">' . $field->input . '</div>';
										}
										?>
									</div>
									<div class="clearfix"></div>
									<?php
								}
								?>
							</fieldset>
						</div>
						<!-- end widget content -->
					</div>
					<!-- end widget -->
				</article>
				<?php
			}
		}
		?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
