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

/** @var SellaciousViewProduct $this */
JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellacious/owl.carousel.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/owl.theme.default.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.detail.image.css', null, true);

JHtml::_('script', 'com_sellacious/owl.carousel.min.js', true, true);

if ($this->helper->config->get('image_gallery_enable')):
	JHtml::_('script', 'com_sellacious/jquery.fancybox-plus.js', true, true);
	JHtml::_('stylesheet', 'com_sellacious/jquery.fancybox-plus.css', null, true);
endif;
JHtml::_('script', 'com_sellacious/jquery.ez-plus.js', true, true);
JHtml::_('script', 'com_sellacious/product.detail.js', true, true);

$imW = (int) $this->helper->config->get('product_img_width', 270);
$imH = (int) $this->helper->config->get('product_img_height', 270);
$imA = $this->helper->config->get('image_slider_size_adjust') ?: 'contain';

$pvUrl   = '';
$pvImage = '';
$playBtn = JHtml::_('image', 'com_sellacious/play-btn.png', null, null, true, 1);;

if ($this->item->get('primary_video_url')):
	$pvUrl   = $this->helper->media->generateVideoEmbedUrl($this->item->get('primary_video_url'));
	$pvImage = $this->helper->media->generateVideoThumb($this->item->get('primary_video_url'));
endif;

$iz_gallery_enable    = (int) $this->helper->config->get('image_gallery_enable', 1);
$iz_navigation_enable = (int) $this->helper->config->get('image_navigation_enable', 1);

$iz_enable       = (int) $this->helper->config->get('image_zoom_enable', 1);
$iz_type         = $this->helper->config->get('image_zoom_type', 'lens');
$iz_border_width = (int) $this->helper->config->get('image_zoom_border_width', 8);
$iz_border_color = $this->helper->config->get('image_zoom_border_color', 'rgba(0, 0, 0, 0.1)');

$iz_lens_size = (int) $this->helper->config->get('image_zoom_lens_size', 200);

$iz_window_width          = (int) $this->helper->config->get('image_zoom_window_width', 400);
$iz_window_height         = (int) $this->helper->config->get('image_zoom_window_height', 400);
$iz_lens_border_width     = (int) $this->helper->config->get('image_zoom_lens_border_width', 1);
$iz_lens_border_color     = $this->helper->config->get('image_zoom_lens_border_color', 'rgba(0, 0, 0, 0.1)');
$iz_lens_background_color = $this->helper->config->get('image_zoom_lens_background_color', 'rgba(255, 255, 255, 0.4)');
$iz_easing_enable         = (int) $this->helper->config->get('image_zoom_easing_enable', 1);

$iz_type_mobile      = $this->helper->config->get('image_zoom_type_mobile', 'lens');
$iz_lens_size_mobile = (int) $this->helper->config->get('image_zoom_lens_size_mobile', '180');

$ezOptions = array();

$ezOptions['borderColour'] = $iz_border_color;
$ezOptions['borderSize']   = $iz_border_width;
$ezOptions['easing']       = $iz_easing_enable ? true : false;
$ezOptions['zoomType']     = $iz_type;

if ($iz_gallery_enable):
	$ezOptions['gallery']  = 'detail-gallery';
	$ezOptions['cursor']   = 'pointer';
endif;

if ($iz_type == 'lens'):
	$ezOptions['lensShape'] = 'round';
	$ezOptions['lensSize']  = $iz_lens_size;
else:
	$ezOptions['zoomWindowHeight']  = $iz_window_height;
	$ezOptions['zoomWindowWidth']   = $iz_window_width;
	$ezOptions['zoomWindowFadeIn']  = 300;
	$ezOptions['zoomWindowFadeOut'] = 300;

	$ezOptions['lensBorderColour'] = $iz_lens_border_color;
	$ezOptions['lensBorderSize']   = $iz_lens_border_width;
	$ezOptions['lensColour']       = $iz_lens_background_color;
	$ezOptions['lensOpacity']      = 1;
endif;

$ezOptions['lensFadeIn']  = 300;
$ezOptions['lensFadeOut'] = 300;
$ezOptions['enabled']     = $iz_enable ? true : false;
$ezOptions['zIndex']      = 92;

if ($iz_type_mobile == 'lens') :
	$ezOptions['respond']          	  = array(
		array('range' => '0-991', 'zoomType' => 'lens', 'lensShape' => 'round', 'lensSize' => $iz_lens_size_mobile),
	);
else:
	$ezOptions['respond']          	  = array(
		array('range' => '0-991', 'showLens' => false),
	);
endif;


$jsEzOptions = json_encode($ezOptions);

