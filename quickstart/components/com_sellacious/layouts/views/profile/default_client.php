<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

/** @var  \SellaciousHelper  $helper */
/** @var  \Joomla\Registry\Registry  $registry */
$helper   = $this->helper;
$registry = $this->registry;

$msgUndefined = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');
?>
<fieldset class="w100p users_profile_client form-horizontal">
	<legend>
		<?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELDSET_CLIENT'); ?>
	</legend>
	<?php if ($this->getShowOption('client.client_type')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_CLIENT_TYPE_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php
			$catTypes = $helper->client->getTypes();
			$catType  = $helper->core->getArrayField($catTypes, 'value', $registry->get('client.client_type', 'individual'), 'text');

			echo $catType ?: $msgUndefined;
			?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('client.business_name')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BUSINESS_NAME_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('client.business_name') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('client.org_reg_no')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_ORG_REG_NO_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('client.org_reg_no') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('client.org_certificate')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_ORG_CERTIFICATE_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php
			$filter       = array(
				'list.select' => 'a.id, a.path, a.original_name, a.doc_type, a.doc_reference',
				'table_name'  => 'clients',
				'context'     => 'org_certificate',
				'record_id'   => $registry->get('client.id'),
				'state'       => 1,
			);
			$certificates = (array) $helper->media->loadObjectList($filter);
			$images       = array();
			$files        = array();

			foreach ($certificates as $certificate)
			{
				$imgUrl = $helper->media->getURL($certificate->path, false);

				if ($imgUrl)
				{
					if ($helper->media->isImage($certificate->path))
					{
						$images[] = '<img src="' . $imgUrl . '"/>';
					}
					else
					{
						$dLink   = JRoute::_(JUri::base(true) . '/index.php?option=com_sellacious&task=media.download&id=' . $certificate->id);
						$files[] = '<a href="' . $dLink . '">' . $certificate->original_name . '</a>';
					}
				}
			}
			?>

			<?php if ($images || $files): ?>
				<?php if ($images): ?>
				<ul class="media-list media-list-image">
					<?php foreach ($images as $image): ?>
						<li><?php echo $image ?></li>
					<?php endforeach; ?>
				</ul>
				<div class="clearfix"></div>
				<?php endif; ?>
				<?php if ($files): ?>
				<ul class="media-list media-list-generic pull-left">
					<?php foreach ($files as $cFile): ?>
						<li><i class="fa fa-files-o"></i> <?php echo $cFile ?></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $msgUndefined; ?>
			<?php endif ?>

		</div>
	</div>
	<?php endif; ?>
</fieldset>
