<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Create a shortcut for params.
$params = $this->item->params;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$canEdit = $this->item->params->get('access-edit');
$info    = $params->get('info_block_position', 0);
$show_title    = $params->get('show_title', 0);

// Check if associations are implemented. If they are, define the parameter.
$assocParam = (JLanguageAssociations::isEnabled() && $params->get('show_associations'));

?>
<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
		|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $assocParam); ?>

<div class="card card-blog shadow-lg mb-5 mb-lg-0">
	<div class="image-wrap position-relative">
		<?php echo JLayoutHelper::render('joomla.content.intro_image_blog', $this->item); ?>
		<a href="<?php echo  JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)); ?>" class="overly position-absolute text-center d-flex justify-content-center align-items-center text-white">
			<i class="fab fa-telegram-plane"></i>
		</a>
		
	</div>
	<div class="card-body">
		<?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $this->item); ?>
		<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
		<?php // Todo: for Joomla4 joomla.content.info_block.block can be changed to joomla.content.info_block ?>
		<small class="text-muted above-top d-block mb-3 pb-2">
			<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
			<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
						<?php echo JLayoutHelper::render('joomla.content.icons', array('params' => $params, 'item' => $this->item, 'print' => false)); ?>	
			<?php endif; ?>
		</small>

			<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
				<?php echo JLayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
			<?php endif; ?>
		<?php endif; ?>

		<span class="card-text">
			<?php echo $this->item->event->beforeDisplayContent	 ?>
			<?php echo $this->item->introtext; ?>
			<?php echo $this->item->event->afterDisplayContent; ?>
		</span>
		<?php if ($params->get('show_readmore') && $this->item->readmore) : ?>
			<p class="card-text">
					<?PHP if ($params->get('access-view')) :
						$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));
					else :
						$menu = JFactory::getApplication()->getMenu();
						$active = $menu->getActive();
						$itemId = $active->id;
						$link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
						$link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)));
					endif; ?>

					<?php echo JLayoutHelper::render('joomla.content.readmore_blog', array('item' => $this->item, 'params' => $params, 'link' => $link)); ?>

			</p>
		<?php endif; ?>
	</div>
	<?php if ($useDefList && ($info == 1 || $info == 2)) : ?>
		<div class="card-footer">
			<small class="text-muted">
					<!-- Blog Info Starts Here -->
						<?php // Todo Not that elegant would be nice to group the params ?>
							<?php // Todo: for Joomla4 joomla.content.info_block.block can be changed to joomla.content.info_block ?>
							<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
							<?php if ($params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
								<?php echo JLayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
							<?php endif; ?>
					<!-- Blog Info Ends Here -->
					<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
						<?php echo JLayoutHelper::render('joomla.content.icons', array('params' => $params, 'item' => $this->item, 'print' => false)); ?>	
					<?php endif; ?>
				</small>
		</div>
	<?php endif; ?>
</div>
