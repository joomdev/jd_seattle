<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;
?>
<div id="import-images" class="import-tab">
	<?php
	$template = new stdClass;

	$template->id          = 0;
	$template->import_type = 'images';
	$template->title       = JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_IMAGES_TITLE');
	$template->alias       = JApplicationHelper::stringURLSafe($template->title);
	$template->mapping     = array();
	$template->override    = 1;

	echo $this->renderLayout('section_main', $template, 'default');
	?>
</div>
