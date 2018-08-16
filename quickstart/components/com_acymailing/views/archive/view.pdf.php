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


class archiveViewArchive extends acymailingView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();

	}

	function view(){

			$mailid = acymailing_getCID('mailid');

		if(empty($mailid)){
			$query = 'SELECT m.`mailid` FROM `#__acymailing_list` as l LEFT JOIN `#__acymailing_listmail` as lm ON l.listid=lm.listid LEFT JOIN `#__acymailing_mail` as m on lm.mailid = m.mailid';
			$query .= ' WHERE l.`visible` = 1 AND l.`published` = 1 AND m.`visible`= 1 AND m.`published` = 1';
			if(!empty($listid)) $query .= ' AND l.`listid` = '.(int) $listid;
			$query .= ' ORDER BY m.`mailid` DESC LIMIT 1';
			$mailid = acymailing_loadResult($query);

			if(empty($mailid)) return acymailing_raiseError(E_ERROR,  404, 'Newsletter not found');
		}

		$access_sub = true;

			$mailClass = acymailing_get('helper.mailer');
			$mailClass->loadedToSend = false;
			$oneMail = $mailClass->load($mailid);

			if(empty($oneMail->mailid)){
				return acymailing_raiseError(E_ERROR,  404, 'Newsletter not found : '.$mailid );
			}

			if(!$access_sub OR !$oneMail->published OR !$oneMail->visible){
				$key = acymailing_getVar('string', 'key');
				if(empty($key) OR $key !== $oneMail->key){
					acymailing_enqueueMessage('You can not have access to this e-mail','error');
					acymailing_redirect(acymailing_completeLink('lists',false,true));
					return false;
				}
			}

		$currentEmail = acymailing_currentUserEmail();
		if(!empty($currentEmail)){
			$userClass = acymailing_get('class.subscriber');
			$receiver = $userClass->get($currentEmail);
		}else{
			$receiver = new stdClass();
			$receiver->name = acymailing_translation('VISITOR');
		}

		$oneMail->sendHTML = true;
		acymailing_trigger('acymailing_replaceusertags', array(&$oneMail, &$receiver, false));

		acymailing_setPageTitle($oneMail->subject );

		if(!empty($oneMail->text)) echo nl2br($mailClass->textVersion($oneMail->text,false));
			else echo nl2br($mailClass->textVersion($oneMail->body,true));

	}
}
