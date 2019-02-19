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

/** @var \SellaciousViewStores $this */
$app = JFactory::getApplication();

// Load the behaviors.
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.stores.css', null, true);
?>
<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=stores'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="stores-page" class="w100p">
			<?php
			if (count($this->items) == 0)
			{
				?><h4><?php echo JText::_('COM_SELLACIOUS_STORES_NO_MATCH') ?></h4><?php
			}

			foreach ($this->items as $item)
			{
				echo $this->loadTemplate('block', $item);
			}
			?>
			<div class="clearfix"></div>
			<?php
			/** @var JPagination $pagination */
			$pagination = $this->pagination;
			?>

	</div>
	<div class="clearfix"></div>
	<div class="left pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
	<div class="center"><br><?php echo $pagination->getPagesCounter(); ?></div>
	<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
</form>
