<?php

/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2018 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
defined('_JEXEC') or die;
jimport('astroid.framework.helper');
jimport('astroid.framework.constants');
jimport('astroid.framework.template');

class AstroidFrameworkArticle {

   public $type;
   public $article;
   private $params;
   public $template;

   function __construct($article, $categoryView = false) {
      $this->article = $article;
      if ($categoryView) {
         $attribs = new JRegistry();
         $attribs->loadString($this->article->attribs, 'JSON');
         $this->article->params->merge($attribs);
      }

      $this->type = $this->article->params->get('astroid_article_type', 'regular');
      $template = JFactory::getApplication()->getTemplate(true);
      $this->template = new AstroidFrameworkTemplate($template);

      $mainframe = JFactory::getApplication();
      $this->params = new JRegistry();
      $itemId = $mainframe->input->get('Itemid', 0);
      if ($itemId) {
         $menu = $mainframe->getMenu();
         $item = $menu->getItem($itemId);
         if ($item->query['option'] == 'com_content' && ($item->query['view'] == 'category' || $item->query['view'] == 'article')) {
            $this->params = $item->params;
         }
      }
      $this->addMeta();
      $this->renderRating();
   }

   public function addMeta() {

      $enabled = $this->template->params->get('article_opengraph', 0);
      $fb_id = $this->template->params->get('article_opengraph_facebook', '');
      $tw_id = $this->template->params->get('article_opengraph_twitter', '');

      if (empty($enabled)) {
         return;
      }
      $config = JFactory::getConfig();
      $og_title = $this->article->title;
      if (!empty($this->article->params->get('astroid_og_title', ''))) {
         $og_title = $this->article->params->get('astroid_og_title', '');
      }
      $og_description = $this->article->metadesc;
      if (!empty($this->article->params->get('astroid_og_desc', ''))) {
         $og_description = $this->article->params->get('astroid_og_desc', '');
      }
      $images = json_decode($this->article->images);
      if (isset($images->image_intro) && !empty($images->image_intro)) {
         $og_image = htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8');
      }
      if (!empty($this->article->params->get('astroid_og_image', ''))) {
         $og_image = JURI::base() . $this->article->params->get('astroid_og_image', '');
      }



      $og_sitename = $config->get('sitename');
      $og_siteurl = JURI::base() . ContentHelperRoute::getArticleRoute($this->article->slug, $this->article->catid, $this->article->language);

      $meta = [];
      $meta[] = '<meta property="og:type" content="article">';
      $meta[] = '<meta name="twitter:card" content="summary" />';
      if (!empty($og_title)) {
         $meta[] = '<meta property="og:title" content="' . $og_title . '">';
      }
      if (!empty($og_sitename)) {
         $meta[] = '<meta property="og:site_name" content="' . $og_sitename . '">';
      }
      if (!empty($og_siteurl)) {
         $meta[] = '<meta property="og:url" content="' . $og_siteurl . '">';
      }
      if (!empty($og_description)) {
         $meta[] = '<meta property="og:description" content="' . substr($og_description, 0, 200) . '">';
      }
      if (!empty($og_image)) {
         $meta[] = '<meta property="og:type" content="article"><meta property="og:image" content="' . $og_image . '">';
      }
      if (!empty($fb_id)) {
         $meta[] = '<meta property="fb:app_id" content="' . $fb_id . '" />';
      }
      if (!empty($tw_id)) {
         $meta[] = '<meta name="twitter:creator" content="@' . $tw_id . '" />';
      }
      $meta = implode('', $meta);
      if (!empty($meta)) {
         $document = JFactory::getDocument();
         $document->addCustomTag($meta);
      }
   }

   public function render() {
      if ($this->type == 'regular') {
         return false;
      }
      $this->template->loadLayout('blog.' . $this->type, true, ['article' => $this->article]);
   }

   // Read time
   public function renderReadTime() {
      if ($this->showReadTime()) {
         $this->article->readtime = $this->calculateReadTime($this->article->text);
         $this->template->loadLayout('blog.modules.readtime', true, ['article' => $this->article]);
      }
   }

   public function showReadTime() {
      if (JFactory::getApplication()->input->get('tmpl', '') === 'component') {
         return FALSE;
      }

      $menu_level = $this->params->get('astroid_readtime', '');
      $article_level = $this->article->params->get('astroid_readtime', '');
      $astroid_level = $this->template->params->get('astroid_readtime', 1);
      return $this->checkPriority($menu_level, $article_level, $astroid_level);
   }

