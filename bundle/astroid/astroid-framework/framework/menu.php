<?php

/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2018 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
defined('_JEXEC') or die;
jimport('astroid.framework.constants');
jimport('joomla.application.module.helper');
jimport('astroid.framework.template');

class AstroidMenu {

   public static function getMenu($menutype = '', $nav_class = [], $logo = null, $logoOdd = 'left', $headerType = 'horizontal', $nav_wrapper_class = []) {
      if (empty($menutype)) {
         return '';
      }

      $list = self::getList($menutype);
      $base = self::getBase();
      $active = self::getActive();
      $default = self::getDefault();
      $active_id = $active->id;
      $default_id = $default->id;
      $path = $base->tree;
      $showAll = 1;
      $template = new AstroidFrameworkTemplate(JFactory::getApplication()->getTemplate(true));

      $return = [];
      // Menu Wrapper
      echo '<div class="' . (!empty($nav_wrapper_class) ? ' ' . implode(' ', $nav_wrapper_class) : '') . '">'
      . '<ul class="' . implode(' ', $nav_class) . '">';


      $megamenu = false;
      $count_menu = 0;
      foreach ($list as $i => &$item) {
         if ($item->level == 1) {
            $count_menu++;
         }
      }
      $logo_position = $count_menu / 2;
      $logo_position = (int) $logo_position;
      if ($count_menu % 2 != 0) {
         $logo_position = $logoOdd == 'left' ? $logo_position + 1 : $logo_position;
      }

      $logo_position_count = 0;
      $astroid_menu_options = new stdClass();
      $li_content = [];

      foreach ($list as $i => &$item) {
         $options = self::getAstroidMenuOptions($item, $list);
         $class = self::getLiClass($item, $options, $default_id, $active_id, $path);

         if ($item->level == 1) {
            // Code for adding Centered Logo
            if (($logo_position_count == $logo_position) && $logo !== null) {
               $app = JFactory::getApplication();
               $template = $app->getTemplate(true);
               $template = new AstroidFrameworkTemplate($template);
               echo '<li class="nav-item nav-stacked-logo flex-grow-1 text-center">';
               $template->loadLayout('logo');
               echo '</li>';
            }
            $logo_position_count++;
         }



         if ($options->megamenu && $item->level == 1) {
            echo '<li class="' . \implode(' ', $class) . '">';
            echo $template->loadLayout('header.menu.link', false, ['item' => $item, 'options' => $options, 'mobilemenu' => false, 'active' => in_array('nav-item-active', $class), 'header' => $headerType]);
            echo self::getMegaMenu($item, $options, $list);
            echo '</li>';
         } elseif (!$options->megamenu) {
            echo '<li class="' . \implode(' ', $class) . '">';
            echo $template->loadLayout('header.menu.link', false, ['item' => $item, 'options' => $options, 'mobilemenu' => false, 'active' => in_array('nav-item-active', $class), 'header' => $headerType]);

            // The next item is deeper.
            if ($item->deeper) {
               echo '<div' . ($item->level == 1 ? ' data-width="' . $options->width . '"' : '') . ' class="jddrop-content nav-submenu-container nav-item-level-' . $item->level . '">'
               . '<ul class="nav-submenu">';
            }
            // The next item is shallower.
            elseif ($item->shallower) {
               echo '</li>';
               echo str_repeat('</ul>'
                       . '</div>'
                       . '</li>', $item->level_diff);
            }
            // The next item is on the same level.
            else {
               echo '</li>';
            }
         }
      }
      echo '</ul>'
      . '</div>';
   }

   // Joomla Functions

