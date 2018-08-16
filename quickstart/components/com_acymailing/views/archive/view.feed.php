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

jimport( 'joomla.application.component.view');
class archiveViewArchive extends acymailingView
{
	function display($tpl = null){
		$doc	= JFactory::getDocument();
		$menu = acymailing_getMenu();
		if (is_object( $menu )) {
			$menuparams = new acyParameter( $menu->params );
		}
 		$listid = acymailing_getCID('listid');
			if(empty($listid) AND !empty($menuparams)){
				$listid = $menuparams->get('listid');
			}
		$doc->link = acymailing_completeLink('archive&listid='.intval($listid));
		 $listClass = acymailing_get('class.list');
 		if(empty($listid)){
				return acymailing_raiseError(E_ERROR,  404, 'Mailing List not found' );
			}
			$oneList = $listClass->get($listid);
			if(empty($oneList->listid)){
				return acymailing_raiseError(E_ERROR,  404, 'Mailing List not found : '.$listid );
			}
			if(!acymailing_isAllowed($oneList->access_sub) || !$oneList->published || !$oneList->visible){
				return acymailing_raiseError(E_ERROR,  404, acymailing_translation('ACY_NOTALLOWED') );
			}

		$config = acymailing_config();
		$filters = array();
		$filters[] = 'a.type = \'news\'';
		$filters[] = 'a.published = 1';
		$filters[] = 'a.visible = 1';
		$filters[] = 'c.listid = '.$oneList->listid;
		$query = 'SELECT a.*';
		$query .= ' FROM '.acymailing_table('listmail').' as c';
		$query .= ' LEFT JOIN '.acymailing_table('mail').' as a on a.mailid = c.mailid ';
		$query .= ' WHERE ('.implode(') AND (',$filters).')';
		$query .= ' ORDER BY a.'.$config->get('acyrss_order','senddate').' '.($config->get('acyrss_order','senddate') == 'subject' ? 'ASC' : 'DESC');
		$query .= ' LIMIT '.$config->get('acyrss_element','20');
		$rows = acymailing_loadObjectList($query);
		$doc->title = $config->get('acyrss_name','');
		$doc->description = $config->get('acyrss_description','');

		$receiver = new stdClass();
		$receiver->name = acymailing_translation('VISITOR');
		$receiver->subid = 0;

		$mailClass = acymailing_get('helper.mailer');

		foreach ( $rows as $row )
		{
			$mailClass->loadedToSend = false;
			$oneMail = $mailClass->load($row->mailid);
			$oneMail->sendHTML = true;
			acymailing_trigger('acymailing_replaceusertags', array(&$oneMail, &$receiver, false));
			$title = $this->escape( $oneMail->subject );
			$title = html_entity_decode( $title );
			$link = acymailing_route('index.php?option=com_acymailing&amp;ctrl=archive&amp;task=view&amp;listid='.$oneList->listid.'-'.$oneList->alias.'&amp;mailid='.$row->mailid.'-'.$row->alias);

			$author			= $oneMail->userid;
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $oneMail->body;
			$item->date			= $oneMail->created;
			$item->category   	= $oneMail->type;
			$item->author		= $author;

			$doc->addItem( $item );
		}
	}
}

