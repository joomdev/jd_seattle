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
JHtml::_('script', 'media/com_sellacious/js/plugin/cookie/jquery.cookie.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/util.tabstate.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/util.number_format.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/util.float-val.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.productlisting.js', array('version' => S_VERSION_CORE, 'relative' => true));

JHtml::_('stylesheet', 'com_sellacious/view.productlisting.css', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

/** @var JForm $form */
$form = $this->form;
?>
<script type="text/javascript">
jQuery(document).ready(function ($) {
	var $select = $('select');
	$select.css('width', '100%');
	$select.select2();
});
</script>
<div class="row">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post"
		name="adminForm" id="adminForm" class="form-validate form-horizontal">

		<!-- New widget start -->
		<article class="col-sm-12 col-md-12 col-lg-12">

			<!-- Widget ID (each widget will need unique ID)-->
			<div class="tabsedit-container-off">

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
								<li class="<?php echo $class ?>">
									<a href="#tab-<?php echo $fs_key; ?>" data-toggle="tab">
										<?php echo JText::_($fieldset->label, true) ?>
									</a>
								</li>
								<?php
								$counter++;
							}
						}
						?>
					</ul>

					<?php // Add content for visible tabs ?>

					<div id="myTabContent3" class="tab-content padding-10"><?php

						$counter = 0;

						foreach ($fieldsets as $fs_key => $fieldset)
						{
							if ($visible[$fs_key])
							{
								$fields = $form->getFieldset($fieldset->name); ?>
								<div class="tab-pane fade <?php echo $counter++ ? '' : 'in active' ?>" id="tab-<?php echo $fs_key; ?>">
									<fieldset>
										<div class="row padding-5">
											<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
												<?php
												$field = $form->getField('seller_uid');

												if ($field)
												{
													echo '<div class="row padding-5">';
													echo '<div class="form-label col-sm-6 col-md-6 col-lg-4">' . $field->label . '</div>';
													echo '<div class="controls col-sm-6 col-md-6 col-lg-8">' . $field->input . '</div>';
													echo '</div>';
												}

												$field = $form->getField('category_id');

												if ($field)
												{
													echo '<div class="row padding-5">';
													echo '<div class="form-label col-sm-6 col-md-6 col-lg-4">' . $field->label . '</div>';
													echo '<div class="controls col-sm-6 col-md-6 col-lg-8">' . $field->input . '</div>';
													echo '</div>';
												}
												?>
											</div>
											<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
												<?php echo $this->loadTemplate('wallet'); ?>
											</div>
										</div>
										<?php
										foreach ($fields as $field)
										{
											if ($fs_key == 'basic' && ($field->fieldname == 'seller_uid' || $field->fieldname == 'category_id'))
											{
												// nothing
											}
											elseif ($field->hidden)
											{
												echo $field->input;
											}
											elseif ($field->label || $field->input)
											{
												?>
												<div class="row padding-5">
													<?php
													if ($field->label && $field->input && (!isset($fieldset->width) || $fieldset->width < 12))
													{
														echo '<div class="form-label col-sm-3 col-md-3 col-lg-2">' . $field->label . '</div>';
														echo '<div class="controls col-sm-9 col-md-9 col-lg-10">' . $field->input . '</div>';
													}
													elseif ($field->label)
													{
														echo '<div class="controls col-md-12">' . $field->label . '</div>';
													}
													elseif ($field->input)
													{
														echo '<div class="controls col-md-12">' . $field->input . '</div>';
													}
													?>
												</div>
												<?php
											}
										}

										if ($fs_key == 'basic')
										{
											echo $this->loadTemplate('items');
										}
										?>
									</fieldset>
								</div>
								<?php
							}
						}
						?>
					</div>
					<?php
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
			<!-- end widget -->

		</article>
		<!-- WIDGET END -->

	</form>
</div>