   public static function getMegaMenu($item, $options, $items) {
      $template = new AstroidFrameworkTemplate(JFactory::getApplication()->getTemplate(true));
      echo '<div data-width="' . $options->width . '" class="jddrop-content megamenu-container">';
      if (!empty($options->rows)) {
         foreach ($options->rows as $row) {
            echo '<div class="row m-0">';
            foreach ($row['cols'] as $col) {
               echo '<div class="col col-md-' . $col['size'] . '">';
               try {
                  foreach ($col['elements'] as $element) {
                     if ($element['type'] == "module") {
                        $modules = JModuleHelper::getModuleList();
                        foreach ($modules as $module) {
                           if ($module->id == $element['id']) {
                              $params = \json_decode($module->params, true);
                              $style = $params['style'];
                              if (empty($style)) {
                                 $style = "html5";
                              }
                              echo '<div class="megamenu-item megamenu-module">';
                              echo JModuleHelper::renderModule($module, ['style' => $style]);
                              echo "</div>";
                           }
                        }
                     } else if ($item->parent) {
                        $base = self::getBase();
                        $active = self::getActive();
                        $default = self::getDefault();
                        $active_id = $active->id;
                        $default_id = $default->id;
                        $path = $base->tree;
                        echo '<div class="megamenu-item megamenu-menu-container">';
                        echo '<ul class="megamenu-menu">';
                        foreach ($items as $i => $subitem) {
                           if ($subitem->id != $element['id']) {
                              continue;
                           }
                           $subitem->anchor_css = empty($subitem->anchor_css) ? 'megamenu-title' : ' ' . $subitem->anchor_css;
                           $options = self::getAstroidMenuOptions($subitem, $items);
                           $class = self::getLiClass($subitem, $options, $default_id, $active_id, $path);
                           echo '<li class="megamenu-menu-item">';
                           echo $template->loadLayout('header.menu.link', false, ['item' => $subitem, 'options' => $options, 'mobilemenu' => true, 'active' => in_array('nav-item-active', $class)]);
                           if ($subitem->parent) {
                              echo '<div class="megamenu-submenu-container">';
                              self::getMegaMenuSubItems($subitem, $items);
                              echo '</div>';
                           }
                           echo '</li>';
                        }
                        echo '</ul>';
                        echo "</div>";
                     }
                  }
               } catch (\Exception $e) {
                  
               }
               echo '</div>';
            }
            echo '</div>';
         }
      }
      echo '</div>';
   }

   public static function getMegaMenuSubItems($parent, $listAll) {
      $base = self::getBase();
      $active = self::getActive();
      $default = self::getDefault();
      $active_id = $active->id;
      $default_id = $default->id;
      $path = $base->tree;
      $template = new AstroidFrameworkTemplate(JFactory::getApplication()->getTemplate(true));

      $return = [];

      $list = [];

      foreach ($listAll as $i => &$item) {
         if ($item->parent_id != $parent->id) {
            continue;
         }
         $list[] = $item;
      }

      echo '<ul class="megamenu-submenu">';
      foreach ($list as $i => &$item) {
         $options = self::getAstroidMenuOptions($item, $list);
         $class = self::getLiClass($item, $options, $default_id, $active_id, $path);

         echo '<li class="' . \implode(' ', $class) . '">';
         echo $template->loadLayout('header.menu.link', false, ['item' => $item, 'options' => $options, 'mobilemenu' => true, 'active' => in_array('nav-item-active', $class)]);
         if ($item->parent) {
            self::getMegaMenuSubItems($item, $listAll);
         }
         echo '</li>';
      }
      echo '</ul>';
   }

   public static function getList($menutype) {
      $app = JFactory::getApplication();
      $menu = $app->getMenu();

      // Get active menu item
      $base = self::getBase();
      $user = JFactory::getUser();
      $levels = $user->getAuthorisedViewLevels();
      asort($levels);

      $path = $base->tree;
      $start = 1;
      $end = 0;
      $showAll = 1;
      $items = $menu->getItems('menutype', $menutype);
      $hidden_parents = array();
      $lastitem = 0;

      if ($items) {
         foreach ($items as $i => $item) {
            $item->parent = false;

            if (isset($items[$lastitem]) && $items[$lastitem]->id == $item->parent_id && $item->params->get('menu_show', 1) == 1) {
               $items[$lastitem]->parent = true;
            }

            if (($start && $start > $item->level) || ($end && $item->level > $end) || (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path)) || ($start > 1 && !in_array($item->tree[$start - 2], $path))) {
               unset($items[$i]);
               continue;
            }

            // Exclude item with menu item option set to exclude from menu modules
            if (($item->params->get('menu_show', 1) == 0) || in_array($item->parent_id, $hidden_parents)) {
               $hidden_parents[] = $item->id;
               unset($items[$i]);
               continue;
            }

            $item->deeper = false;
            $item->shallower = false;
            $item->level_diff = 0;

            if (isset($items[$lastitem])) {
               $items[$lastitem]->deeper = ($item->level > $items[$lastitem]->level);
               $items[$lastitem]->shallower = ($item->level < $items[$lastitem]->level);
               $items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
            }

            $lastitem = $i;
            $item->active = false;
            $item->flink = $item->link;

            // Reverted back for CMS version 2.5.6
            switch ($item->type) {
               case 'separator':
                  break;

               case 'heading':
                  // No further action needed.
                  break;

               case 'url':
                  if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false)) {
                     // If this is an internal Joomla link, ensure the Itemid is set.
                     $item->flink = $item->link . '&Itemid=' . $item->id;
                  }
                  break;

               case 'alias':
                  $item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
                  break;

               default:
                  $item->flink = 'index.php?Itemid=' . $item->id;
                  break;
            }

