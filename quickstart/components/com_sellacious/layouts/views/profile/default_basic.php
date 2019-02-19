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

/** @var  \Joomla\Registry\Registry  $registry */
$registry = $this->registry;

$msgUndefined = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');
?>
<fieldset class="w100p users_profile_basic form-horizontal">
	<legend>
		<?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELDSET_BASIC'); ?>
	</legend>
	<?php if ($this->getShowOption('name')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_USER_FIELD_NAME_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('name'); ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('JGLOBAL_EMAIL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('email'); ?>
		</div>
	</div>
	<?php if ($this->getShowOption('params.timezone')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_USER_FIELD_TIMEZONE_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('params.timezone') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.mobile')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_MOBILE_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('profile.mobile') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php if ($this->getShowOption('profile.website')): ?>
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_WEBSITE_LABEL'); ?></label>
		</div>
		<div class="controls">
			<?php echo $registry->get('profile.website') ?: $msgUndefined; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ($this->getShowOption('profile.avatar')): ?>
		<div class="control-group">
			<div class="control-label">
				<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_AVATAR_LABEL'); ?></label>
			</div>
			<div class="controls">
				<?php
				$filter  = array(
					'list.select' => 'a.id, a.path, a.original_name, a.doc_type, a.doc_reference',
					'table_name'  => 'user',
					'context'     => 'avatar',
					'record_id'   => $registry->get('id'),
					'state'       => 1,
				);
				$avatar = $this->helper->media->loadObject($filter);
				$imgUrl = $avatar ? $this->helper->media->getURL($avatar->path, false) : null;

				if ($imgUrl && $this->helper->media->isImage($avatar->path)):
					?><ul class="media-list media-list-image">
						<li><img src="<?php echo $imgUrl ?>"/></li>
					</ul>
					<div class="clearfix"></div><?php
				else:
					echo $msgUndefined;
				endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->helper->config->get('user_currency') && $this->getShowOption('profile.currency')): ?>
		<div class="control-group">
			<div class="control-label">
				<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_CURRENCY_LABEL'); ?></label>
			</div>
			<div class="controls">
				<?php echo $registry->get('profile.currency') ?: $msgUndefined; ?>
			</div>
		</div>
	<?php endif; ?>
</fieldset>
