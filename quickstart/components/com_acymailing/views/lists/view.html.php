<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php


class listsViewLists extends acymailingView{
	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){
		global $Itemid;
		$config = acymailing_config();

		$menu = acymailing_getMenu();

		if(empty($menu)) {
			acymailing_enqueueMessage(acymailing_translation('ACY_NOTALLOWED'));
			acymailing_redirect('index.php');
		}

		$selectedLists = 'all';

		if(is_object($menu)){
			$menuparams = new acyParameter($menu->params);

			$this->listsintrotext = $menuparams->get('listsintrotext');
			$this->listsfinaltext = $menuparams->get('listsfinaltext');
			$selectedLists = $menuparams->get('lists', 'all');

			$document = JFactory::getDocument();
			if($menuparams->get('menu-meta_description')) $document->setDescription($menuparams->get('menu-meta_description'));
			if($menuparams->get('menu-meta_keywords')) acymailing_addMetadata('keywords', $menuparams->get('menu-meta_keywords'));
			if($menuparams->get('robots')) acymailing_addMetadata('robots', $menuparams->get('robots'));
			if($menuparams->get('page_title')) acymailing_setPageTitle($menuparams->get('page_title'));
		}

		if(empty($menuparams)){
			acymailing_addBreadcrumb(acymailing_translation('MAILING_LISTS'));
		}

		$document = JFactory::getDocument();
		$link = '&format=feed&limitstart=';
		if($config->get('acyrss_format') == 'rss' || $config->get('acyrss_format') == 'both'){
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(acymailing_route($link.'&type=rss'), 'alternate', 'rel', $attribs);
		}
		if($config->get('acyrss_format') == 'atom' || $config->get('acyrss_format') == 'both'){
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(acymailing_route($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

		$listsClass = acymailing_get('class.list');
		$allLists = $listsClass->getLists('', $selectedLists);

		if(acymailing_level(1)){
			$allLists = $listsClass->onlyCurrentLanguage($allLists);
		}

		$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;
		$this->rows = $allLists;
		$this->item = $myItem;
	}
}
