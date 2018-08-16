<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php if(count($this->lists) > 10){ ?>
	<script language="javascript" type="text/javascript">
		<!--
		function acymailing_searchAList(){
			var filter = document.getElementById("acymailing_searchList").value.toLowerCase();
			for(var i = 0; i <<?php echo count($this->lists); ?>; i++){
				var itemName = document.getElementById("listName_" + i).innerHTML.toLowerCase();
				if(itemName.indexOf(filter) > -1){
					document.getElementById("acylistrow_" + i).style.display = "table-row";
				}else{
					document.getElementById("acylistrow_" + i).style.display = "none";
				}
			}
		}
		//-->
	</script>
	<div style="margin-bottom:10px;"><input onkeyup="acymailing_searchAList();" type="text" style="width: 200px;max-width:100%;margin-bottom:5px;" placeholder="<?php echo acymailing_translation('ACY_SEARCH'); ?>" id="acymailing_searchList"></div>
<?php }

$k = 0;
$i = 0;

$orderedList = array();
$listsPerCategory = array();
$languages = array();
foreach($this->lists as $row){
	$orderedList[$row->category][$row->listid] = $row;
	$listsPerCategory[$row->category][$row->listid] = $row->listid;
	if(count($this->lists) < 4) continue;

	$languages['all'][$row->listid] = $row->listid;
	if($row->languages == 'all') continue;
	$lang = explode(',', trim($row->languages, ','));
	foreach($lang as $oneLang){
		$languages[strtolower($oneLang)][$row->listid] = $row->listid;
	}
}
ksort($orderedList);
$allCats = array_keys($orderedList);
$this->lists = array();
foreach($orderedList as $oneCategory){
	$this->lists = array_merge($this->lists, $oneCategory);
}

if($currentPage == 'export'){
	$possibleStatuses = array();
	$possibleStatuses[] = acymailing_selectOption("0", acymailing_translation('ACY_DONT_EXPORT'));
	$possibleStatuses[] = acymailing_selectOption("-1", acymailing_translation('ACTION_UNSUBSCRIBED'));
	$possibleStatuses[] = acymailing_selectOption("2", acymailing_translation('PENDING_SUBSCRIPTION'));
	$possibleStatuses[] = acymailing_selectOption("1", acymailing_translation('SUBSCRIBED'));

	if(!acymailing_isAdmin()){
		$possibleStatuses[0]->class = 'btn-danger';
		$possibleStatuses[1]->class = 'btn-success';
		$possibleStatuses[2]->class = 'btn-success';
		$possibleStatuses[3]->class = 'btn-success';
	}
}

echo '<table class="acymailing_table" id="lists_choice"><tbody>';