            if ((strpos($item->flink, 'index.php?') !== false) && strcasecmp(substr($item->flink, 0, 4), 'http')) {
               $item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
            } else {
               $item->flink = JRoute::_($item->flink);
            }

            // We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
            // when the cause of that is found the argument should be removed
            $item->title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
            $item->anchor_css = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
            $item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
            $item->anchor_rel = htmlspecialchars($item->params->get('menu-anchor_rel', ''), ENT_COMPAT, 'UTF-8', false);
            $item->menu_image = $item->params->get('menu_image', '') ?
                    htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
            $item->menu_image_css = htmlspecialchars($item->params->get('menu_image_css', ''), ENT_COMPAT, 'UTF-8', false);
         }

         if (isset($items[$lastitem])) {
            $items[$lastitem]->deeper = (($start ?: 1) > $items[$lastitem]->level);
            $items[$lastitem]->shallower = (($start ?: 1) < $items[$lastitem]->level);
            $items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start ?: 1));
         }
      }

      return $items;
   }

   public static function getBase() {
      $menu = JFactory::getApplication()->getMenu();
      $active = $menu->getActive();


      if ($active) {
         return $active;
      }

      return self::getActive();
   }

   public static function getActive() {
      $menu = JFactory::getApplication()->getMenu();
      return $menu->getActive() ?: self::getDefault();
   }

   public static function getDefault() {
      $menu = JFactory::getApplication()->getMenu();
      $lang = JFactory::getLanguage();

      // Look for the home menu
      if (JLanguageMultilang::isEnabled()) {
         return $menu->getDefault($lang->getTag());
      } else {
         return $menu->getDefault();
      }
   }

   public static function getAstroidMenuOptions($item, $list) {
      $astroid_menu_options = $item->params->get('astroid_menu_options', []);
      $astroid_menu_options = (array) $astroid_menu_options;
      // set defaults
      $data = new \stdClass();
      $data->megamenu = 0;
      $data->icononly = 0;
      $data->subtitle = '';
      $data->icon = '';
      $data->customclass = '';
      $data->width = '';
      $data->alignment = '';
      $data->rows = [];


      if (isset($astroid_menu_options['megamenu']) && $astroid_menu_options['megamenu']) {
         $data->megamenu = 1;
      }
      if (isset($astroid_menu_options['showtitle']) && $astroid_menu_options['showtitle']) {
         $data->icononly = 1;
      }
      if (isset($astroid_menu_options['subtitle']) && !empty($astroid_menu_options['subtitle'])) {
         $data->subtitle = $astroid_menu_options['subtitle'];
      }
      if (isset($astroid_menu_options['icon']) && !empty($astroid_menu_options['icon'])) {
         $data->icon = $astroid_menu_options['icon'];
      }
      if (isset($astroid_menu_options['customclass']) && !empty($astroid_menu_options['customclass'])) {
         $data->customclass = $astroid_menu_options['customclass'];
      }
      if (isset($astroid_menu_options['rows']) && !empty($astroid_menu_options['rows'])) {
         $data->rows = \json_decode($astroid_menu_options['rows'], true);
      }
      if (!$data->megamenu) {
         if (isset($astroid_menu_options['width']) && !empty($astroid_menu_options['width'])) {
            $data->width = $astroid_menu_options['width'];
         }
         if (isset($astroid_menu_options['alignment']) && !empty($astroid_menu_options['alignment'])) {
            $data->alignment = $astroid_menu_options['alignment'];
         } else {
            $data->alignment = 'right';
         }
      } else {
         if (isset($astroid_menu_options['megamenu_width']) && !empty($astroid_menu_options['megamenu_width'])) {
            $data->width = $astroid_menu_options['megamenu_width'];
         }
         if (isset($astroid_menu_options['megamenu_direction']) && !empty($astroid_menu_options['megamenu_direction'])) {
            $data->alignment = $astroid_menu_options['megamenu_direction'];
         } else {
            $data->alignment = 'center';
         }
      }
      if ($data->alignment == 'full') {
         $data->width = 'container';
         $data->alignment = 'center';
      }
      if ($data->alignment == 'edge') {
         $data->width = '100vw';
         $data->alignment = 'center';
      }

      if ($item->level > 1) {
         $data->megamenu = self::isParentMegamenu($item->parent_id, $list);
      }

      return $data;
   }

   public static function isParentMegamenu($pid, $list) {
      $parent = null;
      foreach ($list as $item) {
         if ($item->id == $pid) {
            $parent = $item;
            break;
         }
      }
      if ($parent === null) {
         return 0;
      }
      if ($parent->level > 1) {
         return self::isParentMegamenu($parent->parent_id, $list);
      } else {
         $options = self::getAstroidMenuOptions($parent, $list);
         return $options->megamenu;
      }
   }

   public static function getLiClass($item, $options, $default_id, $active_id, $path) {
      $class = [];
      if ($item->level != 1) {
         $class[] = 'nav-item-submenu';
      } else {
         $class[] = 'nav-item';
      }
      $class[] = 'nav-item-id-' . $item->id;
      $class[] = 'nav-item-level-' . $item->level;

      if ($item->id == $default_id) {
         $class[] = 'nav-item-default';
      }

      if ($item->id == $active_id || ($item->type === 'alias' && $item->params->get('aliasoptions') == $active_id)) {
         $class [] = 'nav-item-current';
      }

      if (in_array($item->id, $path)) {
         $class[] = 'nav-item-active';
      } elseif ($item->type === 'alias') {
         $aliasToId = $item->params->get('aliasoptions');
         if (count($path) > 0 && $aliasToId == $path[count($path) - 1]) {
            $class[] = 'nav-item-active';
         } elseif (in_array($aliasToId, $path)) {
            $class[] = 'nav-item-alias-parent-active';
         }
      }

      if ($item->type === 'separator') {
         $class[] = 'nav-item-divider';
      }

      if ($item->deeper) {
         $class[] = 'nav-item-deeper';
      }

      if ($item->parent || $options->megamenu) {
         $class[] = 'nav-item-parent';
      }

      if ($options->megamenu) {
         $class[] = 'nav-item-megamenu';
      } else if ($item->parent) {
         $class[] = 'nav-item-dropdown';
      }

      if (!empty($options->customclass)) {
         $class[] = $options->customclass;
      }
      return $class;
   }

   public static function getMobileMenu($menutype = '') {
      if (empty($menutype)) {
         return '';
      }

      $list = self::getList($menutype);
      $base = self::getBase();
      $active = self::getActive();
      $default = self::getDefault();
      $active_id = $active->id;
      $default_id = $default->id;
      $path = $base->tree;
      $showAll = 1;
      $template = new AstroidFrameworkTemplate(JFactory::getApplication()->getTemplate(true));

      echo '<ul class="astroid-mobile-menu d-none">';
      $megamenu = false;
      $count_menu = 0;
      foreach ($list as $i => &$item) {
         if ($item->level == 1) {
            $count_menu++;
         }
      }
      foreach ($list as $i => &$item) {
         $options = self::getAstroidMenuOptions($item, $list);
         $class = self::getLiClass($item, $options, $default_id, $active_id, $path);
         echo '<li class="' . \implode(' ', $class) . '">';
         echo $template->loadLayout('header.menu.link', false, ['item' => $item, 'options' => $options, 'mobilemenu' => true, 'active' => in_array('nav-item-active', $class)]);
         if ($item->deeper) {
            echo '<ul class="nav-child list-group navbar-subnav level-' . $item->level . '">';
         } elseif ($item->shallower) {
            echo '</li>';
            echo str_repeat('</ul></li>', $item->level_diff);
         } else {
            echo '</li>';
         }
      }
      echo '</ul>';
   }

}
