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

$params = $template->params;
$mode = $params->get('header_stacked_menu_mode', 'center');
$block_1_type = $params->get('header_block_1_type', 'blank');
$block_1_position = $params->get('header_block_1_position', '');
$block_1_custom = $params->get('header_block_1_custom', '');
$block_2_type = $params->get('header_block_2_type', 'blank');
$block_2_position = $params->get('header_block_2_position', '');
$block_2_custom = $params->get('header_block_2_custom', '');
$header_mobile_menu = $params->get('header_mobile_menu', '');
$header_menu = $params->get('header_menu', '');
$odd_menu_items = $params->get('odd_menu_items', 'left');
$class = ['astroid-header', 'astroid-stacked-header', 'astroid-stacked-' . $mode . '-header'];
$enable_offcanvas = $params->get('enable_offcanvas', FALSE);
$offcanvas_animation = $params->get('offcanvas_animation', 'st-effect-1');
$offcanvas_togglevisibility = $params->get('offcanvas_togglevisibility', 'd-block');
$navClass = ['nav', 'astroid-nav', 'justify-content-center', 'd-flex', 'align-items-center'];
$navClassLeft = ['nav', 'astroid-nav', 'justify-content-left', 'd-flex', 'align-items-left'];
$navClassDivided = ['nav', 'astroid-nav'];
$navWrapperClass = ['astroid-nav-wraper', 'align-self-center', 'px-2', 'd-none', 'd-lg-block', 'w-100'];
?>
<header id="astroid-header" class="<?php echo implode(' ', $class); ?>">
   <div class="d-flex">
      <div class="header-stacked-section d-flex justify-content-between flex-column w-100">
         <?php
         if ($mode == 'center') {
            echo '<div class="w-100 d-flex justify-content-center">';
            ?>
            <?php if (!empty($header_mobile_menu)) { ?>
               <div class="d-flex d-lg-none justify-content-start">
                  <div class="header-mobilemenu-trigger d-lg-none burger-menu-button align-self-center" data-offcanvas="#astroid-mobilemenu" data-effect="mobilemenu-slide">
                     <button class="button" type="button"><span class="box"><span class="inner"></span></span></button>
                  </div>
               </div>
            <?php } ?>
            <?php
            echo '<div class="d-flex w-100 justify-content-center">';
            $template->loadLayout('logo');
            echo '</div>';
            if ($enable_offcanvas) {
               ?>
               <div class="d-flex justify-content-end">
                  <div class="header-offcanvas-trigger burger-menu-button align-self-center <?php echo $offcanvas_togglevisibility; ?>" data-offcanvas="#astroid-offcanvas" data-effect="<?php echo $offcanvas_animation; ?>">
                     <button type="button" class="button">
                        <span class="box">
                           <span class="inner"></span>
                        </span>
                     </button>
                  </div>
               </div>
               <?php
            }
            echo '</div>';
            // header nav starts -->
            echo '<div class="w-100 d-none d-lg-flex justify-content-center py-3">';
            AstroidMenu::getMenu($header_menu, array_merge($navClass), null, 'left', 'stacked', $navWrapperClass);
            echo '</div>';
            // header nav ends
            // header block starts
            if ($block_1_type == 'position') {
               echo '<div class="w-100 header-block-item d-none d-lg-flex justify-content-center py-3">';
               echo $template->renderModulePosition($block_1_position, 'xhtml');
               echo '</div>';
            }
            if ($block_1_type == 'custom') {
               echo '<div class="w-100 header-block-item d-none d-lg-flex justify-content-center py-3">';
               echo $block_1_custom;
               echo '</div>';
            }

            // header block ends
         }
         if ($mode == 'seperated') {
            // header block starts
            if ($block_1_type == 'position') {
               echo '<div class="w-100 header-block-item d-none d-lg-flex justify-content-center py-3">';
               echo $template->renderModulePosition($block_1_position, 'xhtml');
               echo '</div>';
            }
            if ($block_1_type == 'custom') {
               echo '<div class="w-100 header-block-item d-none d-lg-flex justify-content-center py-3">';
               echo $block_1_custom;
               echo '</div>';
            }
            // header nav starts   
            echo '<div class="header-stacked-inner w-100 d-flex justify-content-center">';
            ?>
            <?php if (!empty($header_mobile_menu)) { ?>
               <div class="d-flex d-lg-none justify-content-start">
                  <div class="header-mobilemenu-trigger d-lg-none burger-menu-button align-self-center" data-offcanvas="#astroid-mobilemenu" data-effect="mobilemenu-slide">
                     <button class="button" type="button"><span class="box"><span class="inner"></span></span></button>
                  </div>
               </div>
               <?php
            }
            echo '<div class="d-flex w-100 justify-content-center">';
            echo '<div class="d-lg-none">';
            $template->loadLayout('logo');
            echo '</div>';
            AstroidMenu::getMenu($header_menu, $navClass, true, $odd_menu_items, 'stacked', $navWrapperClass);
            echo '</div>';
            if ($enable_offcanvas) {
               ?>
               <div class="d-flex justify-content-end">
                  <div class="header-offcanvas-trigger burger-menu-button align-self-center <?php echo $offcanvas_togglevisibility; ?>" data-offcanvas="#astroid-offcanvas" data-effect="<?php echo $offcanvas_animation; ?>">
                     <button type="button" class="button">
                        <span class="box">
                           <span class="inner"></span>
                        </span>
                     </button>
                  </div>
               </div>
               <?php
            }
            echo '</div>';
            // header nav ends
            // header block starts
            if ($block_2_type == 'position') {
               echo '<div class="w-100 header-block-item d-none d-lg-flex justify-content-center py-3">';
               echo $template->renderModulePosition($block_2_position, 'xhtml');
               echo '</div>';
            }
            if ($block_2_type == 'custom') {
               echo '<div class="w-100 header-block-item d-none d-lg-flex justify-content-center py-3">';
               echo $block_2_custom;
               echo '</div>';
            }
            // header block ends
         }
         if ($mode == 'divided') {
            echo '<div class="w-100 d-flex justify-content-center">';
            ?>
            <?php if (!empty($header_mobile_menu)) { ?>
               <div class="d-flex d-lg-none justify-content-start">
                  <div class="header-mobilemenu-trigger d-lg-none burger-menu-button align-self-center" data-offcanvas="#astroid-mobilemenu" data-effect="mobilemenu-slide">
                     <button class="button" type="button"><span class="box"><span class="inner"></span></span></button>
                  </div>
               </div>
               <?php
            }
            if (!empty($block_1_type)) {
               echo '<div class="d-flex w-100 justify-content-center justify-content-lg-start">';
            } else {
               echo '<div class="d-flex w-100 justify-content-center py-3">';
            }
            $template->loadLayout('logo');
            echo '</div>';

            // header block starts
            if ($block_1_type == 'position') {
               echo '<div class="d-none d-lg-flex w-100 header-block-item justify-content-end py-3 align-items-center">';
               echo $template->renderModulePosition($block_1_position, 'xhtml');
               echo '</div>';
            }
            if ($block_1_type == 'custom') {
               echo '<div class="d-none d-lg-flex w-100 header-block-item justify-content-end py-3 align-items-center">';
               echo $block_1_custom;
               echo '</div>';
            }
            // header block ends

            if ($enable_offcanvas) {
               ?>
               <div class="d-flex justify-content-end">
                  <div class="header-offcanvas-trigger burger-menu-button align-self-center <?php echo $offcanvas_togglevisibility; ?>" data-offcanvas="#astroid-offcanvas" data-effect="<?php echo $offcanvas_animation; ?>">
                     <button type="button" class="button">
                        <span class="box">
                           <span class="inner"></span>
                        </span>
                     </button>
                  </div>
               </div>
               <?php
            }
            echo '</div>';
            // header nav starts -->
            echo '<div class="w-100 d-none d-lg-flex">';
            echo '<div class="d-flex justify-content-start py-3 flex-grow-1">';
            AstroidMenu::getMenu($header_menu, $navClassLeft, null, 'left', 'stacked', $navWrapperClass);
            echo '</div>';
            // header nav ends
            // header block starts
            if ($block_2_type == 'position') {
               echo '<div class="d-flex header-block-item justify-content-end py-3 align-items-center">';
               echo $template->renderModulePosition($block_2_position, 'xhtml');
               echo '</div>';
            }
            if ($block_2_type == 'custom') {
               echo '<div class="d-flex header-block-item justify-content-end py-3 align-items-center">';
               echo $block_2_custom;
               echo '</div>';
            }
            echo '</div>';
            // header block ends
         }
         ?>
      </div>
   </div>
</header>