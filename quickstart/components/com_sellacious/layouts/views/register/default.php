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

/** @var  SellaciousViewUser $this */
JHtml::_('behavior.formvalidator');
JHtml::_('jquery.framework');
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');

JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);
JHtml::_('script', 'com_sellacious/util.validator-mobile.js', false, true);

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

JHtml::_('script', 'com_sellacious/fe.view.profile.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.profile.css', null, true);
?>
<script>
	Joomla.submitbutton = function(task, form) {
		if (document.formvalidator.isValid(document.getElementById('register-form'))) {
			Joomla.submitform(task, form);
		}
	}
</script>
<?php
$fieldsets = $this->form->getFieldsets();
$accordion = array('parent' => true, 'toggle' => false, 'active' => 'profile_accordion_basic');

echo JHtml::_('bootstrap.startAccordion', 'profile_accordion', $accordion);
?>
<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=register&catid=' . $this->state->get('seller.catid')); ?>"
	method="post" id="register-form" name="register-form" class="form-validate form-horizontal">

	<?php
	// Get a list of configured segments
	$segments = $this->helper->config->get('profile_fieldset_order');

	// Display configured segments
	if (is_array($segments))
	{
		foreach ($segments as $segment)
		{
			// The captcha segment is not listed so we won't need to check for it here to skip
			if (!empty($fieldsets[$segment]))
			{
				try
				{
					echo $this->loadTemplate('fieldset', $fieldsets[$segment]);
				}
				catch (Exception $e)
				{
				}

				unset($fieldsets[$segment]);
			}
			// There are multiple custom fieldsets with names like: fs_12, fs_103
			elseif ($segment == 'custom')
			{
				foreach (array_keys($fieldsets) as $key)
				{
					if (preg_match('/^fs_\d+$/i', $key))
					{
						try
						{
							echo $this->loadTemplate('fieldset', $fieldsets[$key]);
						}
						catch (Exception $e)
						{
						}

						unset($fieldsets[$key]);
					}
				}
			}
		}
	}

	// Display remaining segments except captcha
	foreach (array_keys($fieldsets) as $key)
	{
		if ($key != 'captcha')
		{
			try
			{
				echo $this->loadTemplate('fieldset', $fieldsets[$key]);
			}
			catch (Exception $e)
			{
			}

			unset($fieldsets[$key]);
		}
	}
	?>

	<div class="clearfix"></div>
	<br>

	<fieldset class="w100p captcha-fieldset">
		<?php
		$fields = $this->form->getFieldset('captcha');

		foreach ($fields as $field):
			if ($field->hidden):
				echo $field->input;
			else:
				?>
				<div class="control-group">
					<?php if ($field->label): ?>
						<div class="control-label"><?php echo $field->label ?></div>
						<div class="controls"><?php echo $field->input ?></div>
					<?php else: ?>
						<div class="controls col-md-12"><?php echo $field->input ?></div>
					<?php endif; ?>
				</div>
			<?php
			endif;
		endforeach;
		?>
	</fieldset>

	<div class="clearfix"></div>
	<br>

	<fieldset>
		<div class="control-group">
			<div class="controls text-right">
				<button type="button" class="btn btn-default"
						onclick="return Joomla.submitbutton('register.save', this.form);"><?php echo JText::_('JSUBMIT') ?></button>
			</div>
		</div>
	</fieldset>

	<input type="hidden" name="task"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php echo JHtml::_('bootstrap.endAccordion'); ?>
<div class="clearfix"></div>
