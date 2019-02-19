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

/** @var  \Sellacious\Product  $displayData */
$item   = $displayData;
$url    = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->getCode());
$images = $item->getImages(true, true);
?><table class="w100p">
	<tr>
		<td class="compare-item-image"><img src="<?php echo reset($images) ?>"
					title="<?php echo htmlspecialchars($item->get('title')) ?>"/></td>
		<td class="compare-item-title"><a href="<?php echo $url ?>"><?php
					echo htmlspecialchars($item->get('title') . ' ' . $item->get('variant_title')) ?></a></td>
		<td style="width:12px;"><a
					class="compare-remove" data-item="<?php echo $item->getCode() ?>"><i class="fa fa-times-circle"></i></a></td>
	</tr>
</table><?php



