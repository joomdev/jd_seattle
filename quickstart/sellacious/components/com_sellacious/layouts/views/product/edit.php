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

/** @var  SellaciousViewProduct $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

JHtml::_('script', 'media/com_sellacious/js/plugin/select2/select2.min.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'media/com_sellacious/js/plugin/cookie/jquery.cookie.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/util.tabstate.js', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

JHtml::_('script', 'com_sellacious/view.product.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.product.css', array('version' => S_VERSION_CORE, 'relative' => true));

$me        = JFactory::getUser();
$sellerUid = $this->form->getValue('seller_uid');

$defLanguage = JFactory::getLanguage();
$tag         = $defLanguage->getTag();
$languages   = JLanguageHelper::getContentLanguages();

$languages = array_filter($languages, function ($item) use ($tag){
	return ($item->lang_code != $tag);
});
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('select[id^="filter_"]').each(function () {
			var w = 0;
			$(this).find('option').each(function () {
				var wt = $(this).text().length;
				w = wt > w ? wt : w;
			});

			$(this).css('max-width', '100%');
			$(this).css('width', (Math.min(w, 1000) + 10) + 'ch');
		});
		$('select').select2();

		var o = new SellaciousViewProduct.Related;
		o.init('#jform_related_groups', '<?php echo JSession::getFormToken() ?>');
	});

	Joomla.submitbutton = function (task) {
		var form = document.getElementById('adminForm');
		var task2 = task.split('.')[1] || '';
		if (/set.*/.test(task2) || task2 === 'cancel' || document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		} else {
			alert(Joomla.JText._('COM_SELLACIOUS_VALIDATION_FORM_FAILED', 'Invalid or incomplete form.'));
		}
	}
</script>
<div class="row">

	<form action="<?php echo JUri::getInstance()->toString(); ?>"
		method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" enctype="multipart/form-data">

		<!-- NEW WIDGET START -->
		<article class="col-sm-12 col-md-12 col-lg-12">

			<?php
			$multiSeller = $this->helper->config->get('multi_seller');
			$isStaff     = $this->helper->access->checkAny(array('seller', 'pricing', 'shipping'), 'product.edit.');
			$sellerRef   = $multiSeller && $isStaff && $sellerUid > 0;
			$langRef     = count($languages) && $this->form->getField('language');
			?>

			<?php if ($langRef || $sellerRef): ?>
			<div class="barhead bordered bg-color-lighten">
				<div class="row">
					<?php if ($sellerRef): ?>
						<div class="<?php echo ($langRef) ? 'col-sm-7' : 'col-sm-12' ?>">
							<div class="notifierhead"><?php
								$sName = JFactory::getUser($sellerUid)->name;
								echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EDIT_NOTE_CURRENT_SELLER', $sName ?: JText::_('COM_SELLACIOUS_SELLER_NONAME')); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ($langRef): ?>
						<div class="<?php echo ($sellerRef) ? 'col-sm-5' : 'col-sm-12'?>">
							<div class="lang-dropdown">
								<div class="pull-left form-label margin-right-5"><?php
									echo $this->form->getLabel('language'); ?></div>
								<?php echo $this->form->getInput('language'); ?>
							</div>
						</div>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
			</div>
			<?php endif; ?>

			<!-- Widget ID (each widget will need unique ID)-->
			<div class="tabsedit-container-off">

				<!-- widget content -->
				<div class="widget-body edittabs">
					<?php
					$fieldsets = $this->form->getFieldsets();
					$visible   = array();

					// Find visible tabs
					foreach ($fieldsets as $fs_key => $fieldset)
					{
						$fields = $this->form->getFieldset($fieldset->name);

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
					<div class="tab-menu-head">
						<a class="tabbar-toggler" href="javascript:void(0);">
							<span class="active-tab"><?php echo JText::_('COM_SELLACIOUS_TAB_BAR_LABEL'); ?></span>
							<div class="hamburger">
								<span></span>
								<span></span>
								<span></span>
							</div>
						</a>
					</div>
					<ul id="myTab3" class="nav nav-tabs tabs-pull-left bordered">
						<?php
						$this->counter = 0;

						foreach ($fieldsets as $fs_key => $fieldset)
						{
							if ($visible[$fs_key])
							{
								$css = (isset($fieldset->align) && $fieldset->align == 'right') ? 'pull-right' : '';
								?>
								<li class="<?php echo ($this->counter++) ? '' : ' active' ?> <?php echo $css ?>">
									<a href="#tab-<?php echo $fs_key; ?>" data-toggle="tab">
										<?php echo JText::_($fieldset->label, true) ?>
									</a>
								</li>
								<?php
							}
						}
						?>
					</ul>
					<div id="myTabContent3" class="tab-content padding-10">
					<?php
						$this->counter = 0;

						foreach ($fieldsets as $fs_key => $fieldset)
						{
							if ($visible[$fs_key])
							{
								// Special treatment for some tabs
								if ($fieldset->name == 'basic')
								{
									echo $this->loadTemplate('basic');
								}
								elseif ($fieldset->name == 'variants')
								{
									echo $this->loadTemplate('variants');
								}
								else
								{
									$fields = $this->form->getFieldset($fieldset->name);
									?>
									<div class="tab-pane fade" id="tab-<?php echo $fs_key; ?>">
										<fieldset>
											<?php
											foreach ($fields as $field)
											{
												if ($field->getAttribute('name') == 'language'):
													continue;
												endif;

												if ($field->hidden):
													echo $field->input;
												else:
													?>
													<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
														<?php
														if ($field->label && (!isset($fieldset->width) || $fieldset->width < 12))
														{
															echo '<div class="form-label col-sm-3 col-md-3 col-lg-2">' . $field->label . '</div>';
															echo '<div class="controls col-sm-9 col-md-9 col-lg-10">' . $field->input . '</div>';
														}
														else
														{
															echo '<div class="controls col-md-12">' . $field->input . '</div>';
														}
														?>
													</div>
													<?php
												endif;
											}
											?>
										</fieldset>
									</div>
									<?php
								}
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
							$fields = $this->form->getFieldset($fieldset->name);

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
