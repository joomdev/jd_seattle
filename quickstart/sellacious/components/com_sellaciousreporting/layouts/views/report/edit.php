<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('jquery.framework');

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

JHtml::_('script', 'media/com_sellacious/js/plugin/select2/select2.min.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'media/com_sellacious/js/plugin/cookie/jquery.cookie.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/util.tabstate.js', array('version' => S_VERSION_CORE, 'relative' => true));

JHtml::_('script', 'com_sellaciousreporting/view.report.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellaciousreporting/view.report.css', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

$form = $this->form;
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			var $select = $('select');
			$select.css('width', '100%');
			$select.select2();
		});
	})(jQuery);

	Joomla.submitbutton = function (task) {
		var form = document.getElementById('adminForm');
		var task2 = task.split('.')[1] || '';
		if (task2 == 'setType' || task2 == 'setHandler' || task2 == 'cancel' || document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		} else {
			alert(Joomla.JText._('COM_SELLACIOUS_VALIDATION_FORM_FAILED'));
		}
	}
</script>
<div class="row">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<!-- NEW WIDGET START -->
		<article class="col-sm-12 col-md-12 col-lg-12">

			<!-- Widget ID (each widget will need unique ID)-->
			<div class="tabsedit-container-off">

				<!-- widget div-->
				<div>

					<!-- widget content -->
					<div class="widget-body edittabs">
						<?php
						$fieldsets = $form->getFieldsets();
						$visible   = array();

						// Find visible tabs
						foreach ($fieldsets as $fs_key => $fieldset)
						{
							$fields = $form->getFieldset($fieldset->name);

							$visible[$fs_key] = 0;

							// Skip if fieldset is empty.
							if (count($fields) == 0)
							{
								continue;
							}

							foreach ($fields as $field)
							{
								if (!$field->hidden)
								{
									$visible[$fs_key]++;
								}
							}
						}

						// Add links for visible tabs
						?>
						<ul id="myTab3" class="nav nav-tabs tabs-pull-left bordered">
							<?php
							$counter = 0;

							foreach ($fieldsets as $fs_key => $fieldset)
							{
								if ($visible[$fs_key])
								{
									$class = ($counter ? '' : ' active') . ((isset($fieldset->align) && $fieldset->align == 'right') ? 'pull-right' : '');
									?>
									<li class="hidden-xs hidden-sm <?php echo $class ?>">
										<a href="#tab-<?php echo $fs_key; ?>" data-toggle="tab">
											<i class="fa fa-tasks"></i>&nbsp;&nbsp;&nbsp;<?php echo JText::_($fieldset->label, true) ?>
										</a>
									</li>
									<?php
									$counter++;
								}
							}
							?>
							<li class="dropdown pull-left visible-xs visible-sm">
								<a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
									<i class="fa fa-lg fa-gear"></i> <b class="caret"></b></a>
								<ul class="dropdown-menu">
									<?php
									$counter = 0;

									foreach ($fieldsets as $fs_key => $fieldset)
									{
										if ($visible[$fs_key])
										{
											$counter++;
											?>
											<li>
												<a href="#tab-<?php echo $fs_key; ?>"
												   data-toggle="tab"><?php echo JText::_($fieldset->label, true) ?></a>
											</li>
											<?php
										}
									}
									?>
								</ul>
							</li>
							<?php
							?></ul><?php
						// Add content for visible tabs
						?>
						<div id="myTabContent3" class="tab-content padding-10"><?php

							$counter = 0;

							$dev = SellaciousHelper::getInstance()->config->get('development', 0);

							foreach ($fieldsets as $fs_key => $fieldset)
							{
								if ($visible[$fs_key])
								{
									$fields = $form->getFieldset($fieldset->name); ?>
									<div class="tab-pane fade<?php echo ($counter++) ? '' : ' in active' ?>" id="tab-<?php echo $fs_key ?>">
										<fieldset>
											<?php
											foreach ($fields as $field)
											{
												if ($field->hidden)
												{
													echo $field->input;
												}
												else
												{
													$lbl   = $dev ? " title='$field->id' data-placement='bottom'" : '';
													$clazz = ($field->label && strtolower($field->type) != 'note') ? 'input-row' : '';
													?>
													<div class="hasTooltip row <?php echo $clazz ?>" <?php echo $lbl ?>>
														<?php
														if ($field->label == '' || (isset($fieldset->width) && $fieldset->width == 12))
														{
															echo '<div class="controls col-md-12">' . $field->input . '</div>';
														}
														else
														{
															echo '<div class="form-label col-sm-3 col-md-3 col-lg-2">' . $field->label . '</div>';
															echo '<div class="controls col-sm-9 col-md-9 col-lg-10">' . $field->input . '</div>';
														}
														?>
													</div>
													<div class="clearfix"></div>
													<?php
												}
											}
											?>
										</fieldset>
									</div>
									<?php
								}
							}

							?></div><?php

						// Add (remaining) content for invisible tabs
						foreach ($fieldsets as $fs_key => $fieldset)
						{
							if (!$visible[$fs_key])
							{
								$fields = $form->getFieldset($fieldset->name);

								foreach ($fields as $field)
								{
									echo $field->input;
								}
							}
						}

						?>
						<input type="hidden" name="task" value="" />
						<?php echo JHtml::_('form.token'); ?>
					</div>
					<!-- end widget content -->

				</div>
				<!-- end widget div -->

			</div>
			<!-- end widget -->

		</article>
		<!-- WIDGET END -->
	</form>
</div>

