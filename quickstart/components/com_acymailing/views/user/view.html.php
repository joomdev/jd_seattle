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


class UserViewUser extends acymailingView{
	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		global $Itemid;
		$this->Itemid = $Itemid;
		
		parent::display($tpl);
	}

	function modify(){
		$values = new stdClass();
		$values->show_page_heading = 0;

		$listsClass = acymailing_get('class.list');
		$subscriberClass = acymailing_get('class.subscriber');

		$menu = acymailing_getMenu();

		if(is_object($menu)){
			$menuparams = new acyParameter($menu->params);

			if(!empty($menuparams)){
				$this->introtext = $menuparams->get('introtext');
				$this->finaltext = $menuparams->get('finaltext');
				$this->dropdown = $menuparams->get('dropdown');

				if($menuparams->get('menu-meta_description')) acymailing_addMetadata('description', $menuparams->get('menu-meta_description'));
				if($menuparams->get('menu-meta_keywords')) acymailing_addMetadata('keywords', $menuparams->get('menu-meta_keywords'));
				if($menuparams->get('robots')) acymailing_addMetadata('robots', $menuparams->get('robots'));
				if($menuparams->get('page_title')) acymailing_setPageTitle($menuparams->get('page_title'));

				$values->suffix = $menuparams->get('pageclass_sfx', '');
				$values->page_heading = ACYMAILING_J16 ? $menuparams->get('page_heading') : $menuparams->get('page_title');
				$values->show_page_heading = ACYMAILING_J16 ? $menuparams->get('show_page_heading', 0) : $menuparams->get('show_page_title', 0);
			}
		}

		$subscriber = $subscriberClass->identify(true);
		if(empty($subscriber)){
			$subscription = $listsClass->getLists('listid');
			$subscriber = new stdClass();
			$subscriber->html = 1;
			$subscriber->subid = 0;
			$subscriber->key = 0;

			if(!empty($subscription)){
				foreach($subscription as $id => $onesub){
					$subscription[$id]->status = 1;
					if(!empty($menuparams) && strtolower($menuparams->get('listschecked', 'all')) != 'all' && !in_array($id, explode(',', $menuparams->get('listschecked', 'all')))){
						$subscription[$id]->status = 0;
					}
				}
			}

			acymailing_addBreadcrumb(acymailing_translation('SUBSCRIPTION'));
			if(empty($menu)) acymailing_setPageTitle(acymailing_translation('SUBSCRIPTION'));
		}else{
			$subscription = $subscriberClass->getSubscription($subscriber->subid, 'listid');

			acymailing_addBreadcrumb(acymailing_translation('MODIFY_SUBSCRIPTION'));
			if(empty($menu)) acymailing_setPageTitle(acymailing_translation('MODIFY_SUBSCRIPTION'));
		}
		if(!empty($subscriber->email)) $subscriber->email = acymailing_punycode($subscriber->email, 'emailToUTF8');

		acymailing_initJSStrings();

		if(!empty($menuparams) AND strtolower($menuparams->get('lists', 'all')) != 'all'){
			$visibleLists = strtolower($menuparams->get('lists', 'all'));
			if($visibleLists == 'none'){
				$subscription = array();
			}else{
				$newSubscription = array();
				$visiblesListsArray = explode(',', $visibleLists);
				foreach($subscription as $id => $onesub){
					if(in_array($id, $visiblesListsArray)) $newSubscription[$id] = $onesub;
				}
				$subscription = $newSubscription;
			}
		}


		if(!acymailing_level(3)){
			if(!empty($menuparams) && strtolower($menuparams->get('customfields', 'default')) != 'default'){
				$fieldsToDisplay = strtolower($menuparams->get('customfields', 'default'));
				$this->fieldsToDisplay = $fieldsToDisplay;
			}else{
				$this->fieldsToDisplay = 'default';
			}
		}

		$hiddenLists = '';
		if(!empty($menuparams)){
			$hiddenLists = trim($menuparams->get('hiddenlists', 'None'));
			if(empty($subscriber)){
				$allLists = $listsClass->getLists('listid');
			}else $allLists = $subscriberClass->getSubscription($subscriber->subid, 'listid');

			$hiddenListsArray = array();
			if(strpos($hiddenLists, ',') || is_numeric($hiddenLists)){
				$allhiddenlists = explode(',', $hiddenLists);
				foreach($allLists as $oneList){
					if(!$oneList->published || !in_array($oneList->listid, $allhiddenlists)) continue;
					$hiddenListsArray[] = $oneList->listid;
					unset($subscription[$oneList->listid]);
				}
			}elseif(strtolower($hiddenLists) == 'all'){
				$subscription = array();
				foreach($allLists as $oneList){
					if(!empty($oneList->published)) $hiddenListsArray[] = $oneList->listid;
				}
			}
			$hiddenLists = implode(',', $hiddenListsArray);
		}

		$defaultSubscription = $subscription;
		$forceLists = acymailing_getVar('string', 'listid', '');
		if(!empty($forceLists)){
			$subscription = array();
			$forceLists = explode(',', $forceLists);
			foreach($forceLists as $oneList){
				if(!empty($defaultSubscription[$oneList])){
					$subscription[$oneList] = $defaultSubscription[$oneList];
				}
			}
		}
		$forceHiddenLists = acymailing_getVar('string', 'hiddenlist', '');
		if(!empty($forceHiddenLists)){
			$forceHiddenLists = explode(',', $forceHiddenLists);
			$tmpList = array();
			$defaultHidden = explode(',', $hiddenLists);
			foreach($forceHiddenLists as $oneList){
				if(!empty($defaultSubscription[$oneList]) || in_array($oneList, $defaultHidden)){
					$tmpList[] = $oneList;
				}
			}
			$hiddenLists = implode(',', $tmpList);
		}

		$displayLists = false;
		foreach($subscription as $oneSub){
			if(!empty($oneSub->published) AND $oneSub->visible){
				$displayLists = true;
				break;
			}
		}

		$this->hiddenlists = $hiddenLists;
		$this->values = $values;
		$this->status = acymailing_get('type.festatus');
		$this->subscription = $subscription;
		$this->subscriber = $subscriber;
		$this->displayLists = $displayLists;
		$this->config = acymailing_config();
	}

	function saveunsub(){
		$subscriberClass = acymailing_get('class.subscriber');
		$subscriber = $subscriberClass->identify();
		$this->subscriber = $subscriber;

		$listid = acymailing_getVar('int', 'listid');
		if(!empty($listid)){
			$listClass = acymailing_get('class.list');
			$mylist = $listClass->get($listid);
			$this->list = $mylist;
		}
	}


	function unsub(){

		$subscriberClass = acymailing_get('class.subscriber');
		$config = acymailing_config();
		$this->config = $config;

		$subscriber = $subscriberClass->identify();
		$this->subscriber = $subscriber;

		$mailid = acymailing_getVar('int', 'mailid');
		$this->mailid = $mailid;

		$query = 'SELECT l.listid, l.name FROM '.acymailing_table('list').' as l';
		$query .= ' JOIN '.acymailing_table('listsub').' AS ls ON ls.listid = l.listid AND ls.subid = '.acymailing_getVar('int', 'subid');
		$query .= ' WHERE l.type = \'list\' AND (ls.unsubdate < ls.subdate OR ls.unsubdate IS NULL) AND l.visible = 1 AND l.published = 1';
		$query .= ' ORDER BY l.ordering ASC';

		$otherSubscriptions = acymailing_loadObjectList($query);

		$query = 'SELECT lm.listid FROM '.acymailing_table('mail').' AS m INNER JOIN '.acymailing_table('listmail').' AS lm ON m.mailid = lm.mailid WHERE m.mailid = '.acymailing_getVar('int', 'mailid');
		$listsToDeny = acymailing_loadObjectList($query);

		if(!empty($otherSubscriptions)){
			$i = 0;
			foreach($otherSubscriptions as $anotherSubscription){
				foreach($listsToDeny as $oneListToDeny){
					if($anotherSubscription->listid == $oneListToDeny->listid){
						unset($otherSubscriptions[$i]);
						continue;
					}
				}
				$i++;
			}
		}

		$this->otherSubscriptions = $otherSubscriptions;

		$replace = array();
		$replace['{list:name}'] = '';
		foreach($subscriber as $oneProp => $oneVal){
			$replace['{user:'.$oneProp.'}'] = $oneVal;
			$replace['{user:'.$oneProp.' | ucwords}'] = ucwords($oneVal);
		}

		if(!empty($mailid)){
			$classListmail = acymailing_get('class.listmail');
			$lists = $classListmail->getLists($mailid);
			$this->lists = $lists;
			if(!empty($lists)){
				$oneList = reset($lists);
				foreach($oneList as $oneProp => $oneVal){
					$replace['{list:'.$oneProp.'}'] = $oneVal;
				}
			}

			$mailClass = acymailing_get('class.mail');
			$news = $mailClass->get($mailid);
			if(!empty($news)){
				foreach($news as $oneProp => $oneVal){
					if(!is_string($oneVal)) continue;
					$replace['{mail:'.$oneProp.'}'] = $oneVal;
				}
			}
		}

		$intro = str_replace('UNSUB_INTRO', acymailing_translation('UNSUB_INTRO'), $config->get('unsub_intro', 'UNSUB_INTRO'));
		$intro = ' <div class="unsubintro" > '.nl2br(str_replace(array_keys($replace), $replace, $intro)).'</div> ';
		$this->intro = $intro;

		$this->replace = $replace;


		$unsubtext = str_replace(array_keys($replace), $replace, acymailing_translation('UNSUBSCRIBE'));
		acymailing_addBreadcrumb($unsubtext);

		acymailing_setPageTitle($unsubtext);
	}
}
