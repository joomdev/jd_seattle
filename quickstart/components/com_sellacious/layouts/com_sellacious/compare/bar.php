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

/** @var   JLayoutFile $this */
/** @var   \Sellacious\Product[] $displayData */
$items = $displayData;
$codes = array();

if (is_array($items) && array_filter($items))
{
	?>
	<div class="w100p">
		<table class="tbl-compare">
			<tbody>
			<tr>
				<?php foreach ($items as $item) : ?>
					<?php
					if (is_object($item))
					{
						$layoutId = 'bar_item';
						$codes[]  = $item->getCode();
					}
					else
					{
						$layoutId = 'bar_noitem';
					}
					?>
					<td style="width: <?php echo 900 / count($items) ?>px;"
						class="<?php echo $layoutId ?>"><?php echo $this->sublayout($layoutId, $item); ?></td>
				<?php endforeach; ?>
				<td class="compare-submit"><?php
					if (count($items) >= 2):
						?><a class="btn btn-success" href="<?php
						echo JRoute::_('index.php?option=com_sellacious&view=compare&c=') . implode(',', $codes); ?>"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_COMPARE')); ?></a><?php
					else:
						?><a href="#" class="btn btn-success disabled"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_COMPARE')); ?></a><?php
					endif;
				?></td>
			</tr>
			</tbody>
		</table>
	</div>
	<?php
}
else
{
	echo '<div class="hidden w100p"></div>';
}
