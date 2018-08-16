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

acymailing_importPlugin('acymailing');
$typesFilters = array();
$outputFilters = implode('', acymailing_trigger('onAcyDisplayFilters', array(&$typesFilters, 'mail')));

if(empty($typesFilters)) return;

$filterClass = acymailing_get('class.filter');
$filterClass->addJSFilterFunctions();

$js = '';
$datatype = "filter";
if(!empty($this->mail->$datatype)){
	foreach($this->mail->{$datatype}['type'] as $block => $oneFilter){
		$js .= 'if(!document.getElementById(\'addButton_'.$block.'\')) addOrBlock();';
		$jsFunction = "if(!document.getElementById('addButton_$block')) addOrBlock();
					document.getElementById('addButton_$block').click();";

		foreach($oneFilter as $num => $oneType) {
			if (empty($oneType)) continue;
			$js .= "
				if(!document.getElementById('" . $datatype . "type$num')){
					" . $jsFunction . "
				}
				
				document.getElementById('" . $datatype . "type$num').value= '$oneType';
				update" . ucfirst($datatype) . "($num);";
			if (empty($this->mail->{$datatype}[$num][$oneType])) continue;

			foreach ($this->mail->{$datatype}[$num][$oneType] as $key => $value) {
				$js .= "
				try{
					document.adminForm.elements['" . $datatype . "[$num][$oneType][$key]'].value = '" . addslashes(str_replace(array("\n", "\r"), ' ', $value)) . "';
					if(document.adminForm.elements['" . $datatype . "[$num][$oneType][$key]'].type && document.adminForm.elements['" . $datatype . "[$num][$oneType][$key]'].type == 'checkbox'){
						document.adminForm.elements['" . $datatype . "[$num][$oneType][$key]'].checked = 'checked';
					}
				}catch(e){}";
			}

			if ($datatype == 'filter') $js .= " countresults($num);";
		}
	}
}

acymailing_addScript(true, "document.addEventListener(\"DOMContentLoaded\", function(){ $js });");

$typevaluesFilters = array();
$typevaluesFilters[] = acymailing_selectOption('', acymailing_translation('FILTER_SELECT'));
foreach($typesFilters as $oneType => $oneName){
	$typevaluesFilters[] = acymailing_selectOption($oneType, $oneName);
}

?>
<br/>
<div class="acy_filter_mail">
	<input type="hidden" name="data[mail][filter]" value=""/>

	<div id="acybase_filters" style="display:none">
		<div id="filters_original">
			<?php echo acymailing_select($typevaluesFilters, "filter[type][__block__][__num__]", 'class="inputbox" size="1" onchange="updateFilter(__num__);countresults(__num__);"', 'value', 'text', '', 'filtertype__num__'); ?>
			<span id="countresult___num__"></span>

			<div class="acyfilterarea" id="filterarea___num__"></div>
		</div>
		<?php echo $outputFilters; ?>
	</div>
	<?php echo acymailing_translation('RECEIVER_LISTS').' '.acymailing_translation('RECEIVER_FILTER'); ?>
	<div class="onelineblockoptions" id="filtersblock">
		<span class="acyblocktitle"><?php echo acymailing_translation('ACY_FILTERS'); ?></span>
		<button id="acyorbutton" class="acymailing_button" onclick="addOrBlock();return false;"><?php echo ucfirst(acymailing_translation('ACY_OR')); ?></button>
	</div>
</div>
