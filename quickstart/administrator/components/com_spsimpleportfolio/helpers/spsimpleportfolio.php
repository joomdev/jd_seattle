<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

defined('_JEXEC') or die;

class SpsimpleportfolioHelper {

  public static $extension = 'com_spsimpleportfolio';

  public static function addSubmenu($submenu) {

    JHtmlSidebar::addEntry(
      JText::_('COM_SPSIMPLEPORTFOLIO_TITLE_ITEMS'),
      'index.php?option=com_spsimpleportfolio',
      $submenu == 'items'
    );

    JHtmlSidebar::addEntry(
      JText::_('COM_SPSIMPLEPORTFOLIO_CATEGORIES'),
      'index.php?option=com_categories&view=categories&extension=com_spsimpleportfolio',
      $submenu == 'categories'
    );

    JHtmlSidebar::addEntry(
      JText::_('COM_SPSIMPLEPORTFOLIO_TITLE_TAGS'),
      'index.php?option=com_spsimpleportfolio&view=tags',
      $submenu == 'tags'
    );
  }

  public static function getActions($messageId = 0) {
    $result	= new JObject;
    if (empty($messageId)) {
      $assetName = 'com_spsimpleportfolio';
    } else {
      $assetName = 'com_spsimpleportfolio.item.'.(int) $messageId;
    }
    $actions = JAccess::getActions('com_spsimpleportfolio', 'component');
    foreach ($actions as $action) {
      $result->set($action->name, JFactory::getUser()->authorise($action->name, $assetName));
    }
    return $result;
  }

  // Create thumbs
  public static function createThumbs($src, $sizes = array(), $folder, $base_name, $ext) {

    list($originalWidth, $originalHeight) = getimagesize($src);

    switch($ext) {
      case 'bmp': $img = imagecreatefromwbmp($src); break;
      case 'gif': $img = imagecreatefromgif($src); break;
      case 'jpg': $img = imagecreatefromjpeg($src); break;
      case 'jpeg': $img = imagecreatefromjpeg($src); break;
      case 'png': $img = imagecreatefrompng($src); break;
    }

    if(count($sizes)) {
      $output = array();

      if($base_name) {
        $output['original'] = $folder . '/' . $base_name . '.' . $ext;
      }

      foreach ($sizes as $key => $size) {
        $targetWidth = $size[0];
        $targetHeight = $size[1];
        $ratio_thumb = $targetWidth/$targetHeight;
        $ratio_original = $originalWidth/$originalHeight;

        if ($ratio_original >= $ratio_thumb) {
          $height = $originalHeight;
          $width = ceil(($height*$targetWidth)/$targetHeight);
          $x = ceil(($originalWidth-$width)/2);
          $y = 0;
        } else {
          $width = $originalWidth;
          $height = ceil(($width*$targetHeight)/$targetWidth);
          $y = ceil(($originalHeight-$height)/2);
          $x = 0;
        }

        $new = imagecreatetruecolor($targetWidth, $targetHeight);

        if($ext == "gif" or $ext == "png") {
          imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
          imagealphablending($new, false);
          imagesavealpha($new, true);
        }

        imagecopyresampled($new, $img, 0, 0, $x, $y, $targetWidth, $targetHeight, $width, $height);

        if($base_name) {
          $dest = dirname($src) . '/' . $base_name . '_' . $key . '.' . $ext;
          $output[$key] = $folder . '/' . $base_name . '_' . $key . '.' . $ext;
        } else {
          $dest = $folder . '/' . $key . '.' . $ext;
        }

        switch($ext) {
          case 'bmp': imagewbmp($new, $dest); break;
          case 'gif': imagegif($new, $dest); break;
          case 'jpg': imagejpeg($new, $dest); break;
          case 'jpeg': imagejpeg($new, $dest); break;
          case 'png': imagepng($new, $dest); break;
        }
      }

      return $output;
    }

    return false;
  }

  public static function isPageBuilderIntegrated($item) {

    $output = new stdClass();
    $integration = false;
    $output->url = '';

    if(JPluginHelper::isEnabled('spsimpleportfolio', 'sppagebuilder')) {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      $user = JFactory::getUser();
      $query->select('a.id');
      $query->from('#__sppagebuilder_integrations as a');
      $query->where($db->quoteName('component') . ' = ' . $db->quote('com_spsimpleportfolio'));
      $query->where($db->quoteName('state') . ' = 1');
      $db->setQuery($query);
      $integration = $db->loadResult();

      $hasPage = self::hasPBPage($item->id);
      $output->hasPage = $hasPage;
      

      if($integration && $hasPage) {

        $app = JApplication::getInstance('site');
        $router = $app->getRouter();

        $lang_code = (isset($item->language) && $item->language && explode('-',$item->language)[0])? explode('-',$item->language)[0] : '';
        $enable_lang_filter = JPluginHelper::getPlugin('system', 'languagefilter');
        $conf = JFactory::getConfig();

        $front_link = 'index.php?option=com_sppagebuilder&view=form&tmpl=componenet&layout=edit&extension=com_spsimpleportfolio&extension_view=item&id=' . $hasPage;
        $sefURI = str_replace('/administrator', '', $router->build($front_link));
        if($lang_code && $lang_code !== '*' && $enable_lang_filter && $conf->get('sef') ){
          $sefURI = str_replace('/index.php/', '/index.php/' . $lang_code . '/', $sefURI);
        } elseif($lang_code && $lang_code !== '*') {
          $sefURI = $sefURI . '&lang=' . $lang_code;
        }

        $output->url = $sefURI;
      }
    }

    $output->integration = $integration;

    return $output;
  }

  public static function hasPBPage($view_id = 0) {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('id')));
    $query->from($db->quoteName('#__sppagebuilder'));
    $query->where($db->quoteName('extension') . ' = '. $db->quote('com_spsimpleportfolio'));
    $query->where($db->quoteName('extension_view') . ' = '. $db->quote('item'));
    $query->where($db->quoteName('view_id') . ' = '. $db->quote($view_id));
    $db->setQuery($query);
    return $db->loadResult();
  }
}
