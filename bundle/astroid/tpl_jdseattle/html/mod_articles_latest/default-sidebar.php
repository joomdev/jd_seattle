<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="recent-post-wrapper shadow-lg p-4 mb-5">
	<h4><?php echo $module->title ?></h4>
	<div class="latestnews recent-post-slide">
	<?php foreach ($list as $item) : $images	=	json_decode($item->images); $image_intro	=	($images->image_intro);  $created	=	date_format(date_create($item->created),"d M Y");?>
	<div class="col p-0 mb-4">
		<div class="card card-blog m-0">
				<div class="image-wrap position-relative">
					<img class="card-img-top" src="<?php echo $image_intro; ?>" alt="Card image cap">
					<a href="<?php echo $item->link; ?>" class="overly position-absolute text-center d-flex justify-content-center align-items-center text-white">
						<i class="fab fa-telegram-plane"></i>
					</a>
				</div>
				<div class="card-body">
					<h5 class="card-title">
						<a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
					</h5>
					<p class="card-text"><?php echo $item->introtext; ?></p>
				</div>
				<div class="card-footer">
					<small class="text-muted">
						<span class="d-inline-block mr-3 mb-2">
							<i class="lni-alarm-clock mr-1"></i>  <?php echo $created; ?>
						</span>
						<a href="<?php echo $item->link; ?>" class="d-inline-block mb-2">
							<i class="lni-user mr-1"></i> <?php echo $item->author; ?>
						</a>
					</small>
				</div>
		</div>
	</div>
	<?php endforeach; ?>
	</div>
</div>

<script src="<?php echo JURI::root(); ?>templates/<?php echo JFactory::getApplication()->getTemplate(true)->template; ?>/js/slick.min.js"></script>
<script>
(function($){
    $(function(){
      $('.recent-post-slide').slick({
			arrows: false,
			dots: true,
			infinite: true,
			speed: 300,
			slidesToShow: 1,
			adaptiveHeight: true
		});
    });
})(jQuery);
</script>