$this->document->addScriptDeclaration(<<<JS
	
	jQuery(function (jq) {
		jq(document).ready(function() {
			var image       = jq('.image-detail .product-img');
			var fancyBox    = {$iz_gallery_enable};
			var playBtn     = "{$playBtn}";
			
			//For Previous Slide
			jq('.prevslide').on('click',function(){
				var thumbs = jq('.products-slider-detail').find('a');
				var thumb = jq('.products-slider-detail').find('a.current');
				var index = thumbs.index(thumb) === 0 ? thumbs.length - 1 : thumbs.index(thumb) - 1;
				
				var prevthumb = thumbs.eq(index);
				thumb.removeClass('current');
				prevthumb.addClass('current').trigger('click');
			});
			
			//For Next Slide
			jq('.nextslide').on('click',function() {
				var thumbs = jq('.products-slider-detail').find('a');
				var thumb = jq('.products-slider-detail').find('a.current');
				
				var index = thumbs.index(thumb) === thumbs.length - 1 ? 0 : thumbs.index(thumb) + 1;
				
				var nextthumb = thumbs.eq(index);
				thumb.removeClass('current');
				nextthumb.addClass('current').trigger('click');
			});
			
			jq('.products-slider-detail a').click(function () {
				var thumb = jq(this).find('.thumb-img');
				var owlStage = jq(this).parent().parent();
				owlStage.find('a').removeClass('current');
				jq(this).addClass('current');
				
				var src = thumb.attr('data-src');
				var srcZ = thumb.attr('data-zoom-image');
				image.css('background-image', 'url("' + src + '")');
				
				image.find('.play-btn').remove();
				
				var EZP = image.data('ezPlus');
				
				if (jq(this).data('video-url')) {
					image.append('<img class="play-btn" src="' + playBtn + '">');
					if (EZP) EZP.changeState('disable');
				}
				else {
					if (EZP) {
						EZP.changeState('enable');
						EZP.swaptheimage(src, srcZ);
					}
				}
				
				return false;
			});
			
			//Enable Gallery in Fancy Box Popup
			if(fancyBox === 1){
				image.bind('click', function (e) {
					var ez = image.data('ezPlus');
					var galleryList = [];
					if (ez.options.gallery) {
						jq('#' + ez.options.gallery + ' a').each(function () {
							var imgSrc = '';
							if (jq(this).data(ez.options.attrImageZoomSrc)) {
								imgSrc = jq(this).data(ez.options.attrImageZoomSrc);
							}
							else if (jq(this).data('image')) {
								imgSrc = jq(this).data('image');
							}
							//put the current image at the start
							if (imgSrc === ez.zoomImage) {
								if(jq(this).data('fbplus-type') === 'iframe'){
									imgSrc = jq(this).data('video-url');
								}
								galleryList.unshift({
									href: '' + imgSrc + '',
									title: jq(this).find('img').attr('title'),
									type: jq(this).data('fbplus-type')
								});
							}
							else {
								if(jq(this).data('fbplus-type') === 'iframe'){
									imgSrc = jq(this).data('video-url');
								}
								galleryList.push({
									href: '' + imgSrc + '',
									title: jq(this).find('img').attr('title'),
									type: jq(this).data('fbplus-type')
								});
							}
						});
					}
					//if no gallery - return current image
					else {
						galleryList.push({
							href: '' + ez.zoomImage + '',
							title: jq(this).find('img').attr('title'),
							type: jq(this).data('fbplus-type')
						});
					}
					
					jq.fancyboxPlus(galleryList);
					return false;
				});
			}

			var opts = jq.extend({}, {$jsEzOptions}, {});
			
			function initEZPlus() {
				
				image.ezPlus(opts);
			}
			
			//Init elevateZoom
			initEZPlus();
			
			//Triggered when window width is changed.
			jq( window ).on( "resize", function() {
				//ReInit elevateZoom
				var obj = image.data('ezPlus');
				if (obj) obj.destroy();
				image.ezPlus(opts);
			});
		});
	});
JS
);
?>
<style>
<?php if($iz_type == 'lens'):?>
.zoomContainer {
	overflow: hidden;
}
<?php endif; ?>
.image-detail .product-img,
.image-detail .product-vid {
	min-width: <?php echo $imW ?>px;
	width: 100%;
	height: <?php echo $imH ?>px;
	background-size: <?php echo $imA ?>;
	position: relative !important;
}
.owl-carousel .owl-item img.play-btn {
	position: absolute;
	left: 25%;
	width: 50%;
	top: 25%;
}
.product-img img.play-btn {
	position: absolute;
	left: 35%;
	width: 30%;
	top: 50%;
}
</style>
<div id="product-images-container">
	<div class="productdetail-img">
		<?php $images = $this->item->get('images'); ?>
		<?php $image  = reset($images) ?>
		<div class="image-detail">
			<div class="product-img" style="background-image: url(<?php echo $image ?>);" data-zoom-image="<?php echo $image ?>"
				 data-src="<?php echo htmlspecialchars($image) ?>">
			</div>

			<?php if ($iz_navigation_enable && count($images) > 1): ?>
			<div class="slidecontrol">
				<a href="javascript:void(0);" class="prevslide"><i class="fa fa-angle-left"></i></a>
				<a href="javascript:void(0);" class="nextslide"><i class="fa fa-angle-right"></i></a>
			</div>
			<?php endif; ?>
		</div>
		<div id="detail-gallery"  class="products-slider-detail owl-carousel owl-theme">
			<?php foreach ($images as $i => $image): ?>
				<?php
				$anchorClass = $i == 0 ? 'current' : ''; ?>

				<a href="#" class="<?php echo $anchorClass; ?>" data-fbplus-type="image" data-zoom-image="<?php echo $image ?>">
					<span class="thumb-img" style="background-image: url(<?php echo $image ?>);"
						  data-zoom-image="<?php echo $image ?>" data-src="<?php echo htmlspecialchars($image) ?>">
					</span>
				</a>
			<?php endforeach; ?>
			<?php if ($pvUrl): ?>
			<a href="#" class="product-vid" data-fbplus-type="iframe" data-video-url="<?php echo $pvUrl ?>" data-zoom-image="<?php echo $pvImage ?>">
					<span class="thumb-img" style="background-image: url(<?php echo $pvImage ?>);"
						  data-zoom-image="<?php echo $pvImage ?>" data-src="<?php echo htmlspecialchars($pvImage) ?>">
						<img class="play-btn" src="<?php echo $playBtn ?>">
					</span>
			</a>
			<?php endif; ?>
		</div>
	</div>
</div>
