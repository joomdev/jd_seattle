<?php
/**
 *  
 * @package    Com_Jdprofiler
 * @author      Joomdev
 * @copyright  Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'media/com_jdprofiler/css/form.css');
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {
		
	});

	Joomla.submitbutton = function (task) {
		if (task == 'profile.cancel') {
			Joomla.submitform(task, document.getElementById('profile-form'));
		}
		else {
			
			if (task != 'profile.cancel' && document.formvalidator.isValid(document.id('profile-form'))) {
				
				Joomla.submitform(task, document.getElementById('profile-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_jdprofiler&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="profile-form" class="form-validate">

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_JDPROFILER_TITLE_PROFILE', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">

				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<?php echo $this->form->renderField('note1'); ?>
				<?php echo $this->form->renderField('name'); ?>
				<?php echo $this->form->renderField('alias'); ?>
				<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
				<?php echo $this->form->renderField('image'); ?>
				<?php echo $this->form->renderField('designation'); ?>
				<?php echo $this->form->renderField('sbio'); ?>
				<?php echo $this->form->renderField('team'); ?>

				<?php echo $this->form->renderField('note2'); ?>
				<?php echo $this->form->renderField('lbio'); ?>
				<?php echo $this->form->renderField('email'); ?>
				<?php echo $this->form->renderField('phone'); ?>
				<?php echo $this->form->renderField('location'); ?>
		
				 
				<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
				<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
				<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />

				<?php echo $this->form->renderField('created_by'); ?>
				<?php echo $this->form->renderField('modified_by'); ?>				<input type="hidden" name="jform[created_on]" value="<?php echo $this->item->created_on; ?>" />
				<input type="hidden" name="jform[modified_on]" value="<?php echo $this->item->modified_on; ?>" />


					<?php if ($this->state->params->get('save_history', 1)) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
					</div>
					<?php endif; ?>
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'social', JText::_('COM_JDPROFILER_SOCIAL_PROFILE', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">
				<?php echo $this->form->renderField('social'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
