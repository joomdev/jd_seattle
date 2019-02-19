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

/** @var stdClass[] $items */
JFactory::getLanguage()->load('mod_smartymenu', JPATH_SITE . '/'. JPATH_SELLACIOUS_DIR . '/modules/mod_smartymenu');
JFactory::getLanguage()->load('mod_smartymenu', JPATH_SITE . '/'. JPATH_SELLACIOUS_DIR);

?>
<ol class="breadcrumb breadcrumb-<?php echo $moduleclass_sfx; ?>">
	<?php
	/** @var \Joomla\Registry\Registry $params */
	if ($params->get('showHere', 1))
	{
		echo '<li><i class="fa fa-home"></i> &#160;</li>';
	}

	// Generate the trail
	foreach ($list as $item)
	{
		echo '<li>' . JText::_($item->name) . '</li>';
	}
	?>
</ol>
