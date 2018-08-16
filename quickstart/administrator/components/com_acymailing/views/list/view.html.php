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


class ListViewList extends acymailingView{
	
	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){
		$config = acymailing_config();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();

		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'a.ordering', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'asc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$selectedCreator = acymailing_getUserVar($paramBase."filter_creator", 'filter_creator', 0, 'int');
		$selectedCategory = acymailing_getUserVar($paramBase."filter_category", 'filter_category', 0, 'string');

		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = "a.name LIKE $searchVal OR a.description LIKE $searchVal OR a.listid LIKE $searchVal";
		}
		$filters[] = "a.type = 'list'";
		if(!empty($selectedCreator)) $filters[] = 'a.userid = '.$selectedCreator;
		if(!empty($selectedCategory)) $filters[] = 'a.category = '.acymailing_escapeDB($selectedCategory);

		if(!acymailing_isAdmin()) {
			$listClass = acymailing_get('class.list');
			$lists = $listClass->getFrontendLists('listid');

			$filters[] = 'listid IN ('.implode(',', array_keys($lists)).')';
		}

		$query = 'SELECT a.*, d.'.$this->cmsUserVars->name.' as creatorname, d.'.$this->cmsUserVars->username.' AS username, d.'.$this->cmsUserVars->email.' AS email';
		$query .= ' FROM '.acymailing_table('list').' as a';
		$query .= ' LEFT JOIN '.acymailing_table($this->cmsUserVars->table, false).' as d on a.userid = d.'.$this->cmsUserVars->id;
		$query .= ' WHERE ('.implode(') AND (', $filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);

		$queryCount = 'SELECT COUNT(a.listid) FROM  '.acymailing_table('list').' as a';
		$queryCount .= ' WHERE ('.implode(') AND (', $filters).')';

		$pageInfo->elements->total = acymailing_loadResult($queryCount);

		$listids = array();
		foreach($rows as $oneRow){
			$listids[] = $oneRow->listid;
		}

		$subscriptionresults = array();
		if(!empty($listids)){
			$querySubscription = 'SELECT count(subid) as total,listid,status FROM '.acymailing_table('listsub').' WHERE listid IN ('.implode(',', $listids).') GROUP BY listid, status';
			$countresults = acymailing_loadObjectList($querySubscription);
			foreach($countresults as $oneResult){
				$subscriptionresults[$oneResult->listid][intval($oneResult->status)] = $oneResult->total;
			}
		}

		foreach($rows as $i => $oneRow){
			$rows[$i]->nbsub = intval(@$subscriptionresults[$oneRow->listid][1]);
			$rows[$i]->nbunsub = intval(@$subscriptionresults[$oneRow->listid][-1]);
			$rows[$i]->nbwait = intval(@$subscriptionresults[$oneRow->listid][2]);
		}

		$pageInfo->elements->page = count($rows);

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		if(acymailing_isAdmin()) {
			$acyToolbar = acymailing_get('helper.toolbar');
			if (acymailing_isAllowed($config->get('acl_lists_filter', 'all'))) {
				$acyToolbar->link(acymailing_completeLink('filter'), acymailing_translation('ACY_FILTERS'), 'filter');
				$acyToolbar->divider();
			}

			if (acymailing_isAllowed($config->get('acl_lists_manage', 'all'))) $acyToolbar->add();
			if (acymailing_isAllowed($config->get('acl_lists_manage', 'all'))) $acyToolbar->edit();
			if (acymailing_isAllowed($config->get('acl_lists_delete', 'all'))) $acyToolbar->delete();
			if (acymailing_isAllowed($config->get('acl_lists_manage', 'all')) || acymailing_isAllowed($config->get('acl_lists_manage', 'all')) || acymailing_isAllowed($config->get('acl_lists_delete', 'all'))) $acyToolbar->divider();
			$acyToolbar->help('list-listing');
			$acyToolbar->setTitle(acymailing_translation('LISTS'), 'list');
			$acyToolbar->display();
		}

		$order = new stdClass();
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}

		$filters = new stdClass();
		$creatorfilterType = acymailing_get('type.creatorfilter');
		$creatorfilterType->type = 'list';
		$filters->creator = $creatorfilterType->display('filter_creator', $selectedCreator, 'list');
		$listcategoryType = acymailing_get('type.categoryfield');
		$filters->category = $listcategoryType->getFilter('list', 'filter_category', $selectedCategory, ' onchange="document.adminForm.submit();"');

		$this->config = $config;
		$this->filters = $filters;
		$this->order = $order;
		$toggleClass = acymailing_get('helper.toggle');
		$this->toggleClass = $toggleClass;
		$this->rows = $rows;
		$this->pageInfo = $pageInfo;
		$this->pagination = $pagination;
	}

	function form(){
		$listClass = acymailing_get('class.list');
		$listid = acymailing_getCID('listid');

		if(!empty($listid)){
			$list = $listClass->get($listid);

			if(empty($list->listid)){
				acymailing_display('List '.$listid.' not found', 'error');
				$listid = 0;
			}
		}

		if(empty($listid)){
			$list = new stdClass();
			$list->visible = 1;
			$list->description = '';
			$list->category = '';
			$list->published = 1;
			$list->creatorname = acymailing_currentUserName();
			$list->access_manage = 'none';
			$list->access_sub = 'all';
			$list->languages = 'all';
			$colors = array('#3366ff', '#7240A4', '#7A157D', '#157D69', '#ECE649');
			$list->color = $colors[rand(0, count($colors) - 1)];
		}

		$editor = acymailing_get('helper.editor');
		$editor->name = 'editor_description';
		$editor->content = $list->description;
		$editor->setDescription();

		$script = '
			document.addEventListener("DOMContentLoaded", function(){
				acymailing.submitbutton = function(pressbutton) {
					if (pressbutton == \'cancel\') {
						acymailing.submitform(pressbutton,document.adminForm);
						return;
					}
					if(window.document.getElementById("name").value.length < 2){alert(\''.acymailing_translation('ENTER_TITLE', true).'\'); return false;}';
		$script .= $editor->jsCode();
		$script .= 'acymailing.submitform(pressbutton,document.adminForm);
				};
			 }); ';
		$script .= 'function affectUser(idcreator,name,email){
			window.document.getElementById("creatorname").innerHTML = name;
			window.document.getElementById("listcreator").value = idcreator;
		}';


		acymailing_addScript(true, $script);

		if(acymailing_isAdmin()) {
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->addButtonOption('apply', acymailing_translation('ACY_APPLY'), 'apply', false);
			$acyToolbar->save();
			$acyToolbar->cancel();
			$acyToolbar->divider();
			$acyToolbar->help('list-form');
			$acyToolbar->setTitle(acymailing_translation('LIST'), 'list&task=edit&listid=' . $listid);
			$acyToolbar->display();
		}

		$colorBox = acymailing_get('type.color');
		$this->colorBox = $colorBox;
		if(acymailing_level(1)){
			$this->welcomeMsg = acymailing_get('type.welcome');
			$this->languages = acymailing_get('type.listslanguages');
		}
		$unsubMsg = acymailing_get('type.unsub');
		$this->unsubMsg = $unsubMsg;
		$this->list = $list;
		$this->editor = $editor;
	}
}
