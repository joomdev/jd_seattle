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

use Joomla\Registry\Registry;

JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/fe.view.downloads.js', false, true);

/** @var  SellaciousViewDownloads $this */
/** @var  stdClass $tplData */
$delivery = new Registry($tplData);

$now    = JFactory::getDate();
$expiry = JFactory::getDate($delivery->get('expiry'));
?>
<div class="w100p tile-box toggle-box">
	<div class="tile-head">
		<table class="w100p">
			<tr>
				<?php if ($delivery->get('order_number')): ?>
				<td class="toggle-element order-number"><strong><?php echo $this->escape($delivery->get('order_number')); ?></strong></td>
				<td class="toggle-element hidden">
					<a href="<?php echo JRoute::_(sprintf('index.php?option=com_sellacious&view=order&id=%d', $delivery->get('order_id'))) ?>">
						<button type="button" class="btn btn-primary btn-lg order-number active">
						<?php echo $this->escape($delivery->get('order_number')); ?></button></a>
				</td>
				<td class="text-right"><strong><?php echo JHtml::_('date', $delivery->get('order_date'), 'D, F d, Y h:i A'); ?></strong></td>
				<?php else: ?>
				<td class="text-right"><strong><?php echo JHtml::_('date', $delivery->get('delivery_date'), 'D, F d, Y h:i A'); ?></strong></td>
				<?php endif; ?>
				<td style="width:30px;">
					<a class="pull-right btn-toggle">
						<i class="fa fa-caret-down fa-lg toggle-element hidden"></i>
						<i class="fa fa-caret-left fa-lg toggle-element "></i>
					</a>
				</td>
			</tr>
		</table>
	</div>

	<div class="tile-body toggle-element hidden">
		<h4 class="strong"><?php echo $delivery->get('product_name'); ?></h4>
		<div class="fieldset">
			<?php
			if ($delivery->get('license_title'))
			{
				?><span class="pull-left"><?php echo JText::sprintf('COM_SELLACIOUS_LICENSE_LABEL_LICENSE_LABEL', $delivery->get('license_title')); ?>&nbsp;
				<a target="_blank" href="<?php echo JRoute::_('index.php?option=com_sellacious&view=license&id=' . $delivery->get('license_id')); ?>">
					<i class="fa fa-external-link"></i></a></span><?php
			}

			$expiry_date = JHtml::_('date', $delivery->get('expiry'), 'D, F d, Y h:i A');
			?>
			<span class="pull-right"><?php echo JText::sprintf('COM_SELLACIOUS_LICENSE_LABEL_LICENSE_EXPIRY', $expiry_date); ?></span>
			<div class="clearfix"></div>
		</div>
		<?php if ($delivery->get('items')): ?>
		<table class="w100p">
			<tbody>
			<?php foreach ($delivery->get('items') as $media): ?>
				<?php if (is_object($media->media)): ?>
				<tr>
					<td>
						<strong><?php echo $this->escape($media->media->original_name ?: $delivery->get('product_name')); ?></strong><br>
						<small><em><?php echo $this->helper->media->getFileType($media->media->original_name, false); ?></em></small>
					</td>
					<td class="center"><?php
						foreach (explode(',', $media->tags) as $tag)
						{
							echo '<label class="label label-success">' . $tag . '</label> ';
						}
					?></td>
					<td class="center"><?php echo JText::sprintf('COM_SELLACIOUS_LICENSE_LABEL_LICENSE_VERSION', $media->version);  ?></td>
					<td class="center"><?php echo $media->released; ?></td>
					<td class="center"><?php echo $media->is_latest ? '<i class="fa fa-fw fa-check-circle-o"></i> ' : ''; ?></td>
					<td class="center"><button type="button" class="btn btn-primary btn-download btn-sm"
						data-delivery="<?php echo $delivery->get('id') ?>"
							data-file="<?php echo $media->media->id ?>"><i class="fa fa-download"></i> </button>
						<div><?php echo isset($media->media->limit) ? JText::sprintf('COM_SELLACIOUS_LICENSE_LABEL_LICENSE_LIMIT', $media->media->limit) : ''; ?></div>
					</td>
				</tr>
				<?php endif; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php else:	?>
			<h5><em><?php echo JText::_('COM_SELLACIOUS_DOWNLOADS_NO_ITEM_MESSAGE'); ?></em></h5>
		<?php endif; ?>
	</div>
</div>
