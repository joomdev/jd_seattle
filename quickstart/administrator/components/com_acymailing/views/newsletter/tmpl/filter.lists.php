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
if(empty($currentPage)) $currentPage = 'mail';

foreach($this->lists as $oneList){
	$listids[] = $oneList->listid;
}
if(count($this->lists) > 10){
	?>
	<script language="javascript" type="text/javascript">
		<!--
		var listids = new Array(<?php echo implode(',', $listids); ?>);
		function acymailing_searchAList(){
			var filter = document.getElementById("acymailing_searchList").value.toLowerCase();
			for(var i = 0; i < listids.length; i++){
				var itemName = document.getElementById("listName_" + listids[i]).innerHTML.toLowerCase();
				if(itemName.indexOf(filter) > -1){
					document.getElementById("acylistrow_" + listids[i]).style.display = "table-row";
				}else{
					document.getElementById("acylistrow_" + listids[i]).style.display = "none";
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
$categorizedLists = array();
foreach($orderedList as $oneCategory){
	$categorizedLists = array_merge($categorizedLists, $oneCategory);
}

echo '<table class="acymailing_table" id="lists_choice"><tbody>';

$filter_list = acymailing_getVar('int', 'filter_list');
if(empty($filter_list)) $filter_list = acymailing_getVar('int', 'listid');
$selectedLists = explode(',', acymailing_getVar('string', 'listids'));

foreach($categorizedLists as $row){
	if(empty($row->category)) $row->category = acymailing_translation('ACY_NO_CATEGORY');
	if(count($allCats) > 1 && (empty($currentCatgeory) || $row->category != $currentCatgeory)){
		$currentCatgeory = $row->category;
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td colspan="2">
				<a href="#" onclick="checkCats('<?php echo htmlspecialchars(str_replace("'", "\'", $row->category == acymailing_translation('ACY_NO_CATEGORY') ? -1 : $row->category), ENT_QUOTES, "UTF-8"); ?>'); return false;"><strong><?php echo htmlspecialchars($row->category, ENT_QUOTES, "UTF-8"); ?></strong></a>
			</td>
		</tr>
		<?php
	}

	$checked = (bool)($row->{$currentPage.'id'} || // The list was selected before
					  (empty($row->mailid) && empty($this->mail->mailid) && $filter_list == $row->listid) || // When creating a new newsletter when filtering by list from the listing
					  (empty($this->mail->mailid) && count($this->lists) == 1) || // When creating a newsletter and only one list available
					  (in_array($row->listid, $selectedLists))); // Selected lists on the previous page

	$classList = $checked ? 'acy_list_checked' : 'acy_list_unchecked';
	echo '<tr id="acylistrow_'.$row->listid.'" class="row'.$k.' '.$classList.'" onclick="toggleList(\''.$row->listid.'\', null);">
		<td style="display:none;" id="listId_'.$row->listid.'">'.$row->listid.'</td>
		<td style="display:none;" id="listName_'.$row->listid.'">'.$row->name.'</td>
		<td class="acytdcheckbox"><input name="data[list'.$currentPage.']['.$row->listid.']" id="datalistmail'.$row->listid.'" type="hidden" value="'.(int)$checked.'" /></td>
		<td>
			<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>';
	$text = '<b>'.acymailing_translation('ACY_ID').' : </b>'.$row->listid;
	$text .= '<br />'.$row->description;
	echo acymailing_tooltip($text, $row->name, 'tooltip.png', $row->name).'
		</td>
	</tr>';

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
						if(document.getElementById('acylistrow_' + selectedLists['all'][i]).style.display == 'none') continue;
						toggleList(selectedLists['all'][i], 0);
					}
					if(!selectedLists[selection]) return;
					for(i = 0; i < selectedLists[selection].length; i++){
						if(document.getElementById('acylistrow_' + selectedLists[selection][i]).style.display == 'none') continue;
						toggleList(selectedLists[selection][i], 1);
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
	function toggleList(id, value){
		var valueField = document.getElementById('datalistmail' + id);
		var row = document.getElementById('acylistrow_' + id);

		if(value == 1 || (valueField.value == 0 && value != 0)){
			valueField.value = 1;
			row.className = row.className.replace('acy_list_unchecked', 'acy_list_checked');
		}else{
			valueField.value = 0;
			row.className = row.className.replace('acy_list_checked', 'acy_list_unchecked');
		}
	}

	var listsCats = new Array();

	<?php
	foreach($listsPerCategory as $val => $listids){
		if(empty($val)) $val = '-1';
		echo "listsCats['".str_replace("'", "\'", $val)."'] = new Array('".implode("','", $listids)."'); ";
	}

	?>
	function checkCats(selection){
		if(!listsCats[selection]) return;
		var select = 0;
		for(var i = 0; i < listsCats[selection].length; i++){
			if(document.getElementById('acylistrow_' + listsCats[selection][i]).style.display == 'none') continue;
			if(document.getElementById('datalistmail' + listsCats[selection][i]).value == 0){
				select = 1;
				break;
			}
		}

		for(i = 0; i < listsCats[selection].length; i++){
			if(document.getElementById('acylistrow_' + listsCats[selection][i]).style.display == 'none') continue;
			toggleList(listsCats[selection][i], select);
		}
	}
	-->
</script>
