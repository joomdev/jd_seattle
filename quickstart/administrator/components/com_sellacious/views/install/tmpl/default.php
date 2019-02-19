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
?>
<div class="row">
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious') ?>" method="post">
		<div class="center"><button class="btn btn-primary btn-large"><?php echo JText::_('COM_SELLACIOUS_INSTALL_DOWNLOAD_INASTALL_BUTTON') ?></button></div>
		<?php if ($this->version): ?>
			<br>
			<p class="center" style="color: #000;"><?php echo JText::sprintf('COM_SELLACIOUS_INSTALL_DOWNLOAD_INASTALL_NOTE', $this->version) ?></p>
		<?php endif; ?>
		<br>
		<p class="center" style="color: #f00;"><?php
			echo JText::_('COM_SELLACIOUS_INSTALL_AUTO_AGREE_SELLACIOUS_TERMS_NOTE') ?></p>
		<input type="hidden" name="task" value="install">
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