   // Social Share

   public function renderSocialShare() {
      if ($this->showSocialShare()) {
         $this->template->loadLayout('blog.modules.social', true, ['article' => $this->article]);
      }
   }

   public function showSocialShare() {

      if (JFactory::getApplication()->input->get('tmpl', '') === 'component') {
         return FALSE;
      }

      $menu_level = $this->params->get('astroid_socialshare', '');
      $article_level = $this->article->params->get('astroid_socialshare', '');
      $astroid_level = $this->template->params->get('article_socialshare_type', "none");
      $astroid_level = $astroid_level == 'none' ? 0 : 1;
      return $this->checkPriority($menu_level, $article_level, $astroid_level);
   }

   // Comments
   public function renderComments() {
      if ($this->showComments()) {
         $this->template->loadLayout('blog.modules.comments', true, ['article' => $this->article]);
      }
   }

   public function showComments() {
      if (JFactory::getApplication()->input->get('tmpl', '') === 'component') {
         return FALSE;
      }
      $menu_level = $this->params->get('astroid_comments', '');
      $article_level = $this->article->params->get('astroid_comments', '');
      $astroid_level = $this->template->params->get('article_comments', "none");
      $astroid_level = $astroid_level == 'none' ? 0 : 1;
      return $this->checkPriority($menu_level, $article_level, $astroid_level);
   }

   // Related Posts
   public function renderRelatedPosts() {
      if ($this->showRelatedPosts()) {
         JLoader::register('ModRelatedItemsHelper', JPATH_ROOT . '/modules/mod_related_items/helper.php');
         $params = new JRegistry();
         $params->loadArray(['maximum' => $this->template->params->get('article_relatedposts_count', 4)]);
         $items = ModRelatedItemsHelper::getList($params);
         $this->template->loadLayout('blog.modules.related', true, ['items' => $items]);
      }
   }

   public function showRelatedPosts() {
      if (JFactory::getApplication()->input->get('tmpl', '') === 'component') {
         return FALSE;
      }
      $menu_level = $this->params->get('astroid_relatedposts', '');
      $article_level = $this->article->params->get('astroid_relatedposts', '');
      $astroid_level = $this->template->params->get('article_relatedposts', 1);
      return $this->checkPriority($menu_level, $article_level, $astroid_level);
   }

   // Author Info
   public function renderAuthorInfo() {
      if ($this->showAuthorInfo()) {
         $this->template->loadLayout('blog.modules.author_info', true, ['article' => $this->article]);
      }
   }

   public function showAuthorInfo() {
      if (JFactory::getApplication()->input->get('tmpl', '') === 'component') {
         return FALSE;
      }
      $menu_level = $this->params->get('astroid_authorinfo', '');
      $article_level = $this->article->params->get('astroid_authorinfo', '');
      $astroid_level = $this->template->params->get('article_authorinfo', 1);
      return $this->checkPriority($menu_level, $article_level, $astroid_level);
   }

   // Post Type Icon

   public function renderPostTypeIcon() {
      if ($this->showPostTypeIcon()) {
         $this->template->loadLayout('blog.modules.posttype', true, ['article' => $this->article]);
      }
   }

   public function showPostTypeIcon() {
      if (JFactory::getApplication()->input->get('tmpl', '') === 'component') {
         return FALSE;
      }
      if (JFactory::getApplication()->input->get('option', '') === 'com_content' && JFactory::getApplication()->input->get('view', '') === 'article') {
         return FALSE;
      }
      $menu_level = $this->params->get('astroid_posttype', '');
      $article_level = $this->article->params->get('astroid_posttype', '');
      $astroid_level = $this->template->params->get('article_posttype', 1);
      return $this->checkPriority($menu_level, $article_level, $astroid_level);
   }

   public function renderRating() {
      if ($this->showRating()) {
         $document = JFactory::getDocument();
         $document->addCustomTag('<script src="//cdn.jsdelivr.net/npm/semantic-ui@2.4.0/dist/components/rating.min.js"></script>');
         $document->addStylesheet('//cdn.jsdelivr.net/npm/semantic-ui@2.4.0/dist/components/rating.min.css');
      }
   }

