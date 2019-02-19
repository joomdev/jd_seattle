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

/** @var   SellaciousViewMessages  $this */
$me        = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

JHtml::_('script', 'com_sellacious/view.ratings.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.ratings.css', array('version' => S_VERSION_CORE, 'relative' => true));

foreach ($this->items as $i => $item)
{
	$canChange   = $this->helper->access->check('rating.edit.state', $item->id);
	$code        = $this->helper->product->getCode($item->product_id, $item->variant_id, $item->seller_uid);
	$product_url = JRoute::_('../index.php?option=com_sellacious&view=product&p=' . $code);
	$image_url   = $this->helper->product->getImage($item->product_id, null, true);
	?>
	<tr>
		<td class="nowrap center hidden-phone">
			<label><input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
						  value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canChange) ? '' : ' disabled="disabled"' ?>/>
				<span></span></label>
		</td>
		<td class="nowrap center">
			<span class="btn-round">
				<?php echo JHtml::_('jgrid.published', $item->state, $i, 'ratings.', $canChange); ?></span>
		</td>
		<td style="width:50px; padding:1px;" class="image-box">
			<img class="image-large" src="<?php echo $image_url; ?>"/>
			<img class="image-small" src="<?php echo $image_url; ?>"/>
		</td>
		<td><?php
			$link = ' <a class="btn btn-xs btn-primary" target="_blank" href="' . $product_url . '"><i class="fa fa-external-link"></i></a> ';
			echo JHtml::link($product_url, $link, ' target="_blank"');
			echo $this->escape($item->product_title);
		?></td>
		<td><?php echo $this->escape($item->seller_company); ?></td>
		<td class="nowrap"><?php echo $this->escape(ucwords($item->type)); ?></td>
		<td class="nowrap"><?php echo $this->escape(ucwords($item->author_name)); ?></td>
		<td class="nowrap"><?php echo $stars = $this->helper->core->getStars($item->rating, true); ?></td>
		<td>
			<?php echo $this->escape($item->title); ?>
			<?php $comment = trim($item->comment); ?>
			<?php if (strlen($comment)): ?>
				<a href="#" class="strong show-modal">(detail)</a>
				<div class="static-modal hidden">
					<a href="#" class="pull-right hide-modal hasTooltip" data-placement="left"
					   title="Press <kbd>escape</kbd> or click to close."><i class="fa fa-times"></i></a>
					<h4 class="no-margin"><strong><?php echo $this->escape(ucwords($item->author_name)); ?></strong> –
						<?php echo $stars ?></h4>
					<hr class="standard">
					<h3 class="margin-bottom-5"><strong><?php echo trim($item->title) ?></strong></h3>
					<p style="white-space: normal"><?php echo $comment; ?></p>
				</div>
			<?php endif; ?>
		</td>
		<td class="nowrap">
			<?php echo JHtml::_('date', $item->created, 'M dS, Y'); ?>
			<small style="opacity:0.8;"><?php echo JHtml::_('date', $item->created, 'h:i A'); ?></small>
		</td>
		<td class="nowrap"><?php echo (int) $item->id; ?></td>
	</tr>
	<?php
}
