<?php
/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

defined('_JEXEC') or die();

//Load the method jquery script.
JHtml::_('jquery.framework');
$doc = JFactory::getDocument();
$doc->addStylesheet( JURI::root(true) . '/components/com_spsimpleportfolio/assets/css/featherlight.min.css' );
$doc->addStylesheet( JURI::root(true) . '/components/com_spsimpleportfolio/assets/css/spsimpleportfolio.css' );
$doc->addScript( JURI::root(true) . '/components/com_spsimpleportfolio/assets/js/jquery.shuffle.modernizr.min.js' );
$doc->addScript( JURI::root(true) . '/components/com_spsimpleportfolio/assets/js/featherlight.min.js' );
$doc->addScript( JURI::root(true) . '/components/com_spsimpleportfolio/assets/js/spsimpleportfolio.js' );

if( $this->params->get('show_page_heading') && $this->params->get( 'page_heading' ) ) {
	echo "<h1 class='page-header'>" . $this->params->get( 'page_heading' ) . "</h1>";
}
?>

<div id="sp-simpleportfolio" class="sp-simpleportfolio sp-simpleportfolio-view-items layout-<?php echo $this->layout_type; ?>">
	<?php if($this->params->get('show_filter', 1)) { ?>
	<div class="sp-simpleportfolio-filter">
		<ul>
			<li class="active" data-group="all"><a href="#"><?php echo JText::_('COM_SPSIMPLEPORTFOLIO_SHOW_ALL'); ?></a></li>
			<?php foreach ($this->tagList as $filter) { ?>
			<li data-group="<?php echo $filter->alias; ?>"><a href="#"><?php echo $filter->title; ?></a></li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>

	<?php
	//Videos
	foreach ($this->items as $key => $this->item) {
		if($this->item->video) {
			$video = parse_url($this->item->video);

			switch($video['host']) {
				case 'youtu.be':
				$video_id 	= trim($video['path'],'/');
				$video_src 	= '//www.youtube.com/embed/' . $video_id;
				break;

				case 'www.youtube.com':
				case 'youtube.com':
				parse_str($video['query'], $query);
				$video_id 	= $query['v'];
				$video_src 	= '//www.youtube.com/embed/' . $video_id;
				break;

				case 'vimeo.com':
				case 'www.vimeo.com':
				$video_id 	= trim($video['path'],'/');
				$video_src 	= "//player.vimeo.com/video/" . $video_id;
			}

			echo '<iframe class="sp-simpleportfolio-lightbox" src="'. $video_src .'" width="500" height="281" id="sp-simpleportfolio-video'.$this->item->id.'" style="border:none;" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
		}
	}
	?>

	<div class="sp-simpleportfolio-items sp-simpleportfolio-columns-<?php echo $this->params->get('columns', 3); ?>">
		<?php foreach ($this->items as $this->item) { ?>
		<div class="sp-simpleportfolio-item" data-groups='[<?php echo $this->item->groups; ?>]'>
			<div class="sp-simpleportfolio-overlay-wrapper clearfix">

				<?php if($this->item->video) { ?>
				<span class="sp-simpleportfolio-icon-video"></span>
				<?php } ?>

				<img class="sp-simpleportfolio-img" src="<?php echo $this->item->thumb; ?>" alt="<?php echo $this->item->title; ?>">

				<div class="sp-simpleportfolio-overlay">
					<div class="sp-vertical-middle">
						<div>
							<div class="sp-simpleportfolio-btns">
								<?php if( $this->item->video ) { ?>
								<a class="btn-zoom" href="#" data-featherlight="#sp-simpleportfolio-video<?php echo $this->item->id; ?>"><?php echo JText::_('COM_SPSIMPLEPORTFOLIO_WATCH'); ?></a>
								<?php } else { ?>
								<a class="btn-zoom" href="<?php echo $this->item->popup_img_url; ?>" data-featherlight="image"><?php echo JText::_('COM_SPSIMPLEPORTFOLIO_ZOOM'); ?></a>
								<?php } ?>
								<a class="btn-view" href="<?php echo $this->item->url; ?>"><?php echo JText::_('COM_SPSIMPLEPORTFOLIO_VIEW'); ?></a>
							</div>
							<?php if($this->layout_type != 'default') { ?>
							<h3 class="sp-simpleportfolio-title">
								<a href="<?php echo $this->item->url; ?>">
									<?php echo $this->item->title; ?>
								</a>
							</h3>
							<div class="sp-simpleportfolio-tags">
								<?php echo implode(', ', $this->item->tags); ?>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>

			<?php if($this->layout_type=='default') { ?>
			<div class="sp-simpleportfolio-info">
				<h3 class="sp-simpleportfolio-title">
					<a href="<?php echo $this->item->url; ?>">
						<?php echo $this->item->title; ?>
					</a>
				</h3>
				<div class="sp-simpleportfolio-tags">
					<?php echo implode(', ', $this->item->tags); ?>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>

	<?php if ($this->pagination->get('pages.total') >1) { ?>
	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php } ?>
</div>
