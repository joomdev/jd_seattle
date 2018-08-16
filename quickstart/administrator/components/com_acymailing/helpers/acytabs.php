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

class acytabsHelper{
	var $openPanel = false;
	var $data = array();
	var $name = '';

	function __construct(){
	}

	function startPane($name){
		$this->name = $name;
	}

	function startPanel($text, $id){
		if($this->openPanel) $this->endPanel();

		$obj = new stdClass();
		$obj->text = $text;
		$obj->id = $id;
		$obj->data = '';
		$this->data[] = $obj;
		ob_start();
		$this->openPanel = true;
	}

	function endPanel(){
		if(!$this->openPanel) return;

		$panel = end($this->data);
		$panel->data .= ob_get_clean();
		$this->openPanel = false;
	}

	function endPane(){
		$ret = '';
		$content = '';

		if($this->openPanel) $this->endPanel();

		$ret .= '<div style="margin-left:10px;" class="acytabsystem"><ul class="nav nav-tabs" id="'.$this->name.'" style="width:100%;">'."\r\n";
		foreach($this->data as $k => $data){
			$ret .= '	<li'.($k == 0 ? ' class="active"' : '').' id="'.$data->id.'_tabli"><a href="#'.$data->id.'" id="'.$data->id.'_tablink" onclick="toggleTab(\''.$this->name.'\', \''.$data->id.'\');return false;">'.acymailing_translation($data->text).'</a></li>'."\r\n";

			$content .= '	<div class="tab-pane'.($k == 0 ? ' active' : '').'" id="'.$data->id.'">'."\r\n".$data->data."\r\n".'	</div>'."\r\n";
			unset($data->data);
		}
		$ret .= '</ul>'."\r\n".'<div class="tab-content" id="'.$this->name.'_content">'."\r\n";
		$ret .= $content.'</div></div>';
		unset($this->data);

		static $jsInit = false;
		if(!$jsInit){
			$jsInit = true;
			$js = '
			
			document.addEventListener("DOMContentLoaded", function(){
				var selectedTab = localStorage.getItem("acy'.$this->name.'");
				if(selectedTab && document.getElementById(selectedTab)){
					var selectedLi = document.getElementById("'.$this->name.'").querySelector("li.active");
					var selectedContent = document.getElementById("'.$this->name.'_content").querySelector("div.tab-pane.active");
					selectedLi.className = selectedLi.className.replace("active", "");
					selectedContent.className = selectedContent.className.replace("active", "");
					
					document.getElementById(selectedTab+"_tabli").className += " active";
					document.getElementById(selectedTab).className += " active";
				}
			});
				
			function toggleTab(group, id){
				localStorage.setItem("acy"+group, id);
			
				var contentTabs = document.querySelectorAll("#"+group+"_content > div");
				for (i = 0; i < contentTabs.length; i++) {
					contentTabs[i].className = contentTabs[i].className.replace("active", "");
				}
				document.getElementById(id).className += " active";
				var groupTabs = document.querySelectorAll("#"+group+" > li");
				for (i = 0; i < groupTabs.length; i++) {
					groupTabs[i].className = groupTabs[i].className.replace("active", "");
				}
				document.getElementById(id+"_tablink").parentElement.className += " active";
				
			}';
			acymailing_addScript(true, $js);
		}

		return $ret;
	}
}
