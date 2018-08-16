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
include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'view.html.php');

class NotificationViewNotification extends NewsletterViewNewsletter{
	var $type = 'joomlanotification';
	var $ctrl = 'notification';
	var $nameListing = 'JOOMLA_NOTIFICATIONS';
	var $nameForm = 'JOOMLA_NOTIFICATIONS';
	var $doc = 'joomlanotification';
	var $icon = 'joomlanotification';
	var $filters = array();


	function listing(){
		$config = acymailing_config();

		if(!class_exists('plgSystemAcymailingClassMail')){
			$warning_msg = acymailing_translation('ACY_WARNINGOVERRIDE_DISABLED_1').' <a href="'.acymailing_completeLink('cpanel').'">'.acymailing_translation_sprintf('ACY_WARNINGOVERRIDE_DISABLED_2', ' acymailingclassmail (Override Joomla mailing system plugin)').'</a>';
			acymailing_enqueueMessage($warning_msg, 'notice');
		}

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->elements = new stdClass();
		$pageInfo->limit = new stdClass();
		$this->filters[] = '`type` = '.acymailing_escapeDB($this->type);

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'mailid', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'asc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'asc') $pageInfo->filter->order->dir = 'desc';

		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$this->filters[] = "subject LIKE $searchVal OR body LIKE $searchVal";
		}

		$filters = new stdClass();
		if(ACYMAILING_J16){
			$pageInfo->category = acymailing_getUserVar($paramBase.".category", 'category', '0', 'string');
			if(!empty($pageInfo->category)){
				$this->filters[] = "alias LIKE '".acymailing_getEscaped($pageInfo->category, true)."-%'";
			}
			$catvalues = array();
			$catvalues[] = acymailing_selectOption('0', acymailing_translation('ACY_ALL'));
			$catvalues[] = acymailing_selectOption('joomla', 'Joomla!');
			$catvalues[] = acymailing_selectOption('jomsocial', 'JomSocial');
			$catvalues[] = acymailing_selectOption('seblod', 'SEBLOD');
			$filters->category = acymailing_select($catvalues, 'category', 'size="1" style="width:150px" onchange="acymailing.submitform();"', 'value', 'text', $pageInfo->category);
		}

		$query = 'SELECT mailid, subject, alias, fromname, published, fromname, fromemail, replyname, replyemail FROM #__acymailing_mail WHERE ('.implode(') AND (', $this->filters).')';

		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);

		$queryCount = 'SELECT count(mailid) FROM #__acymailing_mail WHERE ('.implode(') AND (', $this->filters).')';
		$pageInfo->elements->total = acymailing_loadResult($queryCount);
		$pageInfo->elements->page = count($rows);
		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$acyToolbar = acymailing_get('helper.toolbar');
		$acyToolbar->custom('preview', acymailing_translation('ACY_PREVIEW'), 'search', true);
		$acyToolbar->edit();
		$acyToolbar->delete();

		$acyToolbar->divider();
		$acyToolbar->help($this->doc);
		$acyToolbar->setTitle(acymailing_translation($this->nameListing), $this->ctrl);
		$acyToolbar->display();

		$toggleClass = acymailing_get('helper.toggle');
		$this->toggleClass = $toggleClass;
		$this->pageInfo = $pageInfo;
		$this->config = $config;
		$this->rows = $rows;
		$this->pagination = $pagination;
		$this->filters = $filters;
	}

	function form(){
		return parent::form();
	}

	function preview(){
		return parent::preview();
	}

}
