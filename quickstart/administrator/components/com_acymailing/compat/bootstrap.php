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


JHtml::_('bootstrap.framework');

class JHtmlAcyselect extends JHTMLSelect{
	static $event = false;

	public static function booleanlist($name, $attribs = null, $selected = null, $yes = 'JOOMEXT_YES', $no = 'JOOMEXT_NO', $id = false){
		$arr = array(acymailing_selectOption('0', acymailing_translation($no)), acymailing_selectOption('1', acymailing_translation($yes)));
		$arr[0]->class = 'btn-danger';
		$arr[1]->class = 'btn-success';
		return acymailing_radio($arr, $name, $attribs, 'value', 'text', (int)$selected, $id);
	}

	public static function radiolist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false, $vertical = false){
		reset($data);
		$backend = acymailing_isAdmin();
		$config = acymailing_config();
		if(!self::$event){
			self::$event = true;
			if($backend){
				acymailing_addScript(true, '
(function($){
	$.propHooks.checked = {
		set: function(elem, value, name) {
			var ret = (elem[ name ] = value);
			$(elem).trigger("change");
			return ret;
		}
	};
})(jQuery);');
			}else{
				acymailing_addScript(true, '
(function($){
if(!window.acyLocal)
	window.acyLocal = {};
window.acyLocal.radioEvent = function(el) {
	var id = $(el).attr("id"), c = $(el).attr("class"), lbl = $("label[for=\"" + id + "\"]");
	if(c !== undefined && c.length > 0)
		lbl.addClass(c);
	lbl.addClass("active");
	$("input[name=\"" + $(el).attr("name") + "\"]").each(function() {
		if($(this).attr("id") != id) {
			c = $(this).attr("class");
			lbl = $("label[for=\"" + $(this).attr("id") + "\"]");
			if(c !== undefined && c.length > 0)
				lbl.removeClass(c);
			lbl.removeClass("active");
		}
	});
}
$(document).ready(function() {
	setTimeout(function() { $(".acyradios .btn-group label").off("click"); }, 200 );
});

})(jQuery);');
			}
		}

		if(is_array($attribs)){
			$attribs = acymailing_arrayToString($attribs);
		}

		if(!$backend){
			$attribs = ' '.$attribs;
			$onclick = '';
			if(strpos($attribs, ' onclick="') !== false || strpos($attribs, 'onclick=\'') !== false){
				$onclick = $attribs;
			}
			if(strpos($attribs, ' style="') !== false){
				$attribs = str_replace(' style="', ' style="display:none;', $attribs);
			}elseif(strpos($attribs, 'style=\'') !== false){
				$attribs = str_replace(' style=\'', ' style=\'display:none;', $attribs);
			}else{
				$attribs .= ' style="display:none;"';
			}
			if(strpos($attribs, ' onchange="') !== false){
				$attribs = str_replace(' onchange="', ' onchange="window.acyLocal.radioEvent(this);', $attribs);
			}elseif(strpos($attribs, 'onchange=\'') !== false){
				$attribs = str_replace(' onchange=\'', ' onchange=\'window.acyLocal.radioEvent(this);', $attribs);
			}else{
				$attribs .= ' onchange="window.acyLocal.radioEvent(this);"';
			}
		}

		$id_text = preg_replace('#[^a-zA-Z0-9]+#mi', '_', str_replace(array('[', ']'), array('_', ''), $idtag ? $idtag : $name));
		$htmlBootstrap2 = '';
		$htmlBootstrap3 = '';
		if($backend){
			$html = '<div class="controls"><fieldset id="'.$id_text.'fieldset" class="radio btn-group'.($vertical ? ' btn-group-vertical' : '').'">';


		}else{
			$html = '<div class="acyradios" id="'.$id_text.'">';
		}

		foreach($data as $obj){
			if(is_string($obj)){
				$html .= $obj;
				continue;
			}

			$k = $obj->$optKey;
			$t = $translate ? acymailing_translation($obj->$optText) : $obj->$optText;
			$id = (isset($obj->id) ? $obj->id : null);

			$active = '';
			$sel = false;
			$extra = $id ? ' id="'.$obj->id.'"' : '';
			$currId = $id_text.$k;
			if(isset($obj->id)){
				$currId = $obj->id;
			}

			if(is_array($selected)){
				foreach($selected as $val){
					$k2 = is_object($val) ? $val->$optKey : $val;
					if($k == $k2){
						$extra .= ' selected="selected"';
						$sel = true;
						break;
					}
				}
			}elseif((string)$k == (string)$selected){
				$extra .= ' checked="checked"';
				$sel = true;
				$active = 'active';
				if(!empty($obj->class)) $active .= ' '.$obj->class;
			}

			if(!empty($obj->class)) $extra .= ' class="'.$obj->class.'"';

			if($backend){
				$html .= "\n\t\n\t".'<input type="radio" name="'.$name.'" id="'.$id_text.$k.'" value="'.$k.'" '.$extra.' '.$attribs.'/>';
				$html .= "\n\t".'<label for="'.$id_text.$k.'">'.$t.'</label>';

			}else{
				if($config->get('bootstrap_frontend') == 2){
					$onclickFinal = str_replace('this.value', "'".$k."'", $onclick);
					$htmlBootstrap3 .= "\n\t".'<label for="'.$currId.'" class="btn btn-primary '.$active.'" '.$onclickFinal.'>';
					$htmlBootstrap3 .= "\n\t".'<input type="radio" name="'.$name.'"'.' id="'.$currId.'"'.$extra.' '.$attribs.' value="'.$k.'" > '.$t.'</label>';
				}else{
					$html .= "\n\t".'<input type="radio" name="'.$name.'"'.' id="'.$currId.'" value="'.$k.'"'.' '.$extra.' '.$attribs.'/>';
					$htmlBootstrap2 .= "\n\t"."\n\t".'<label for="'.$currId.'"'.' class="btn'.($sel ? ' active'.(empty($obj->class) ? '' : ' '.$obj->class) : '').'">'.$t.'</label>';
				}
			}
		}
		if($backend){
			$html .= '</fieldset></div>';
		}else{
			if($config->get('bootstrap_frontend') == 2){
				$html .= "\n".'<div class="btn-group'.($vertical ? ' btn-group-vertical' : '').'" data-toggle="buttons">'.$htmlBootstrap3."\n".'</div>';
			}else{
				$html .= "\n".'<div class="btn-group'.($vertical ? ' btn-group-vertical' : '').'" data-toggle="buttons-radio">'.$htmlBootstrap2."\n".'</div>';
			}
			$html .= "\n".'</div>';
		}
		$html .= "\n";
		return $html;
	}

}
