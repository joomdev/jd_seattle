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

/** @var  SellaciousViewDownloads $this */
$licenses = $this->helper->license->loadObjectList(array('list.select' => 'a.id, a.title'), 'id');
$licenses = $this->helper->core->arrayAssoc($licenses, 'id', 'title');

JFactory::getDocument()->addStyleDeclaration('.search-in { width: 140px; }');

foreach ($this->items as $i => $item)
{
	$link = 'index.php?option=com_sellacious&view=downloads&f=' . (int) $item->file_id;
	?>
	<tr role="row">
		<td><?php echo $this->escape($item->file_name); ?></td>
		<td><?php echo $this->escape($item->seller_company); ?></td>
		<td class="nowrap center"><?php echo $this->escape($item->item_uid); ?></td>
		<td><?php echo $this->escape($item->product_title); ?></td>
		<td class="nowrap center">
			<a href="<?php echo $link; ?>" class="strong"><?php
				echo (int) $item->dl_count; ?> <i class="fa fa-external-link"></i></a>
		</td>
		<td class="nowrap center" width="1%"><?php echo $this->escape($item->file_id); ?></td>
	</tr>
	<?php
}
