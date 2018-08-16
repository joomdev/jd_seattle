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

class acytoggleHelper{

	var $ctrl = 'toggle';
	var $extra = '';

	private function _getToggle($column, $table = ''){

		$params = new stdClass();
		$params->mode = 'pictures';
		if($column == 'published' && !in_array($table, array('plugins', 'list'))){
			$params->aclass = array(0 => 'acyicon-cancel', 1 => 'acyicon-apply', 2 => 'acyicon-schedule');
			$params->description = array(0 => acymailing_translation('PUBLISH_CLICK'), 1 => acymailing_translation('UNPUBLISH_CLICK'), 2 => acymailing_translation('UNSCHEDULE_CLICK'));
			$params->values = array(0 => 1, 1 => 0, 2 => 0);
			return $params;
		}elseif($column == 'status'){
			$params->mode = 'class';
			$params->class = array(-1 => 'roundsubscrib roundunsub', 1 => 'roundsubscrib roundsub', 2 => 'roundsubscrib roundconf');
			$params->description = array(-1 => acymailing_translation('SUBSCRIBE_CLICK'), 1 => acymailing_translation('UNSUBSCRIBE_CLICK'), 2 => acymailing_translation('CONFIRMATION_CLICK'));
			$params->values = array(-1 => 1, 1 => -1, 2 => 1);
			return $params;
		}

		$params->aclass = array(0 => 'acyicon-cancel', 1 => 'acyicon-apply');
		$params->values = array(0 => 1, 1 => 0);
		return $params;
	}

	function toggleText($action = '', $value = '', $table = '', $text = ''){
		static $jsincluded = false;
		static $id = 0;
		$id++;
		if(!$jsincluded){
			$jsincluded = true;
			$js = "function joomToggleText(id,newvalue,table){
				window.document.getElementById(id).className = 'onload';
					
				var xhr = new XMLHttpRequest();
				xhr.open('GET', '".acymailing_prepareAjaxURL('toggle')."&task='+id+'&value='+newvalue+'&table='+table+'&".acymailing_getFormToken()."');
				xhr.onload = function(){
					document.getElementById(id).innerHTML = xhr.responseText;
					window.document.getElementById(id).className = 'loading';
				};
				xhr.send();
			}";
			acymailing_addScript(true, $js);
		}

		if(!$action) return;

		return '<span id="'.$action.'_'.$value.'" ><a href="javascript:void(0);" onclick="joomToggleText(\''.$action.'_'.$value.'\',\''.$value.'\',\''.$table.'\')">'.$text.'</a></span>';
	}

	function toggle($id, $value, $table, $extra = null){
		$column = substr($id, 0, strpos($id, '_'));
		$params = $this->_getToggle($column, $table);
		if(!isset($params->values[$value])) return;
		$newValue = $params->values[$value];
		if($params->mode == 'pictures'){
			static $pictureincluded = false;
			if(!$pictureincluded){
				$pictureincluded = true;
				$js = "function joomTogglePicture(id,newvalue,table){
					window.document.getElementById(id).className = 'onload';
					var xhr = new XMLHttpRequest();
					xhr.open('GET', '".acymailing_prepareAjaxURL('toggle')."&task='+id+'&value='+newvalue+'&table='+table+'&".acymailing_getFormToken()."');
					xhr.onload = function(){
						document.getElementById(id).innerHTML = xhr.responseText;
						window.document.getElementById(id).className = 'loading';
					};
					xhr.send();
				}";
				acymailing_addScript(true, $js);
			}

			$desc = empty($params->description[$value]) ? '' : $params->description[$value];

			if(empty($params->pictures)){
				$text = ' ';
				$class = 'class="'.$params->aclass[$value].'"';
			}else{
				$text = '<img src="'.$params->pictures[$value].'"/>';
				$class = '';
			}

			return '<a href="javascript:void(0);" style="font-style: normal;" '.$class.' onclick="joomTogglePicture(\''.$id.'\',\''.$newValue.'\',\''.$table.'\')" title="'.str_replace('"', '\"', $desc).'">'.$text.'</a>';
		}elseif($params->mode == 'class'){
			if(empty($extra)) return;
			static $classincluded = false;
			if(!$classincluded){
				$classincluded = true;
				$js = "function joomToggleClass(id,newvalue,table,extra){
					var mydiv = document.getElementById(id);
					mydiv.innerHTML = '';
					mydiv.className = 'onload';
					
					var xhr = new XMLHttpRequest();
					xhr.open('GET', '".acymailing_prepareAjaxURL('toggle')."&task='+id+'&value='+newvalue+'&table='+table+'&".acymailing_getFormToken()."&extra[color]='+extra);
					xhr.onload = function(){
						document.getElementById(id).innerHTML = xhr.responseText;
						window.document.getElementById(id).className = 'loading';
					};
					xhr.send();
				}";
				acymailing_addScript(true, $js);
			}
			
			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			$return = '<a href="javascript:void(0);" onclick="joomToggleClass(\''.$id.'\',\''.$newValue.'\',\''.$table.'\',\''.htmlspecialchars(urlencode($extra['color']), ENT_COMPAT, 'UTF-8').'\');" title="'.str_replace('"', '\"', $desc).'"><div class="'.$params->class[$value].'" style="background-color:'.htmlspecialchars($extra['color'], ENT_COMPAT, 'UTF-8').';border-color:'.htmlspecialchars($extra['color'], ENT_COMPAT, 'UTF-8').'">';
			if(!empty($extra['tooltip'])) $return .= acymailing_tooltip($extra['tooltip'], @$extra['tooltiptitle'], '', '&nbsp;&nbsp;&nbsp;&nbsp;');
			$return .= '</div></a>';

			return $return;
		}
	}

	function display($column, $value){
		$params = $this->_getToggle($column);

		$title = '';
		if($column == 'published') $title = 'title="'.($value == 1 ? acymailing_translation('ENABLED') : acymailing_translation('DISABLED')).'"';

		if(empty($params->pictures)){
			return '<a style="cursor:default;" class="'.$params->aclass[$value].'" '.$title.'></a>';
		}else{
			return '<img src="'.$params->pictures[$value].'"/>';
		}
	}

	function delete($lineId, $elementids, $table, $confirm = false, $text = '', $extraJsOnClick = ''){
		static $deleteJS = false;
		if(!$deleteJS){
			$deleteJS = true;
			$js = "function joomDelete(lineid,elementids,table,reqconfirm){
				if(reqconfirm){
					if(!confirm('".acymailing_translation('ACY_VALIDDELETEITEMS', true)."')) return false;
				}
					
				var xhr = new XMLHttpRequest();
				xhr.open('GET', '".acymailing_prepareAjaxURL($this->ctrl).$this->extra."&task=delete&value='+elementids+'&table='+table+'&".acymailing_getFormToken()."');
				xhr.onload = function(){
					window.document.getElementById(lineid).style.display = 'none';
				};
				xhr.send();
			}";

			acymailing_addScript(true, $js);
		}

		if(empty($text)){
			if(acymailing_isAdmin()){
				$text = '<span class="hasTooltip acyicon-delete" data-original-title="'.acymailing_translation('ACY_DELETE').'" title="'.acymailing_translation('ACY_DELETE').'"/>';
			}else{
				$text = '<img src="'.ACYMAILING_MEDIA_FOLDER.'/images/delete.png" title="Delete">';
			}
		}
		return '<a href="javascript:void(0);" onclick="joomDelete(\''.$lineId.'\',\''.$elementids.'\',\''.$table.'\','.($confirm ? 'true' : 'false').'); '.$extraJsOnClick.'">'.$text.'</a>';
	}
}

