<?php
/**
 * @package     JD Admin Bar
 * @description Enables Top Bar for your Joomla site
 * @Help		www.joomdev.com/forum
 * @copyright   Copyright (C) 2009 - 2020 JoomDev. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

class PlgSystemJDAdminBar extends JPlugin
{
	public $modules = [];
	public function onAfterDispatch() {
		$doc = Factory::getDocument();
		$user = Factory::getUser();
		if (Factory::getApplication()->isClient('administrator') || !in_array('8',$user->groups)) {
			// Not running in Admin.
			// By default only visible to Super Admin's.
			return true;
		}
			// Adding necessary scripts and css.
			$doc->addStyleSheet(Uri::root().'plugins/system/jdadminbar/assets/css/admin-bar.css', ['version' => $doc->getMediaVersion()]);
			$doc->addScript(Uri::root().'plugins/system/jdadminbar/assets/js/admin-bar.js', ['version' => $doc->getMediaVersion()]);
			$doc->addScript(Uri::root().'plugins/system/jdadminbar/assets/js/hoverintent-js.min.js', ['version' => $doc->getMediaVersion()]);
	}

	public function onRenderModule($module){
		$this->modules[] = $module;
	}
	
	public function onAfterRender(){
		// Loading some extra languages so we don't actually have to write the language.
		$language = Factory::getLanguage();
		$language->load('com_config', JPATH_SITE);
		$language->load('com_cpanel', JPATH_ADMINISTRATOR);
		$language->load('com_menus', JPATH_ADMINISTRATOR);
		$language->load('com_admin', JPATH_ADMINISTRATOR);
		$language->load('com_content', JPATH_ADMINISTRATOR);
		$language->load('com_modules', JPATH_ADMINISTRATOR);
		$language->load('com_users', JPATH_ADMINISTRATOR);
		if(JVERSION < 4) {
			// Joomla 3 or whatever.
			$dashboardtext 		= 	JText::_('COM_CPANEL_LINK_DASHBOARD');
			$globalconfigtext	=	JText::_('COM_CPANEL_LINK_GLOBAL_CONFIG');
			$sysinfotext		= 	JText::_('COM_CPANEL_LINK_SYSINFO');
		} else {
			// Joomla 4
			$dashboardtext 		= 	JText::_('COM_CPANEL_DASHBOARD_BASE_TITLE');
			$globalconfigtext	=	JText::_('COM_ADMIN_HELP_SITE_GLOBAL_CONFIGURATION');
			$sysinfotext		= 	JText::_('COM_ADMIN');
		}
		
		$user 				= 		Factory::getUser();
		if (Factory::getApplication()->isClient('administrator') || !in_array('8',$user->groups)) {
			// Not running in Admin.
			// By default only visible to Super Admin's.
			return true;
		}
		$app 				= 		Factory::getApplication();
		$document 			= 		Factory::getDocument();
		$uri 				= 		Uri::getInstance();
		$config 			= 		Factory::getConfig();
		$adminnurl			=		Uri::root()."administrator/";
		$links				= 		$this->GetCurrentPageLink();
		$menulinks 			= 		'';
		$menu 				= 		$app->getMenu();
		$active 			= 		$menu->getActive();
		$ItemId 			= 		$active->id;
		
		// Current menu type link
		if($links->url) {
			$menulinks 		.= 	$this->GetlinkHtml($adminnurl.$links->url,JText::_('JACTION_EDIT').' '.ucfirst($links->type));
		}
		
		// Current Menu link
		if($ItemId) {
			$menulinks 		.= 	$this->GetlinkHtml($adminnurl."index.php?option=com_menus&task=item.edit&id={$ItemId}",JText::_('JACTION_EDIT').' '.JText::_('COM_MENUS_ITEM_FIELD_ALIAS_MENU_LABEL'));
		}

		$modulelinkshtmllinks = '';
		foreach ($this->modules as $module) {
			// only render the module if there is a valid ID associated.
			// somehow rendermodle id rednered without an Id as well.
			if(is_numeric($module->id)) {
				$modulelinkshtmllinks 	.= $this->GetlinkHtml($adminnurl . "index.php?option=com_modules&task=module.edit&id={$module->id}", "{$module->title} <span>(" . (empty($module->position) ? '<em>ID: ' . $module->id . '</em>' : '<em>' . JText::_('COM_MODULES_HEADING_POSITION') . ': ' . $module->position . '</em>') . ")</span>");
			}
		}

		if(isset($modulelinkshtmllinks)) {
			$modlinks 			 = 	new StdClass();
			$modlinks->name	 	 = 	JText::_('COM_MODULES_MODULES');
			$modlinks->link 	 = 	$adminnurl.'index.php?option=com_modules';
			$modlinks->html		 = 	$modulelinkshtmllinks;
			$modulelinks  		=	$this->GetSectionhtml($modlinks);
		}
		
		
		// Templates
		// Check if current menu item has a specially assigned template.
		if($active->template_style_id == 0) {
			$templateid = $app->getTemplate('template')->id;
		} else {
			$templateid = $active->template_style_id;
		}
			$menulinks	.= $this->GetlinkHtml($adminnurl."index.php?option=com_templates&task=style.edit&id={$templateid}",JText::_('COM_CONFIG_TEMPLATE_SETTINGS'));
			
		$outputhtml = "<!-- JD Admin Bar Plugin by JoomDev.com start --><div id='wpadminbar' class='nojq nojs'><div class='quicklinks'  role='navigation' aria-label='Toolbar'><ul class='ab-top-menu'><li class='menupop'>";
							
		$dlinks   = $this->GetlinkHtml($adminnurl,$dashboardtext);
		$dlinks  .= $this->GetlinkHtml($adminnurl."index.php?option=com_content",JText::_('COM_ADMIN_HELP_CONTENT_ARTICLE_MANAGER'));
		$dlinks  .= $this->GetlinkHtml($adminnurl."index.php?option=com_modules",JText::_('COM_ADMIN_HELP_EXTENSIONS_MODULE_MANAGER'));
		$dlinks  .= $this->GetlinkHtml($adminnurl."index.php?option=com_menus",JText::_('COM_MENUS_VIEW_MENUS_TITLE'));
		$dlinks  .= $this->GetlinkHtml($adminnurl."index.php?option=com_users",JText::_('COM_ADMIN_HELP_USERS_USER_MANAGER'));
		$dlinks  .= $this->GetlinkHtml($adminnurl."index.php?option=com_admin&view=sysinfo",$sysinfotext);
		$dlinks  .= $this->GetlinkHtml($adminnurl."index.php?option=com_config",$globalconfigtext);
		
		// Defaultlinks
		$defaultlinks		 = 	new StdClass();
		$defaultlinks->name	 = 	$config->get( 'sitename' );
		$defaultlinks->link  = 	$adminnurl;
		$defaultlinks->html	 = 	$dlinks;
		$outputhtml  		.=	$this->GetSectionhtml($defaultlinks);
		
		$outputhtml  		.= $menulinks;
		$newhtmllinks 		 = $this->GetlinkHtml($adminnurl."index.php?option=com_content&task=article.add",JText::_('COM_CONTENT_FIELDS_TYPE_MODAL_ARTICLE'));
		$newhtmllinks 		.= $this->GetlinkHtml($adminnurl."index.php?option=com_categories&task=category.add&extension=com_content",JText::_('JCATEGORY'));
		$newhtmllinks 		.= $this->GetlinkHtml($adminnurl."index.php?option=com_modules&view=select",JText::_('COM_MODULES_MODULE'));
		$newhtmllinks 		.= $this->GetlinkHtml($adminnurl."index.php?option=com_menus&view=item&layout=edit",JText::_('COM_MENUS_ITEM_FIELD_ALIAS_MENU_LABEL'));
		$newhtmllinks 		.= $this->GetlinkHtml($adminnurl."index.php?option=com_users&task=user.add",JText::_('COM_USERS_FIELD_USER_ID_LABEL'));
		if(isset($links->newlink)) {
			$newhtmllinks 		.= $this->GetlinkHtml($adminnurl.$links->newlink,ucfirst($links->type));
		}
		// Let's Get New Link for the current view
		
		// Ability to Create new items;
		$newlinks 			 = 	new StdClass();
		$newlinks->name	 	 = 	JText::_('JNEW').' +';
		$newlinks->link 	 = 	'#';
		$newlinks->html		 = 	$newhtmllinks;
		$outputhtml  		.=	$this->GetSectionhtml($newlinks);
		
		// Links for all the modules 
		if(isset($modulelinks)) {
			$outputhtml 		.=	$modulelinks;
		}
		// Closing Div's
		$outputhtml			.= "</li></ul></div></div> <!-- JD Admin Bar Plugin by JoomDev.com end -->";
		if ($app->isClient('site')) {
			$buffer = Factory::getApplication()->getBody();
			$buffer = preg_replace_callback('/<body[^>]*>/siU', function ($matches) use ($outputhtml) {
				return $matches[0] . $outputhtml;
			}, $buffer);
			Factory::getApplication()->setBody($buffer);
		}
	}
	

	/*
	*	Function to figure out the links and return based
	*	on the same the links text assodicated
	*	This is custom based on a few components configured
	*	so far and will have more components in the future
	*/
	private static function GetCurrentPageLink() {
		$app 		= 		Factory::getApplication();
		// Let's figure out what the link is all about.
		//The process below is based on SEF links but works on non-sef as well.
		$menu 		= 		$app->getMenu();
		$active 	= 		$menu->getActive();
		$default 	=		$menu->getDefault();
		$ItemId 	= 		$active->id;
		$uri 		= 		Uri::getInstance();
		
		if($active->id == $default->id) {
			
			// Since Homepage doesn't have a url 
			// we have to get it from the active menu item.
			$link 		= 	$active->link;
			parse_str(str_replace('index.php?','',$link), $output);
			foreach($output as $key => $value) {
				$$key = $value;
			}
			
		} else {
			
			// Since this is NOT the homepage,
			// We get the link from the get request itself.
			$router = JSite::getRouter();
			$output = $router->parse($uri);
			foreach($output as $key => $value) {
				$$key = $value;
			}
		}
		
		
		// Blank variables for links above.
		$link = '';
		$linktext = '';
		switch ($option) {
			case 'com_jdbuilder':
				// only one view.
				if($view == 'page') {
					$link 		= 	"index.php?option={$option}&task={$view}.edit&id={$id}";
					$type		=	'page';
					$newlink	=	'index.php?option=com_jdbuilder&view=page&layout=edit';
				}
				$component	=	'JD Builder';
				break;
				
			// com_content A.K.A Articles
			case 'com_content':
				// category
				if($view == 'category') {
					$link 		= 	"index.php?option=com_categories&task={$view}.edit&id={$id}&extension=com_content";
					$type		=	"category";
					//$newlink	=	"index.php?option={$option}&task={$type}.add&extension=com_content";
				}
				
				// Article
				if($view == 'article') {
					$link 		= 	"index.php?option={$option}&task={$view}.edit&id={$id}";
					$type		=	"article";
					//$newlink	=	"index.php?option={$option}&task=$type.add";
				}
				
				$component	=	'Content';
				
				// Nothing for featured view yet!
				
				break;
				
			// com_k2
			case 'com_k2':
				// category
				if($view == 'itemlist' && $task == 'category' && (isset($id))) {
					$link 		= 	"index.php?option={$option}&view=category&cid={$id}";
					$type		=	"category";
					$newlink	=	"index.php?option={$option}&view=category";
				}
				
				// Item
				if($view == 'item') {
					$link 		= 	"index.php?option={$option}&view=item&cid={$id}";
					$type		=	"item";
					$newlink	=	"index.php?option={$option}&view=item";
				}
				
				$component	=	'K2';

				break;	
			
			// Phoca Cart
			case 'com_phocacart':
				// category
				if($view == 'category' && (isset($id))) {
					$link 		= 	"index.php?option={$option}&task=phocacartcategory.edit&{$id}";
					$type		=	"category";
					$newlink	=	"index.php?option={$option}&view=phocacartcategory.add";
				}
				
				// Product
				if($view == 'item') {
					$link 		= 	"index.php?option={$option}&task=phocacartitem.edit&id={$id}";
					$type		=	"item";
					$newlink	=	"index.php?option={$option}&task=phocacartitem.add";
				}
				
				$component	=	'Phoca Cart';

				break;
				
			// Phoca Gallery
			case 'com_phocagallery':
				// Category but link goes to managing images for the category.
				if($view == 'category' && (isset($id))) {
					$link 		= 	"index.php?option={$option}&view=phocagalleryimgs&filter_category_id={$id}";
					$type		=	"category";
					$newlink	=	"index.php?option={$option}&task=phocagalleryimg.edit&filter_category_id={$id}";
				}
			
				$component	=	'Phoca Gallery';

				break;
				
			// Phoca Download
			case 'com_phocadownload':
				// Category but link goes to managing downloads for the category.
				if($view == 'category' && (isset($id))) {
					$link 		= 	"index.php?option={$option}&view=phocadownloadfiles&filter_category_id=".(int)$id;
					$type		=	"Download(s)";
					// unable to auto select category here on the new download page.
					$newlink	=	"index.php?option={$option}&view=phocadownloadfile&layout=edit";
				}
				
				$component	=	'Phoca Cart';

				break;
			case 'com_contact':
				if($view == 'contact') {
					$link 		= 	"index.php?option={$option}&task={$view}.edit&id={$id}";
					$type		=	"contact";
					$newlink	=	"index.php?option={$option}&task=$type.add";
				}
				$component	=	'Contact';
				break; 
				
			case 'com_hikashop':
				// Category
				if((@$view == 'category' || @$ctrl == 'category') && (@$task == 'listing'|| @$layout == 'listing') && !(empty($cid))) {
					$link 		= 	"index.php?option={$option}&ctrl=category&task=edit&cid[]={$cid}";
					$type		=	'category';
					$newlink	=	"index.php?option={$option}&ctrl=category&task=edit";
				}
				
				// Product
				if(isset($ctrl) && ($ctrl == 'product' && $task == 'show')) {
					$link 		= 	"index.php?option={$option}&ctrl={$ctrl}&task=edit&cid[]={$cid}";
					$type		=	'product';
					$newlink	=	"index.php?option={$option}&ctrl={$ctrl}&task=edit";
				}
				
				$component		=	'Hikashop';
				break;
				
			case 'com_virtuemart':
				// manufacturer
				if($view == 'category' && !(empty($virtuemart_manufacturer_id))) {
					$link 		= 	"index.php?option={$option}&view=manufacturer&task=edit&virtuemart_manufacturer_id={$virtuemart_manufacturer_id}";
					$type		=	'manufacturer';
					$newlink	=	"index.php?option={$option}&view=manufacturer&task=edit";
				}
				
				// Category
				if($view == 'category' && !(empty($virtuemart_category_id))) {
					$link 		=	"index.php?option={$option}&view=category&task=edit&cid={$virtuemart_category_id}";
					$type		=	'category';
					$newlink	=	"index.php?option={$option}&view=category&task=edit";
				}
				
				// Product
				if($view == 'productdetails' && !(empty($virtuemart_product_id))) {
					$link 		= 	"index.php?option={$option}&view=product&task=edit&virtuemart_product_id={$virtuemart_product_id}";
					$type		=	'product';
					$newlink	=	"index.php?option={$option}&view=product&task=edit";
				}
				
				$component		=	'Virtuemart';
				
				break;
			case 'com_j2store':
				// Product (more like Article)
				if($view == 'products' && @$task == 'view') {
					// Joomla Article ID and J2Store Product ID isn't same.
					// We have to get it from the DB.
					$articleid	=	self::GetJ2StoreProductidbyArticleId($id);
					
					if($articleid) {
						$link 		= 	"index.php?option=com_content&task=article.edit&id={$articleid}";
					}
					
					$type		=	"product";
					$newlink	=	"index.php?option=com_content&task=article.add";
				}
				
				$component		=	'J2Store';
				
				break;

			case 'com_easyblog':
				// Single Post
				if($view == 'entry') {
					$link 		= 	"index.php?option={$option}&view=composer&tmpl=component&uid={$id}";
					$type		=	"post";
					$newlink	=	"index.php?option=com_easyblog&view=composer&tmpl=component";
				}
				$component		=	'EasyBlog';
				break;
				
			$component_sef	=	$option;
		}
		
		$links = new StdClass();
		$links->url 			= $link;
		$links->text 			= $linktext;
		$links->component_sef 	= $option;
		if(isset($component)) {
			$links->component 		= $component;
		}
		if(isset($newlink)) {
			$links->newlink 		= $newlink;
		}
		if(isset($type)) {
			$links->type 			= $type;
		}
		return $links;
	}
	
	/*
	*	function to return the html for a link.
	*	We can also manage classes and other stuff here
	*/	
	private static function GetlinkHtml($link, $text){
		$attr		=		"target='_blank'";
		$return  	= 		"<li class='menupop'>
				<a class='ab-item' {$attr} href='{$link}'>{$text}</a>
		</li>";
		return $return;
	}
	
	/*
	*	function to return the html for a section.
	*	includes ul li and other stuff,
	*/	
	private static function GetSectionhtml($obj){
		$return  	= 		"<li  class='menupop'>
            <a class='ab-item' aria-haspopup='true' target='_blank' href='{$obj->link}'>{$obj->name}</a>
            <div class='ab-sub-wrapper'>
               <ul class='ab-submenu'>
			   {$obj->html}
			   </ul>
            </div>
         </li>";
		return $return;
	}
	
	/*
	*	The Name of function is self explanatory
	*/
	private static function GetJ2StoreProductidbyArticleId($id) {
		// Assuming it's only going to be for com_content and 
		// J2 store doesn't go marriying other components.
		$db 	= Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('product_source_id'));
		$query->from($db->quoteName('#__j2store_products'));
		$query->where($db->quoteName('j2store_product_id') . ' = ' . $db->quote($id));
		$db->setQuery($query);
		return $articleid = $db->loadResult();
	}
}