   public function showRating() {
      if (JFactory::getApplication()->input->get('tmpl', '') === 'component') {
         return FALSE;
      }
      return $this->template->params->get('article_rating', 1);
   }

   // Utility functions
   public function checkPriority($firstPriority, $secondPriority, $thirdPriority) {
      $firstPriority = $firstPriority == '' ? -1 : (int) $firstPriority;
      $secondPriority = $secondPriority == '' ? -1 : (int) $secondPriority;
      $thirdPriority = $thirdPriority == '' ? -1 : (int) $thirdPriority;

      $enabled = false;
      switch ($firstPriority) {
         case -1:
            switch ($secondPriority) {
               case -1:
                  switch ($thirdPriority) {
                     case 1:
                        $enabled = true;
                        break;
                     case 0:
                        $enabled = false;
                        break;
                  }
                  break;
               case 1:
                  $enabled = true;
                  break;
               case 0:
                  $enabled = false;
                  break;
            }
            break;
         case 1:
            $enabled = true;
            break;
         case 0:
            $enabled = false;
            break;
      }
      return $enabled;
   }

   public function calculateReadTime($string) {
      $speed = 170;
      $word = str_word_count(strip_tags($string));
      $m = floor($word / $speed);
      $s = floor($word % $speed / ($speed / 60));

      if ($m < 1) {
         $m = 1;
      } else if ($s > 30) {
         $m = $m;
      } else {
         $m++;
      }
      if ($m == 1) {
         return JText::sprintf('ASTROID_ARTICLE_READTIME_MINUTE', $m);
      } else {
         return JText::sprintf('ASTROID_ARTICLE_READTIME_MINUTES', $m);
      }
   }

   public function getTemplateParams() {
      return $this->template->params;
   }

   public function getImage() {
      $type = $this->article->params->get('astroid_article_type', 'regular');
      $thumbnail = '';
      switch ($type) {
         case 'video':
            $thumbnail = $this->getVideoThumbnail();
            break;
         case 'gallery':
            $thumbnail = $this->getGalleryThumbnail();
            break;
      }
      $images = json_decode($this->article->images);
      if (isset($images->image_intro) && !empty($images->image_intro)) {
         $thumbnail = true;
      }
      return $thumbnail;
   }

   public function getGalleryThumbnail() {
      $enabled = $this->article->params->get('astroid_article_thumbnail', 1);
      if (!$enabled) {
         return FALSE;
      }
      $items = $this->article->params->get('astroid_article_gallery_items', []);
      if (empty($items)) {
         return '';
      }
      $first_element = NULL;
      foreach ($items as $item) {
         $first_element = $item;
         break;
      }
      return JURI::root() . $first_element['image'];
   }

   public function getVideoThumbnail() {
      $enabled = $this->article->params->get('astroid_article_thumbnail', 1);
      if (!$enabled) {
         return FALSE;
      }
      $type = $this->article->params->get('astroid_article_video_type', 'youtube');
      $return = '';
      $id = $this->article->params->get('astroid_article_video_url', '');
      if (empty($id)) {
         return $return;
      }
      $id = self::getVideoId($id, $type);
      switch ($type) {
         case 'youtube':
            $return = '//img.youtube.com/vi/' . $id . '/maxresdefault.jpg';
            break;
         case 'vimeo':
            $return = self::getVimeoThumbnailByID($id);
            break;
      }
      return $return;
   }

   public static function getVimeoThumbnailByID($vid) {
      $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/" . $vid . ".php"));
      $thumbnail = $hash[0]['thumbnail_large'];
      return $thumbnail;
   }

   public static function getVideoId($url, $type) {
      $parts = parse_url($url);
      if ($type == "youtube") {
         parse_str($parts['query'], $query);
         return (isset($query['v']) ? $query['v'] : '');
      } else {
         return (isset($parts['path']) ? str_replace('/', '', $parts['path']) : '');
      }
   }

   public static function getArticleRating($id) {
      $db = JFactory::getDbo();
      $query = "SELECT * FROM `#__content_rating` WHERE `content_id`='$id'";
      $db->setQuery($query);
      $result = $db->loadObject();
      if (empty($result)) {
         return 0;
      } else {
         return ceil($result->rating_sum / $result->rating_count);
      }
   }

}
