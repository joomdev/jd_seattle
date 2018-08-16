<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');

$authorised = JFactory::getUser()->getAuthorisedViewLevels();

?>
<?php if (!empty($displayData)) : ?>
<div class="div-post-tag d-flex flex-wrap align-items-center  py-4 border-top">
	<h6 class="mb-2 mr-3">
		<span class="align-middle">
			<i class="align-middle lni-tag"></i> Tags:
		</span>
	</h6>
	<?php foreach ($displayData as $i => $tag) : ?>
		<?php if (in_array($tag->access, $authorised)) : ?>
			<?php $tagParams = new Registry($tag->params); ?>
			<?php $link_class = $tagParams->get('tag_link_class', 'label label-info'); ?>
		
				<a href="<?php echo JRoute::_(TagsHelperRoute::getTagRoute($tag->tag_id . ':' . $tag->alias)); ?>" class=" d-inline-block mr-2 mb-2 <?php echo $link_class; ?>">
					<?php echo $this->escape($tag->title); ?>
				</a>
		
		<?php endif; ?>
	<?php endforeach; ?>
</div>
<?php endif; ?>
