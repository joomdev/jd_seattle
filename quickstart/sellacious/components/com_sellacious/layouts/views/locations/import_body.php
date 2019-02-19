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

/** @var  SellaciousViewLocations  $this */

JHtml::_('stylesheet', 'com_sellacious/country-flags.css', array('version' => S_VERSION_CORE, 'relative' => true));

foreach ($this->items as $i => $item)
{
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<label><input type="checkbox" name="cid[]" class="checkbox style-0" id="cb<?php echo $i ?>"
			              value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"/>
				<span></span></label>
		</td>
		<td class="nowrap center">
			<?php $img = $this->helper->media->getURL('media/com_sellacious/images/flag-xs/' . strtolower($item->iso_code) . '.png', false); ?>
			<?php if ($img): ?><img src="<?php echo $img ?>" alt="" style="height: 15px;"/><?php endif; ?>
		</td>
		<td class="nowrap center">
			<?php echo $this->escape($item->iso_code); ?>
		</td>
		<td class="nowrap">
			<?php echo $this->escape($item->title); ?>
		</td>
		<td class="nowrap center">
			<label class="label label-success"><?php echo (int) $item->import ?></label>
		</td>
		<td class="nowrap center">
			<label class="label <?php echo $item->current ? 'label-danger' : 'label-default' ?>"><?php echo (int) $item->current ?></label>
		</td>
		<td class="center hidden-phone">
			<?php echo (int) $item->id; ?>
		</td>
	</tr>
<?php
}
