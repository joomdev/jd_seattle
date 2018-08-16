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

class bounceactionType extends acymailingClass{
	function __construct(){
		parent::__construct();

		$this->values = array();
		$this->values[] = acymailing_selectOption('noaction', acymailing_translation('DO_NOTHING'));
		$this->values[] = acymailing_selectOption('remove', acymailing_translation('REMOVE_SUB'));
		$this->values[] = acymailing_selectOption('unsub', acymailing_translation('UNSUB_USER'));
		$this->values[] = acymailing_selectOption('sub', acymailing_translation('SUBSCRIBE_USER'));
		$this->values[] = acymailing_selectOption('block', acymailing_translation('BLOCK_USER'));
		$this->values[] = acymailing_selectOption('delete', acymailing_translation('DELETE_USER'));

		$this->config = acymailing_config();
		$this->lists = acymailing_get('type.lists');
		$this->lists->getValues();
		array_shift($this->lists->values);

		$js = "function updateSubAction(num){";
			$js .= "myAction = window.document.getElementById('bounce_action_'+num).value;";
			$js .= "if(myAction == 'sub') {window.document.getElementById('bounce_action_lists_'+num).style.display = '';}else{window.document.getElementById('bounce_action_lists_'+num).style.display = 'none';}";
		$js .= '}';
		acymailing_addScript(true, $js);
	}

	function display($num,$value){
		$js ='document.addEventListener("DOMContentLoaded", function(){ updateSubAction("'.$num.'"); });';
		acymailing_addScript(true, $js);

		$return = acymailing_select(  $this->values, 'config[bounce_action_'.$num.']', 'class="inputbox" size="1" onchange="updateSubAction(\''.$num.'\');"', 'value', 'text', $value ,'bounce_action_'.$num);
		$return .= '<span id="bounce_action_lists_'.$num.'" style="display:none">'.$this->lists->display('config[bounce_action_lists_'.$num.']',$this->config->get('bounce_action_lists_'.$num),false).'</span>';

		return $return;
	}

}
