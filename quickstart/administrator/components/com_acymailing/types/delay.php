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

class delayType extends acymailingClass{
	var $values = array();
	var $num = 0;
	var $onChange = '';

	function __construct(){
		parent::__construct();

		static $i = 0;
		$i++;
		$this->num = $i;

		$js = "function updateDelay".$this->num."(){";
			$js .= "delayvar = window.document.getElementById('delayvar".$this->num."');";
			$js .= "delaytype = window.document.getElementById('delaytype".$this->num."').value;";
			$js .= "delayvalue = window.document.getElementById('delayvalue".$this->num."');";
			$js .= "realValue = delayvalue.value;";
			$js .= "if(delaytype == 'minute'){realValue = realValue*60; }";
			$js .= "if(delaytype == 'hour'){realValue = realValue*3600; }";
			$js .= "if(delaytype == 'day'){realValue = realValue*86400; }";
			$js .= "if(delaytype == 'week'){realValue = realValue*604800; }";
			$js .= "if(delaytype == 'month'){realValue = realValue*2592000; }";
			$js .= "delayvar.value = realValue;";
		$js .= '}';
		acymailing_addScript(true, $js);

	}

	function display($map,$value,$type = 1){
		if($type == 0){
			$this->values[] = acymailing_selectOption('second', acymailing_translation('ACY_SECONDS'));
			$this->values[] = acymailing_selectOption('minute', acymailing_translation('ACY_MINUTES'));
		}elseif($type == 1){
			$this->values[] = acymailing_selectOption('minute', acymailing_translation('ACY_MINUTES'));
			$this->values[] = acymailing_selectOption('hour', acymailing_translation('HOURS'));
			$this->values[] = acymailing_selectOption('day', acymailing_translation('DAYS'));
			$this->values[] = acymailing_selectOption('week', acymailing_translation('WEEKS'));
		}elseif($type == 2){
			$this->values[] = acymailing_selectOption('minute', acymailing_translation('ACY_MINUTES'));
			$this->values[] = acymailing_selectOption('hour', acymailing_translation('HOURS'));
		}elseif($type == 3){
			$this->values[] = acymailing_selectOption('hour', acymailing_translation('HOURS'));
			$this->values[] = acymailing_selectOption('day', acymailing_translation('DAYS'));
			$this->values[] = acymailing_selectOption('week', acymailing_translation('WEEKS'));
			$this->values[] = acymailing_selectOption('month', acymailing_translation('MONTHS'));
		}elseif($type == 4){
			$this->values[] = acymailing_selectOption('week', acymailing_translation('WEEKS'));
			$this->values[] = acymailing_selectOption('month', acymailing_translation('MONTHS'));
		}

		$return = $this->get($value,$type);
		$delayValue = '<input class="inputbox" onchange="updateDelay'.$this->num.'();'.$this->onChange.'" type="text" id="delayvalue'.$this->num.'" style="width:50px" value="'.$return->value.'" /> ';
		$delayVar = '<input type="hidden" name="'.$map.'" id="delayvar'.$this->num.'" value="'.$value.'"/>';
		return $delayValue.acymailing_select(  $this->values, 'delaytype'.$this->num, 'class="inputbox" size="1" style="width:100px" onchange="updateDelay'.$this->num.'();'.$this->onChange.'"', 'value', 'text', $return->type ,'delaytype'.$this->num).$delayVar;
	}

	function get($value,$type){

		$return = new stdClass();

		$return->value = $value;
		if($type == 0){
			$return->type = 'second';
		}else{
			$return->type = 'minute';
		}

		if($return->value >= 60  AND $return->value%60 == 0){
			$return->value = (int) $return->value / 60;
			$return->type = 'minute';
			if($type != 0 AND $return->value >=60 AND $return->value%60 == 0){
				$return->type = 'hour';
				$return->value = $return->value / 60;
				if($type != 2 AND $return->value >=24 AND $return->value%24 == 0){
					$return->type = 'day';
					$return->value = $return->value / 24;
					if($type >= 3 AND $return->value >=30 AND $return->value%30 == 0){
						$return->type = 'month';
						$return->value = $return->value / 30;
					}elseif($return->value >=7 AND $return->value%7 == 0){
						$return->type = 'week';
						$return->value = $return->value / 7;
					}
				}
			}
		}

		return $return;

	}

}
