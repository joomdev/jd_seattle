<?php
/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2019 JoomDev.
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 * 	DO NOT MODIFY THIS FILE DIRECTLY AS IT WILL BE OVERWRITTEN IN THE NEXT UPDATE
 *  You can easily override all files under /frontend/ folder.
 *	Just copy the file to ROOT/templates/YOURTEMPLATE/html/frontend/blog/ folder to create and override
 */
// No direct access.
defined('_JEXEC') or die;
extract($displayData);

$article = $params['article'];
$params = $article->params;
$text = $params->get('astroid_article_quote_text', '');
$author = $params->get('astroid_article_quote_author', '');
if (empty($text) && empty($author)) {
   return;
}
?>
<div class="card mb-3">
   <div class="card-body">
      <blockquote class="blockquote text-right">
         <?php if (!empty($text)) { ?>
            <p class="mb-0"><?php echo $text; ?></p>
         <?php } ?>
         <?php if (!empty($author)) { ?>
            <footer class="blockquote-footer"><cite title="<?php echo $author; ?>"><?php echo $author; ?></cite></footer>
            <?php } ?>
      </blockquote>
   </div>
</div>