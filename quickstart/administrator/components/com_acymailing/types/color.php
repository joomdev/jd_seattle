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

class colorType extends acymailingClass{
	function __construct(){
		parent::__construct();

		$this->values = array();

		for($red=0; $red<6;$red++) {
			$rhex = dechex($red * 0x33);
			$rhex = (strlen($rhex) < 2)?"0".$rhex:$rhex;
			for($blue=0; $blue<6;$blue++) {
				$bhex = dechex($blue * 0x33);
				$bhex = (strlen($bhex) < 2)?"0".$bhex:$bhex;
				for($green=0; $green<6;$green++) {
					$ghex = dechex($green * 0x33);
					$ghex = (strlen($ghex) < 2)?"0".$ghex:$ghex;
					$this->values[$red][] = '#'.$rhex.$ghex.$bhex;
				}
			}
		}

		$this->othervalues[] = '#000000';
		$this->othervalues[] = '#111111';
		$this->othervalues[] = '#222222';
		$this->othervalues[] = '#333333';
		$this->othervalues[] = '#444444';
		$this->othervalues[] = '#555555';
		$this->othervalues[] = '#666666';
		$this->othervalues[] = '#777777';
		$this->othervalues[] = '#888888';
		$this->othervalues[]  = '#999999';
		$this->othervalues[]  = '#AAAAAA';
		$this->othervalues[]  = '#BBBBBB';
		$this->othervalues[]  = '#CCCCCC';
		$this->othervalues[]  = '#DDDDDD';
		$this->othervalues[]  = '#EEEEEE';
		$this->othervalues[]  = '#FFFFFF';
		$this->othervalues[]  = '#FF0000';
		$this->othervalues[]  = '#00FFFF';
		$this->othervalues[]  = '#0000FF';
		$this->othervalues[]  = '#0000A0';
		$this->othervalues[]  = '#FF0080';
		$this->othervalues[]  = '#800080';
		$this->othervalues[]  = '#FFFF00';
		$this->othervalues[]  = '#00FF00';
		$this->othervalues[]  = '#FF00FF';
		$this->othervalues[]  = '#FF8040';
		$this->othervalues[]  = '#804000';
		$this->othervalues[]  = '#800000';
		$this->othervalues[]  = '#808000';
		$this->othervalues[]  = '#408080';

	}

	function displayAll($id,$map,$color){
		 $this->jsScript = 'function applyColor'.$id.'(newcolor){document.getElementById(\'color'.$id.'\').value = newcolor; document.getElementById("colordiv'.$id.'").style.display = "none";applyColorExample'.$id.'();}';

		$code = '<input type="text" name="'.$map.'" id="color'.$id.'" onchange=\'applyColorExample'.$id.'()\' class="inputbox" style="width:50px" value="'.htmlspecialchars($color,ENT_COMPAT, 'UTF-8').'" />';
		$code .= ' <input type="text" maxlength="0" style="cursor:pointer;width:50px;background-color:'.htmlspecialchars($color,ENT_COMPAT, 'UTF-8').';" onclick="if(document.getElementById(\'colordiv'.$id.'\').style.display == \'block\'){document.getElementById(\'colordiv'.$id.'\').style.display = \'none\';}else{document.getElementById(\'colordiv'.$id.'\').style.display = \'block\';}" id=\'colorexample'.$id.'\' />';
		$code .= '<div id=\'colordiv'.$id.'\' style=\'display:none;position:absolute;z-index:100;background-color:white;border:1px solid grey\'>'.$this->display($id).'</div>';
		return $code;
	}

	function displayOne($id,$map,$color){

	$this->jsScript = 'function applyColorwysijacolor(newcolor){
							 var myRegex = new RegExp(/([^a-z-])color *:[^;]*(!important)?[^;]*;/i);
							document.getElementById("name_"+currentValueId).style.color = newcolor;
							document.getElementById("colorexamplewysijacolor").style.backgroundColor = newcolor;
							spaced = document.getElementById("style_"+currentValueId).value.substr(0,1);
							if(spaced != " "){
								stringToQuery = \' \' + document.getElementById("style_"+currentValueId).value;
							}
							else{
								stringToQuery = document.getElementById("style_"+currentValueId).value;
							}
							if(stringToQuery.search(myRegex) != -1){
							if(currentValueId.search("tag_h") != -1){
								document.getElementById("style_"+currentValueId).value = stringToQuery.replace(myRegex, "$1"+"color:"+newcolor+" !important;");
							}
							else{
								document.getElementById("style_"+currentValueId).value = stringToQuery.replace(myRegex, "$1"+"color:"+newcolor+";");
							}
							}
							else{
								 if(currentValueId.search("tag_h") != -1){
								document.getElementById("style_"+currentValueId).value = "color:"+newcolor+" !important;" + document.getElementById("style_"+currentValueId).value;
							}
							else{
								document.getElementById("style_"+currentValueId).value = "color:"+newcolor+";" + document.getElementById("style_"+currentValueId).value;
							}
							}
							document.getElementById("colordivwysijacolor").style.display = "none";
							document.getElementById(\'colorexample'.$id.'\').style.backgroundColor = newcolor;
						}';
		$code = ' <input type="text" maxlength="0" style=\'width:17px;height:13px;padding:0px;margin:0px;cursor:pointer;background-color:'.$color.'\' onclick="if(document.getElementById(\'colordivwysijacolor\').style.display == \'block\'){document.getElementById(\'colordivwysijacolor\').style.display = \'none\';}else{document.getElementById(\'colordivwysijacolor\').style.display = \'block\';}" id=\'colorexamplewysijacolor\' />';
		$code .= '<div id=\'colordivwysijacolor\' style=\'display:none;width:300px;position:absolute;background-color:white;border:1px solid grey\'>'.$this->display($id).'</div>';
		return $code;
	}

	function display($id = ''){

		$js =  $this->jsScript;
		$js .= 'function applyColorExample'.$id.'(){document.getElementById(\'colorexample'.$id.'\').style.backgroundColor = document.getElementById(\'color'.$id.'\').value; document.getElementById("colordiv'.$id.'").style.display = "none";}';
		acymailing_addScript(true, $js);


		$text = '<table><tr>';
		foreach($this->othervalues as $oneColor){
			$text .= '<td style="cursor:pointer" width="10" height="10" bgcolor="'.$oneColor.'" onclick="applyColor'.$id.'(\''.$oneColor.'\')"></td>';
		}
		$text .= '</tr></table>';
		$text .= '<table>';
		foreach($this->values as $line){
			$text .= '<tr>';
			foreach($line as $oneColor){
				$text .= '<td style="cursor:pointer" width="10" height="10" bgcolor="'.$oneColor.'" onclick="applyColor'.$id.'(\''.$oneColor.'\')"></td>';
			}
			$text .= '</tr>';
		}
		$text .= '</table>';

		return $text;
	}

}
