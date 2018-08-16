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

class unsubType extends acymailingClass{
	function __construct(){
		parent::__construct();
		$messages = acymailing_loadObjectList('SELECT `subject`, `mailid` FROM '.acymailing_table('mail').' WHERE `type`= \'unsub\'');

		$this->values = array();
		$this->values[] = acymailing_selectOption('0', acymailing_translation('NO_UNSUB_MESSAGE'));
		foreach($messages as $oneMessage){
			$this->values[] = acymailing_selectOption($oneMessage->mailid, '['.acymailing_translation('ACY_ID').' '.$oneMessage->mailid.'] '.$oneMessage->subject);
		}

		$js = "function changeMessage(idField,value){
			linkEdit = idField+'_edit';
			if(value>0){
				window.document.getElementById(linkEdit).onclick = function(){acymailing.openpopup('".acymailing_completeLink((acymailing_isAdmin() ? '' : 'front')."email&task=edit", true, true)."&mailid='+value, 800, 500);return false;};
				window.document.getElementById(linkEdit).style.display = 'inline';
			}else{
				window.document.getElementById(linkEdit).style.display = 'none';
			}
		}";
		acymailing_addScript(true, $js);

	}

	function display($value){
		$linkEdit = acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'email', true).'&amp;task=edit&amp;type=unsub&amp;mailid='.$value;
		$linkAdd = acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'email', true).'&amp;task=add&amp;type=unsub';
		$style = empty($value) ? 'style="display:none!important;"' : '';
		$text = acymailing_popup($linkEdit, '<img src="'.ACYMAILING_IMAGES.'icons/icon-16-edit.png" alt="'.acymailing_translation('EDIT_EMAIL',true).'"/>', '', 0, 500, 'unsub_edit', $style);
		$text .= acymailing_popup($linkAdd, '<img src="'.ACYMAILING_IMAGES.'icons/icon-16-add.png" alt="'.acymailing_translation('CREATE_EMAIL',true).'"/>', '', 0, 500, 'unsub_add');

		return acymailing_select($this->values, 'data[list][unsubmailid]', 'class="inputbox" size="1" onchange="changeMessage(\'unsub\',this.value);"', 'value', 'text', (int) $value ).$text;
	}
}
