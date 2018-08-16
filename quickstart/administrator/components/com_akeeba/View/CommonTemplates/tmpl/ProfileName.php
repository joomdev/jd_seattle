<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();
?>
<div class="akeeba-block--info">
	<strong><?php echo \JText::_('COM_AKEEBA_CPANEL_PROFILE_TITLE'); ?></strong>:
	#<?php echo $this->escape($this->profileid); ?> <?php echo $this->escape($this->profilename); ?>

</div>
