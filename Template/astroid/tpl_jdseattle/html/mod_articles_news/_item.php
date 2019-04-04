<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$images	=	json_decode($item->images);
$image_intro	=	($images->image_intro);
$created	=	date_format(date_create($item->created),"d M Y");
?>

<div class="card card-blog">
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
		<p class="card-text"><?php echo $item->title; ?></p>
	</div>
	<div class="card-footer">
		<small class="text-muted">
			<span class="d-inline-block mr-3 mb-2">
				<i class="lni-alarm-clock mr-1"></i> <?php echo $created; ?></span>
			<a href="<?php echo $item->link; ?>" class="d-inline-block mr-3 mb-2">
				<i class="lni-user mr-1"></i> <?php echo $item->author; ?></a>
		</small>
	</div>
</div>