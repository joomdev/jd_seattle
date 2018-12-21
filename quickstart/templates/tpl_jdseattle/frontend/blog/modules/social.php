<?php
/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2018 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
// No direct access.
defined('_JEXEC') or die;
extract($displayData);

$article = $params['article'];
$params = $article->params;
$type = $template->params->get('article_socialshare_type', 'none');
if ($type == 'none') {
   return;
}
// Addthis Social Share Start 
if ($type == 'addthis') {
	$article_socialshare_addthis = $template->params->get('article_socialshare_addthis', ''); ?>
		<?php if(!empty($article_socialshare_addthis)){ ?>
			<div class="astroid-socialshare">
				<?php echo $article_socialshare_addthis; ?>
			</div>
		<?php } ?>
	<?php
}

// Sharethis Social Share Start 
if ($type == 'sharethis') {
	$article_socialshare_sharethis = $template->params->get('article_socialshare_sharethis', ''); ?>
	<?php if(!empty($article_socialshare_sharethis)){?>
		<?php $doc = JFactory::getDocument(); $doc->addScript('//platform-api.sharethis.com/js/sharethis.js#property='.$article_socialshare_sharethis.'&product=inline-share-buttons'); ?>
			<div class="astroid-socialshare">
				<div class="sharethis-inline-share-buttons"></div>
			</div>
	<?php } ?>
<?php } ?>