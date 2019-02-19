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

$doc = JFactory::getDocument();
$doc->addStyleDeclaration('.search-in { width: 140px; }');

foreach ($this->items as $i => $item)
{
	$seller_uid = isset($item->seller_uid) ? $item->seller_uid : 0;
	$code       = $this->helper->product->getCode($item->product_id, $item->variant_id, $seller_uid);
	?>
	<tr role="row">
		<td><?php echo $this->escape($item->product_title); ?></td>

		<?php if (isset($item->seller_uid)): ?>
			<td><?php echo $this->escape($item->seller_company); ?></td>
			<td class="nowrap center"><?php echo $this->escape($item->item_uid); ?></td>
		<?php else: ?>
			<td class="nowrap center"><?php echo $this->escape($item->product_id); ?></td>
			<td class="nowrap center"><?php echo $this->escape($item->variant_id); ?></td>
		<?php endif; ?>

		<td class="nowrap center">
			<a href="<?php echo 'index.php?option=com_sellacious&view=downloads&p=' . $code; ?>"
			   class="strong"><?php echo (int) $item->dl_count; ?> <i class="fa fa-external-link"></i></a>
		</td>
	</tr>
	<?php
}
