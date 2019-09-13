<?php
/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2019 JoomDev.
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */

defined('_JEXEC') or die;
?>
<div class="items-row row-0 row clearfix">
<?php foreach ($list as $item) : $image = json_decode($item->images); ?>
	<div class="col-lg-4 p-3">
		<div class="card h-100">
			<article class="item column-1" itemprop="blogPost" itemscope=""
				itemtype="https://schema.org/BlogPosting">
				<div class="pull-none item-image">
				<a  href="<?php echo $item->link; ?>"><img src="<?php echo $image->image_intro ?>" alt="" itemprop="thumbnailUrl"></a>
				</div>
				<div class="card-body">
					<div class="item-title">

						<div class="page-header">
							<h2 itemprop="name">
								<a href="<?php echo $item->link; ?>"
									itemprop="url">
									<?php echo $item->title; ?> </a>
							</h2>
						</div>
					</div>
					<dl class="article-info muted">
						<dd class="category-name">
							<i class="far fa-folder"></i>
							<a href="<?php echo JRoute::_("index.php?option=com_content&view=category&layout=$item->category_title&id=$item->catid"); ?>" itemprop="genre"><?php echo $item->category_title; ?>	</a> 
						</dd>
					</dl>
					<?php echo $item->introtext; ?>
					<div class="readmore">
						<a class="btn btn-primary readmore-btn" href="<?php echo $item->link; ?>" itemprop="url" aria-label="Read more:  <?php echo $item->title; ?>">
							Read more ... </a>
					</div>
				</div>
			</article>
		</div>
	</div>
	<?php endforeach; ?>
</div>