foreach($this->lists as $row){
	if(empty($row->category)) $row->category = acymailing_translation('ACY_NO_CATEGORY');
	if(count($allCats) > 1 && (empty($currentCatgeory) || $row->category != $currentCatgeory)){
		$currentCatgeory = $row->category; ?>
		<tr class="<?php echo "row$k"; ?>">
			<td colspan="2">
				<a href="#" onclick="checkCats('<?php echo htmlspecialchars(str_replace("'", "\'", $row->category == acymailing_translation('ACY_NO_CATEGORY') ? -1 : $row->category), ENT_QUOTES, "UTF-8"); ?>'); return false;"><strong><?php echo htmlspecialchars($row->category, ENT_QUOTES, "UTF-8"); ?></strong></a>
			</td>
		</tr>
	<?php }
	if($currentPage == 'export'){
		$checked = (empty($this->exportlist) && in_array($row->listid, $this->selectedlists)) ? 1 : 0;
	}elseif($currentPage == 'import'){
		$filter_lists = explode(',', rtrim(acymailing_getVar('string', 'filter_lists'), ','));
		if(!empty($row->campaign)){
			$checked = acymailing_getVar('cmd', 'importlists['.$row->listid.']', in_array($row->listid, $filter_lists) ? 2 : 0);
		}else{
			$checked = !empty($currentValues[$row->listid]) || in_array($row->listid, $filter_lists) || $listid == $row->listid ? 1 : 0;
		}
	}

	$classList = $checked ? 'acy_list_checked' : 'acy_list_unchecked';
	?>
	<tr id="acylistrow_<?php echo $i; ?>" class="<?php echo "row$k $classList"; ?>">
		<td style="display:none;" id="listId_<?php echo $i; ?>"><?php echo $row->listid; ?></td>
		<td style="display:none;" id="listName_<?php echo $i; ?>"><?php echo $row->name; ?></td>
		<td>
			<?php
			echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>';
			$text = '<b>'.acymailing_translation('ACY_ID').' : </b>'.$row->listid;
			$text .= '<br />'.$row->description;
			echo acymailing_tooltip($text, $row->name, 'tooltip.png', $row->name);
			?>
		</td>
		<td nowrap="nowrap">
			<?php
			if($currentPage == 'export'){
				if(!empty($this->exportlist) && $this->exportlist == $row->listid){
					$checked = $this->exportliststatus;
					if($this->exportliststatus == -2) $checked = 0;
				}
				echo acymailing_radio($possibleStatuses, "exportlists[".$row->listid."]", '', 'value', 'text', $checked, $row->listid.'listmail');
			}elseif($currentPage == 'import'){
				if(!empty($row->campaign)){
					echo acymailing_radio($this->campaignValues, "importlists[".$row->listid."]", '', 'value', 'text', $checked, $row->listid.'listmail');
				}else{
					echo acymailing_radio($this->subscribeOptions, "importlists[".$row->listid."]", '', 'value', 'text', $checked, $row->listid.'listmail');
				}
			}
			?>
		</td>
	</tr>
	<?php
	$k = 1 - $k;
	$i++;
}
if(count($this->lists) > 3){ ?>
	<tr>
		<td></td>
		<td nowrap="nowrap">
			<script language="javascript" type="text/javascript">
				<!--
				var selectedLists = new Array();
				<?php
				foreach($languages as $val => $listids){
					echo "selectedLists['$val'] = new Array('".implode("','", $listids)."'); ";
				}
				?>
				function updateStatus(selection){
					<?php
					$listidAll = "selectedLists['all'][i]+'listmail";
					$listidSelection = "selectedLists[selection][i]+'listmail";
					?>
					for(var i = 0; i < selectedLists['all'].length; i++){
						if(searchParent(window.document.getElementById(<?php echo $listidAll; ?>0'), 'tr').style.display == 'none') continue;
						<?php if(ACYMAILING_J30) echo "jQuery('label[for='+".$listidAll."0]').click();"; ?>
						window.document.getElementById(<?php echo $listidAll; ?>0').checked = true;
					}
					if(!selectedLists[selection]) return;
					for(i = 0; i < selectedLists[selection].length; i++){
						if(searchParent(window.document.getElementById(<?php echo $listidSelection; ?>1'), 'tr').style.display == 'none') continue;
						<?php if(ACYMAILING_J30) echo "jQuery('label[for='+".$listidSelection."1]').click();"; ?>
						window.document.getElementById(<?php echo $listidSelection; ?>1').checked = true;
					}
				}
				-->
			</script>
			<?php
			$selectList = array();
			$selectList[] = acymailing_selectOption('none', acymailing_translation('ACY_NONE'));
			foreach($languages as $oneLang => $values){
				if($oneLang == 'all') continue;
				$selectList[] = acymailing_selectOption($oneLang, ucfirst($oneLang));
			}
			$selectList[] = acymailing_selectOption('all', acymailing_translation('ACY_ALL'));
			echo acymailing_radio($selectList, "selectlists", 'onclick="updateStatus(this.value);"', 'value', 'text');
			?>
		</td>
	</tr>
<?php } ?>
	</tbody>
	</table>

	<script language="javascript" type="text/javascript">
		<!--
		function searchParent(elem, tag){
			tag = tag.toUpperCase();
			do{
				if(elem.nodeName === tag){
					return elem;
				}
			}while(elem = elem.parentNode);
			return null;
		}

		var listsCats = new Array();

		<?php
		foreach($listsPerCategory as $val => $listids){
			if(empty($val)) $val = '-1';
			echo "listsCats['".str_replace("'", "\'", $val)."'] = new Array('".implode("','", $listids)."'); ";
		}

		$listCatsSelection = 'listsCats[selection][i]+"listmail';

		?>
		function checkCats(selection){
			if(!listsCats[selection]) return;
			var unselect = true;
			for(var i = 0; i < listsCats[selection].length; i++){
				if(searchParent(window.document.getElementById(<?php echo $listCatsSelection; ?>0"), 'tr').style.display == 'none') continue;
				if(window.document.getElementById(<?php echo $listCatsSelection; ?>1").checked == true) continue;
				unselect = false;
				break;
			}
			for(i = 0; i < listsCats[selection].length; i++){
				if(searchParent(window.document.getElementById(<?php echo $listCatsSelection; ?>0"), 'tr').style.display == 'none') continue;
				if(unselect){
					<?php if(ACYMAILING_J30) echo 'jQuery("label[for="+'.$listCatsSelection.'0]").click();'; ?>
					window.document.getElementById(<?php echo $listCatsSelection; ?>0").checked = true;
				}else{
					<?php if(ACYMAILING_J30) echo 'jQuery("label[for="+'.$listCatsSelection.'1]").click();'; ?>
					window.document.getElementById(<?php echo $listCatsSelection; ?>1").checked = true;
				}
			}
		}
		-->
	</script>
<?php
