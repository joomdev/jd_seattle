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

/** @var SellaciousViewUser $this */

JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.loadCss');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

JHtml::_('script', 'com_sellacious/util.validator-mobile.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.profile.css', null, true);

$fieldsets = $this->form->getFieldsets();
$accordion = array('parent' => true, 'toggle' => false, 'active' => 'profile_accordion_basic');

echo JHtml::_('bootstrap.startAccordion', 'profile_accordion', $accordion);
?>
<form action="<?php echo JUri::getInstance()->toString(); ?>"
	method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" enctype="multipart/form-data">

	<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=addresses'); ?>"
	   class="btn btn-primary pull-right"><?php echo JText::_('COM_SELLACIOUS_ADDRESSES_MANAGE_LABEL') ?></a>
	<div class="clearfix"></div>
	<br>

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
	<br>
	<div class="control-group captcha-input">
		<div class="controls col-md-12"><?php echo $this->form->getInput('captcha'); ?></div>
	</div>
	<div class="clearfix"></div>

	<br>
	<button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> <?php
		echo strtoupper(JText::_('COM_SELLACIOUS_SAVE')); ?></button>

	<input type="hidden" name="task" value="profile.save"/>
	<?php echo JHtml::_('form.token'); ?>

</form>

<?php echo JHtml::_('bootstrap.endAccordion'); ?>
<div class="clearfix"></div>
