<?php

JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');

$this->document->addScript(JUri::base(true) . '/templates/sellacious/js/plugin/fuelux/wizard/wizard.js');
$this->document->addScript(JUri::root(true) . '/media/com_sellacious/js/plugin/select2/select2.min.js');

JHtml::_('stylesheet', 'com_sellacious/view.setup.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.setup.js', array('version' => S_VERSION_CORE, 'relative' => true));

$fieldsets = array_values($this->form->getFieldsets());
?>
<div class="well well-light padding-20">
<div class="center"><img src="templates/sellacious/images/sellacious-logo-large.png" alt="" width="180px"></div>

<div class="row">
	<article class="hidden-xs col-sm-1 col-md-2 col-lg-3"></article>
	<article class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
		<div class="jarviswidget">
			<header>
				<span class="widget-icon"> <i class="fa fa-cogs"></i> </span>
				<h2><?php echo JText::_('COM_SELLACIOUS_TITLE_SETUP') ?></h2>
			</header>
			<div>
				<div class="widget-body fuelux">
					<div class="wizard">
						<ul class="steps">
							<?php foreach ($fieldsets as $i => $fieldset): ?>
							<?php $fields = $this->form->getFieldset($fieldset->name); ?>
							<?php if (count($fields)): ?>
							<li data-target="#<?php echo $fieldset->name ?>" <?php echo $i ? '' : 'class="active"'; ?>>
								<span class="step-counter"> </span><?php echo JText::_($fieldset->label) ?><span class="chevron"></span>
							</li>
							<?php endif; ?>
							<?php endforeach; ?>
						</ul>
						<div class="actions">
							<button type="button" class="btn btn-sm btn-primary btn-prev">
								<i class="fa fa-arrow-left"></i><?php echo JText::_('COM_SELLACIOUS_SETUP_ACTION_PREVIOUS') ?>
							</button>
							<button type="button" class="btn btn-sm btn-success btn-next" data-last="Finish">
								<?php echo JText::_('COM_SELLACIOUS_SETUP_ACTION_NEXT') ?><i class="fa fa-arrow-right"></i>
							</button>
						</div>
					</div>
					<?php reset($fieldsets); ?>
					<div class="step-content">
						<form action="index.php?option=com_sellacious&view=setup" class="form-horizontal" id="fuelux-wizard" method="post">

							<?php foreach ($fieldsets as $i => $fieldset): ?>
							<?php $fields = $this->form->getFieldset($fieldset->name); ?>
							<?php if (count($fields)): ?>
							<div class="step-pane <?php echo $i ? '' : 'active' ?>" id="<?php echo $fieldset->name ?>">

								<br>
								<div class="text-center">
									<h4><strong><?php echo JText::_($fieldset->label) ?></strong></h4>
									<p><?php echo JText::_($fieldset->description) ?></p>
								</div>

								<!-- wizard form starts here -->
								<fieldset>

									<?php foreach ($fields as $field): ?>

										<div class="form-group">
											<?php echo $field->label; ?>
											<div class="col-md-9">
												<div class="input-group w100p">
													<?php echo $field->input; ?>
												</div>
											</div>
										</div>

									<?php endforeach; ?>

								</fieldset>

							</div>
							<?php endif; ?>
							<?php endforeach; ?>

							<input type="hidden" name="task">
							<?php echo JHtml::_('form.token'); ?>

						</form>
					</div>
					<div class="no-nav hidden setup-spinner setup-overlay"></div>
					<div class="no-nav hidden setup-spinner center">
						<i class="fa fa-spinner fa-pulse fa-4x"></i>
						<h5 class="no-trial"><?php echo JText::_('COM_SELLACIOUS_SETUP_WAIT_SPINNER_LABEL') ?></h5>
						<h5 class="with-trial"><?php echo JText::_('COM_SELLACIOUS_SETUP_WAIT_TRIAL_SPINNER_LABEL') ?></h5>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</article>
	<article class="hidden-xs col-sm-1 col-md-2 col-lg-3"></article>
</div>
</div>
