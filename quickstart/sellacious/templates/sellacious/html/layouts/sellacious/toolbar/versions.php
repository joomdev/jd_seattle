<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

JHtml::_('behavior.framework');

?>
<a rel="{handler: 'iframe', size: {x: <?php echo $displayData['height']; ?>, y: <?php echo $displayData['width']; ?>}}"
	href="index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;item_id=<?php echo (int) $displayData['itemId']; ?>&amp;type_id=<?php echo $displayData['typeId']; ?>&amp;type_alias=<?php echo $displayData['typeAlias']; ?>&amp;<?php echo JSession::getFormToken(); ?>=1"
	title="<?php echo $displayData['title']; ?>" class="btn btn-small modal_jform_contenthistory">
	<span class="icon-archive" aria-hidden="true"></span> <?php echo $displayData['title']; ?>
</a>
