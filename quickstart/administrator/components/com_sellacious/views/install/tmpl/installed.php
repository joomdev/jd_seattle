<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('stylesheet', 'com_sellacious/installer.install.css', false, true);

if ($this->version)
{
	$doc = JFactory::getDocument();
	$doc->addScript('//www.sellacious.com/release/sample-data/v' . $this->version . '/info.js');
}

$app     = JFactory::getApplication();
$message = $app->getUserState('com_installer.extension_message', '');

$app->setUserState('com_installer.redirect_url', '');
$app->setUserState('com_installer.message', '');
$app->setUserState('com_installer.extension_message', '');

JText::script('COM_SELLACIOUS_INSTALL_INSTALLATION_CONFIRM_RESET');
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task, form) {
		if (task === 'reset' && !confirm(Joomla.JText._('COM_SELLACIOUS_INSTALL_INSTALLATION_CONFIRM_RESET'))) return false;
		Joomla.submitform(task, form);
	};
</script>
<div class="row">
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious') ?>" method="post" name="adminForm" id="adminForm">
		<p><?php echo $message ?></p>
		<div class="span12">
			<div class="alert alert-success center">
				<?php $url = JUri::root() . 'sellacious' ?>
				<?php echo JText::sprintf('COM_SELLACIOUS_INSTALL_BACKOFFICE_LOGIN_NOTE', $url) ?><br><br>
				<button type="button" class="btn btn-primary btn-large strong" onclick="Joomla.submitbutton('', this.form);"><i
						class="icon-out-2"></i> <?php echo JText::_('COM_SELLACIOUS_INSTALL_BACKOFFICE_LAUNCH_BUTTON') ?></button>
				<input type="hidden" name="redirect" value="1"/>
				<label for="auto_redirect" style="margin-top: 10px;">
					<input type="checkbox" name="auto_redirect" id="auto_redirect" style="margin-top: -2px;" value="1"/>
					<?php echo JText::_('COM_SELLACIOUS_INSTALL_BACKOFFICE_AUTO_REDIRECT_CHECK_LABEL'); ?>
				</label>
			</div>
		</div>

		<?php if (JFactory::getUser()->authorise('core.admin')): ?>
			<br>
			<div class="span12">
				<div class="alert alert-info center">
					<?php echo JText::_('COM_SELLACIOUS_INSTALL_INSTALLATION_RESET_NOTE') ?>
					<div class="input-group" id="sample-data-install">
						<ul>
							<li><label><input type="radio" name="sample_data" value="" checked>
									<span> <?php echo JText::_('COM_SELLACIOUS_INSTALL_RESET_BLANK') ?></span></label></li>
						</ul>
						<button type="button" class="btn btn-default btn-medium input-group-addon"
						        onclick="Joomla.submitbutton('reset', this.form);"><i
								class="icon-loop"></i> <?php echo JText::_('COM_SELLACIOUS_INSTALL_RESET_BUTTON') ?></button>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
