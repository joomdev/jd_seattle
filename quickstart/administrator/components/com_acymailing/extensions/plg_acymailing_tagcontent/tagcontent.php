<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php defined('_JEXEC') or die('Restricted access'); ?>
<?php

class plgAcymailingTagcontent extends JPlugin{
	public function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'tagcontent');
			$this->params = new acyParameter($plugin->params);
		}
		$this->acypluginsHelper = acymailing_get('helper.acyplugins');
		$tables = acymailing_getTableList();
		$this->newMulticats = in_array(acymailing_getPrefix().'content_multicats', $tables);
	}

	public function acymailing_getPluginType(){
		if($this->params->get('frontendaccess') == 'none' && !acymailing_isAdmin()) return;

		$onePlugin = new stdClass();
		$onePlugin->name = acymailing_translation('JOOMLA_CONTENT');
		$onePlugin->function = 'acymailingtagcontent_show';
		$onePlugin->help = 'plugin-tagcontent';

		return $onePlugin;
	}

	public function acymailingtagcontent_show(){

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		
		acymailing_loadLanguageFile('com_content', JPATH_SITE);

		$paramBase = ACYMAILING_COMPONENT.'.tagcontent';
		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'a.id', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$pageInfo->filter_cat = acymailing_getUserVar($paramBase.".filter_cat", 'filter_cat', '', 'int');
		$pageInfo->contenttype = acymailing_getUserVar($paramBase.".contenttype", 'contenttype', $this->params->get('default_type', 'intro'), 'string');
		$pageInfo->author = acymailing_getUserVar($paramBase.".author", 'author', $this->params->get('default_author', '0'), 'string');
		$pageInfo->titlelink = acymailing_getUserVar($paramBase.".titlelink", 'titlelink', $this->params->get('default_titlelink', 'link'), 'string');
		$pageInfo->lang = acymailing_getUserVar($paramBase.".lang", 'lang', '', 'string');
		$pageInfo->pict = acymailing_getUserVar($paramBase.".pict", 'pict', $this->params->get('default_pict', 1), 'string');
		$pageInfo->pictheight = acymailing_getUserVar($paramBase.".pictheight", 'pictheight', $this->params->get('maxheight', 150), 'string');
		$pageInfo->pictwidth = acymailing_getUserVar($paramBase.".pictwidth", 'pictwidth', $this->params->get('maxwidth', 150), 'string');


		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$picts = array();
		$picts[] = acymailing_selectOption("1", acymailing_translation('JOOMEXT_YES'));
		$pictureHelper = acymailing_get('helper.acypict');
		if($pictureHelper->available()) $picts[] = acymailing_selectOption("resized", acymailing_translation('RESIZED'));
		$picts[] = acymailing_selectOption("0", acymailing_translation('JOOMEXT_NO'));

		$contenttype = array();
		$contenttype[] = acymailing_selectOption("title", acymailing_translation('TITLE_ONLY'));
		$contenttype[] = acymailing_selectOption("intro", acymailing_translation('INTRO_ONLY'));
		$contenttype[] = acymailing_selectOption("text", acymailing_translation('FIELD_TEXT'));
		$contenttype[] = acymailing_selectOption("full", acymailing_translation('FULL_TEXT'));

		$titlelink = array();
		$titlelink[] = acymailing_selectOption("link", acymailing_translation('JOOMEXT_YES'));
		$titlelink[] = acymailing_selectOption("0", acymailing_translation('JOOMEXT_NO'));

		$authorname = array();
		$authorname[] = acymailing_selectOption("author", acymailing_translation('JOOMEXT_YES'));
		$authorname[] = acymailing_selectOption("0", acymailing_translation('JOOMEXT_NO'));

		$searchFields = array('a.id', 'a.title', 'a.alias', 'a.created_by', 'b.name', 'b.username');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		if(!empty($pageInfo->filter_cat)){
			$filters[] = "a.catid = ".$pageInfo->filter_cat;
		}

		if($this->params->get('displayart', 'all') == 'onlypub'){
			$filters[] = "a.state = 1";
		}else{
			$filters[] = "a.state != -2";
		}

		if(!acymailing_isAdmin()){
			$my = JFactory::getUser();

			if(!ACYMAILING_J16){
				$filters[] = 'a.`access` <= '.(int)$my->get('aid');
			}else{
				$groups = implode(',', $my->getAuthorisedViewLevels());
				$filters[] = 'a.`access` IN ('.$groups.')';
			}
		}

		if($this->params->get('frontendaccess') == 'author' && !acymailing_isAdmin()){
			$filters[] = "a.created_by = ".intval(acymailing_currentUserId());
		}

		$whereQuery = '';
		if(!empty($filters)){
			$whereQuery = ' WHERE ('.implode(') AND (', $filters).')';
		}

		$query = 'SELECT SQL_CALC_FOUND_ROWS a.*,b.name,b.username,a.created_by FROM '.acymailing_table('content', false).' as a';
		$query .= ' LEFT JOIN `#__users` AS b ON b.id = a.created_by';
		if(!empty($whereQuery)) $query .= $whereQuery;
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);

		if(!empty($pageInfo->search)){
			$rows = acymailing_search($pageInfo->search, $rows);
		}

		$pageInfo->elements->total = acymailing_loadResult('SELECT FOUND_ROWS()');
		$pageInfo->elements->page = count($rows);

		if(!ACYMAILING_J16){
			$query = 'SELECT a.id, a.id as catid, a.title as category, b.title as section, b.id as secid from #__categories as a ';
			$query .= 'INNER JOIN #__sections as b on a.section = b.id ORDER BY b.ordering,a.ordering';

			$categories = acymailing_loadObjectList($query, 'id');
			$categoriesValues = array();
			$categoriesValues[] = acymailing_selectOption('', acymailing_translation('ACY_ALL'));
			$currentSec = '';
			foreach($categories as $catid => $oneCategorie){
				if($currentSec != $oneCategorie->section){
					if(!empty($currentSec)) $this->values[] = acymailing_selectOption('</OPTGROUP>');
					$categoriesValues[] = acymailing_selectOption('<OPTGROUP>', $oneCategorie->section);
					$currentSec = $oneCategorie->section;
				}
				$categoriesValues[] = acymailing_selectOption($catid, $oneCategorie->category);
			}
		}else{
			$query = "SELECT * from #__categories WHERE `extension` = 'com_content' ORDER BY lft ASC";

			$categories = acymailing_loadObjectList($query, 'id');
			$categoriesValues = array();
			$categoriesValues[] = acymailing_selectOption('', acymailing_translation('ACY_ALL'));
			foreach($categories as $catid => $oneCategorie){
				$categories[$catid]->title = str_repeat('- - ', $categories[$catid]->level).$categories[$catid]->title;
				$categoriesValues[] = acymailing_selectOption($catid, $categories[$catid]->title);
			}
		}

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$tabs = acymailing_get('helper.acytabs');
		echo $tabs->startPane('joomlacontent_tab');
		echo $tabs->startPanel(acymailing_translation('JOOMLA_CONTENT'), 'joomlacontent_content');

		?>
		<script language="javascript" type="text/javascript">
			<!--
			var selectedContents = new Array();
			function applyContent(contentid, rowClass){
				var tmp = selectedContents.indexOf(contentid)
				if(tmp != -1){
					window.document.getElementById('content' + contentid).className = rowClass;
					delete selectedContents[tmp];
				}else{
					window.document.getElementById('content' + contentid).className = 'selectedrow';
					selectedContents.push(contentid);
				}
				updateTag();
			}

			function updateTag(){
				var tag = '';
				var otherinfo = '';
				for(var i = 0; i < document.adminForm.contenttype.length; i++){
					if(document.adminForm.contenttype[i].checked){
						selectedtype = document.adminForm.contenttype[i].value;
						otherinfo += '| type:' + document.adminForm.contenttype[i].value;
					}
				}

				if(document.adminForm.customfields){
					if(document.adminForm.customfields.length == undefined){
						if(document.adminForm.customfields.checked) otherinfo += "| custom:" + document.adminForm.customfields.value;
					}else{
						tmp = 0;
						for(i = 0; i < document.adminForm.customfields.length; i++){
							if(!document.adminForm.customfields[i].checked) continue;
							if(tmp == 0){
								tmp += 1;
								otherinfo += "| custom:" + document.adminForm.customfields[i].value;
							}else{
								otherinfo += "," + document.adminForm.customfields[i].value;
							}
						}
					}
				}

				for(var i = 0; i < document.adminForm.titlelink.length; i++){
					if(document.adminForm.titlelink[i].checked && document.adminForm.titlelink[i].value.length > 1){
						otherinfo += '| ' + document.adminForm.titlelink[i].value;
					}
				}

				var already = 0;
				if(document.adminForm.socialshare){
					for(var i = 0; i < document.adminForm.socialshare.length; i++){
						if(document.adminForm.socialshare[i].checked){
							if(already == 0){
								otherinfo += '| share:' + document.adminForm.socialshare[i].value;
								already++;
							}else{
								otherinfo += ',' + document.adminForm.socialshare[i].value;
							}
						}
					}
				}

				if(selectedtype != 'title'){
					for(var i = 0; i < document.adminForm.author.length; i++){
						if(document.adminForm.author[i].checked && document.adminForm.author[i].value.length > 1){
							otherinfo += '| ' + document.adminForm.author[i].value;
						}
					}
					for(var i = 0; i < document.adminForm.pict.length; i++){
						if(document.adminForm.pict[i].checked){
							otherinfo += '| pict:' + document.adminForm.pict[i].value;
							if(document.adminForm.pict[i].value == 'resized'){
								document.getElementById('pictsize').style.display = '';
								if(document.adminForm.pictwidth.value) otherinfo += '| maxwidth:' + document.adminForm.pictwidth.value;
								if(document.adminForm.pictheight.value) otherinfo += '| maxheight:' + document.adminForm.pictheight.value;
							}else{
								document.getElementById('pictsize').style.display = 'none';
							}
						}
					}
					document.getElementById('format').style.display = '';
				}else{
					document.getElementById('format').style.display = 'none';
				}

				if(document.adminForm.contentformat && document.adminForm.contentformat.value){
					otherinfo += '| format:' + document.adminForm.contentformat.value;
				}

				if(window.document.getElementById('jflang') && window.document.getElementById('jflang').value != ''){
					otherinfo += '|lang:';
					otherinfo += window.document.getElementById('jflang').value;
				}

				for(var i in selectedContents){
					if(selectedContents[i] && !isNaN(i)){
						tag = tag + '{joomlacontent:' + selectedContents[i] + otherinfo + '}<br />';
					}
				}
				setTag(tag);
			}
			//-->
		</script>
		<div class="onelineblockoptions">
			<table width="100%" class="acymailing_table">
				<tr>
					<td>
						<?php echo acymailing_translation('DISPLAY'); ?>
					</td>
					<td colspan="2">
						<?php echo acymailing_radio($contenttype, 'contenttype', 'size="1" onclick="updateTag();"', 'value', 'text', $pageInfo->contenttype); ?>
					</td>
					<td>
						<?php $jflanguages = acymailing_get('type.jflanguages');
						$jflanguages->onclick = 'onchange="updateTag();"';
						echo $jflanguages->display('lang', $pageInfo->lang); ?>
					</td>
				</tr>
				<tr id="format" class="acyplugformat">
					<td valign="top">
						<?php echo acymailing_translation('FORMAT'); ?>
					</td>
					<td valign="top">
						<?php echo $this->acypluginsHelper->getFormatOption('tagcontent'); ?>
					</td>
					<td valign="top"><?php echo acymailing_translation('DISPLAY_PICTURES'); ?></td>
					<td valign="top"><?php echo acymailing_radio($picts, 'pict', 'size="1" onclick="updateTag();"', 'value', 'text', $pageInfo->pict); ?>
						<span id="pictsize" <?php if($pageInfo->pict != 'resized') echo 'style="display:none;"'; ?>><br/><?php echo acymailing_translation('CAPTCHA_WIDTH') ?>
							<input name="pictwidth" type="text" onchange="updateTag();" value="<?php echo $pageInfo->pictwidth; ?>" style="width:30px;"/>
							x <?php echo acymailing_translation('CAPTCHA_HEIGHT') ?>
							<input name="pictheight" type="text" onchange="updateTag();" value="<?php echo $pageInfo->pictheight; ?>" style="width:30px;"/>
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo acymailing_translation('CLICKABLE_TITLE'); ?>
					</td>
					<td>
						<?php echo acymailing_radio($titlelink, 'titlelink', 'size="1" onclick="updateTag();"', 'value', 'text', $pageInfo->titlelink); ?>
					</td>
					<td>
						<?php echo acymailing_translation('AUTHOR_NAME'); ?>
					</td>
					<td>
						<?php echo acymailing_radio($authorname, 'author', 'size="1" onclick="updateTag();"', 'value', 'text', (string)$pageInfo->author); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo acymailing_translation('SHARE'); ?>
					</td>
				<?php
				$socialMedias = array('facebook' => 'Facebook',
									'linkedin' => 'LinkedIn',
									'twitter' => 'Twitter',
									'google' => 'Google+');

				$cpt = 1;
				foreach($socialMedias as $key => $oneSocial){
					if($cpt == 4){
						$cpt = 1;
						echo '</tr><tr><td/>';
					}
					echo '<td><input value="'.$key.'" name="socialshare" id="'.$key.'" type="checkbox" onclick="updateTag();" /> ';
					echo '<label for="'.$key.'">'.$oneSocial.'</label></td>';
					$cpt++;
				}
				while($cpt != 4){
					$cpt++;
					echo '<td/>';
				}
				?>
				</tr>
			</table>
<?php
		$jversion = preg_replace('#[^0-9\.]#i', '', JVERSION);
		if(version_compare($jversion, '3.7.0', '>=')){
			$query = 'SELECT id, title, group_id FROM #__fields WHERE context = "com_content.article" AND state = 1 ORDER BY title ASC';
			$customFields = acymailing_loadObjectList($query);

			if(!empty($customFields)){
				$query = 'SELECT id, title FROM #__fields_groups WHERE context = "com_content.article" AND state = 1 ORDER BY title ASC';
				$groups = acymailing_loadObjectList($query);
				$defaultGroup = new stdClass();
				$defaultGroup->id = 0;
				$defaultGroup->title = acymailing_translation('ACY_NO_GROUP');
				array_unshift($groups, $defaultGroup);

				echo '<div class="onelineblockoptions">
						<span class="acyblocktitle">'.acymailing_translation('EXTRA_FIELDS').'</span>
						<table class="acymailing_table" cellpadding="1">';
				foreach($groups as $oneGroup){
					echo '<tr><td style="font-weight: bold;">'.$oneGroup->title.'</td>';
					$i = 1;
					foreach($customFields as $oneCF){
						if($oneCF->group_id != $oneGroup->id) continue;
						if($i == 4){
							$i = 1;
							echo '</tr><tr><td/>';
						}
						echo '<td><input value="'.$oneCF->id.'" name="customfields" id="cf_'.$oneCF->id.'" type="checkbox" onclick="updateTag();"/>';
						echo '<label style="margin-left:5px" for="cf_'.$oneCF->id.'">'.$oneCF->title.'</label></td>';
						$i++;
					}
					while($i != 4){
						$i++;
						echo '<td/>';
					}
					echo '</tr>';
				}
				echo '</table></div>';
			}
		}
?>
		</div>
		<div class="onelineblockoptions">
			<table class="acymailing_table_options">
				<tr>
					<td width="100%">
						<?php acymailing_listingsearch($pageInfo->search); ?>
					</td>
					<td nowrap="nowrap">
						<?php echo acymailing_select($categoriesValues, 'filter_cat', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int)$pageInfo->filter_cat); ?>
					</td>
				</tr>
			</table>

			<table class="acymailing_table" cellpadding="1" width="100%">
				<thead>
				<tr>
					<th class="title">
					</th>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation('FIELD_TITLE'), 'a.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_AUTHOR'), 'b.name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation(ACYMAILING_J16 ? 'COM_CONTENT_PUBLISHED_DATE' : 'START PUBLISHING'), 'a.publish_up', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_CREATED'), 'a.created', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title titleid">
						<?php echo acymailing_gridSort(acymailing_translation('ACY_ID'), 'a.id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="6">
						<?php echo $pagination->getListFooter(); ?>
						<?php echo $pagination->getResultsCounter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php
				$k = 0;
				for($i = 0, $a = count($rows); $i < $a; $i++){
					$row =& $rows[$i];
					?>
					<tr id="content<?php echo $row->id ?>" class="<?php echo "row$k"; ?>" onclick="applyContent(<?php echo $row->id.",'row$k'" ?>);" style="cursor:pointer;">
						<td class="acytdcheckbox"></td>
						<td>
							<?php
							$text = '<b>'.acymailing_translation('JOOMEXT_ALIAS').': </b>'.$row->alias;
							echo acymailing_tooltip($text, $row->title, '', $row->title);
							?>
						</td>
						<td>
							<?php
							if(!empty($row->name)){
								$text = '<b>'.acymailing_translation('JOOMEXT_NAME').' : </b>'.$row->name;
								$text .= '<br /><b>'.acymailing_translation('ACY_USERNAME').' : </b>'.$row->username;
								$text .= '<br /><b>'.acymailing_translation('ACY_ID').' : </b>'.$row->created_by;
								echo acymailing_tooltip($text, $row->name, '', $row->name);
							}
							?>
						</td>
						<td align="center">
							<?php echo acymailing_date(strip_tags($row->publish_up), acymailing_translation('DATE_FORMAT_LC4')); ?>
						</td>
						<td align="center">
							<?php echo acymailing_date(strip_tags($row->created), acymailing_translation('DATE_FORMAT_LC4')); ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</tbody>
			</table>
		</div>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $pageInfo->filter->order->value; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $pageInfo->filter->order->dir; ?>"/>
		<?php
		echo $tabs->endPanel();
		echo $tabs->startPanel(acymailing_translation('TAG_CATEGORIES'), 'joomlacontent_auto');

		$type = acymailing_getVar('string', 'type');

		?>
		<script language="javascript" type="text/javascript">
			<!--
			window.onload = function(){
				if(window.document.getElementById('tagsauto')){
					window.document.getElementById('tagsauto').onchange = updateAutoTag;
				}
			}
			var selectedCategories = new Array();
			<?php if(!ACYMAILING_J16){ ?>
			function applyAutoContent(secid, catid, rowClass){
				if(selectedCategories[secid] && selectedCategories[secid][catid]){
					window.document.getElementById('content_sec' + secid + '_cat' + catid).className = rowClass;
					delete selectedCategories[secid][catid];
				}else{
					if(!selectedCategories[secid]) selectedCategories[secid] = new Array();
					if(secid == 0){
						for(var isec in selectedCategories){
							for(var icat in selectedCategories[isec]){
								if(selectedCategories[isec][icat] == 'content'){
									window.document.getElementById('content_sec' + isec + '_cat' + icat).className = 'row0';
									delete selectedCategories[isec][icat];
								}
							}
						}
					}else{
						if(selectedCategories[0] && selectedCategories[0][0]){
							window.document.getElementById('content_sec0_cat0').className = 'row0';
							delete selectedCategories[0][0];
						}

						if(catid == 0){
							for(var icat in selectedCategories[secid]){
								if(selectedCategories[secid][icat] == 'content'){
									window.document.getElementById('content_sec' + secid + '_cat' + icat).className = 'row0';
									delete selectedCategories[secid][icat];
								}
							}
						}else{
							if(selectedCategories[secid][0]){
								window.document.getElementById('content_sec' + secid + '_cat0').className = 'row0';
								delete selectedCategories[secid][0];
							}
						}
					}

					window.document.getElementById('content_sec' + secid + '_cat' + catid).className = 'selectedrow';
					selectedCategories[secid][catid] = 'content';
				}

				updateAutoTag();
			}
			<?php }else{ ?>
			function applyAutoContent(catid, rowClass){
				if(selectedCategories[catid]){
					window.document.getElementById('content_cat' + catid).className = rowClass;
					delete selectedCategories[catid];
				}else{
					window.document.getElementById('content_cat' + catid).className = 'selectedrow';
					selectedCategories[catid] = 'content';
				}

				updateAutoTag();
			}
			<?php } ?>

			function updateAutoTag(){
				tag = '{autocontent:';
				<?php if(!ACYMAILING_J16){ ?>
				for(var isec in selectedCategories){
					for(var icat in selectedCategories[isec]){
						if(selectedCategories[isec][icat] == 'content'){
							if(icat != 0){
								tag += 'cat' + icat + '-';
							}else{
								tag += 'sec' + isec + '-';
							}
						}
					}
				}
				<?php }else{ ?>
				for(var icat in selectedCategories){
					if(selectedCategories[icat] == 'content'){
						tag += icat + '-';
					}
				}
				<?php } ?>

				var already = 0;
				if(document.adminForm.autosocialshare){
					for(var i = 0; i < document.adminForm.autosocialshare.length; i++){
						if(document.adminForm.autosocialshare[i].checked){
							if(already == 0){
								tag += '| share:' + document.adminForm.autosocialshare[i].value;
								already++;
							}else{
								tag += ',' + document.adminForm.autosocialshare[i].value;
							}
						}
					}
				}

				if(document.adminForm.min_article && document.adminForm.min_article.value && document.adminForm.min_article.value != 0){
					tag += '| min:' + document.adminForm.min_article.value;
				}
				if(document.adminForm.max_article.value && document.adminForm.max_article.value != 0){
					tag += '| max:' + document.adminForm.max_article.value;
				}
				if(document.adminForm.contentorder.value){
					tag += "| order:" + document.adminForm.contentorder.value + "," + document.adminForm.contentorderdir.value;
				}
				if(document.adminForm.contentfilter && document.adminForm.contentfilter.value){
					tag += document.adminForm.contentfilter.value;
				}
				if(document.adminForm.meta_article && document.adminForm.meta_article.value){
					tag += '| meta:' + document.adminForm.meta_article.value;
				}

				for(var i = 0; i < document.adminForm.contenttypeauto.length; i++){
					if(document.adminForm.contenttypeauto[i].checked){
						selectedtype = document.adminForm.contenttypeauto[i].value;
						tag += '| type:' + document.adminForm.contenttypeauto[i].value;
					}
				}

				if(document.adminForm.customfieldsauto){
					if(document.adminForm.customfieldsauto.length == undefined){
						if(document.adminForm.customfieldsauto.checked) tag += "| custom:" + document.adminForm.customfieldsauto.value;
					}else{
						tmp = 0;
						for(i = 0; i < document.adminForm.customfieldsauto.length; i++){
							if(!document.adminForm.customfieldsauto[i].checked) continue;
							if(tmp == 0){
								tmp += 1;
								tag += "| custom:" + document.adminForm.customfieldsauto[i].value;
							}else{
								tag += "," + document.adminForm.customfieldsauto[i].value;
							}
						}
					}
				}

				for(var i = 0; i < document.adminForm.titlelinkauto.length; i++){
					if(document.adminForm.titlelinkauto[i].checked && document.adminForm.titlelinkauto[i].value.length > 1){
						tag += '|' + document.adminForm.titlelinkauto[i].value;
					}
				}
				if(selectedtype != 'title'){
					for(var i = 0; i < document.adminForm.authorauto.length; i++){
						if(document.adminForm.authorauto[i].checked && document.adminForm.authorauto[i].value.length > 1){
							tag += '|' + document.adminForm.authorauto[i].value;
						}
					}
					for(var i = 0; i < document.adminForm.pictauto.length; i++){
						if(document.adminForm.pictauto[i].checked){
							tag += '| pict:' + document.adminForm.pictauto[i].value;
							if(document.adminForm.pictauto[i].value == 'resized'){
								document.getElementById('pictsizeauto').style.display = '';
								if(document.adminForm.pictwidthauto.value) tag += '| maxwidth:' + document.adminForm.pictwidthauto.value;
								if(document.adminForm.pictheightauto.value) tag += '| maxheight:' + document.adminForm.pictheightauto.value;
							}else{
								document.getElementById('pictsizeauto').style.display = 'none';
							}
						}
					}
					document.getElementById('formatauto').style.display = '';
				}else{
					document.getElementById('formatauto').style.display = 'none';
				}

				if(document.getElementById('contentformatautoinvert').value == 1) tag += '| invert';
				if(document.adminForm.contentformatauto && document.adminForm.contentformatauto.value){
					tag += '| format:' + document.adminForm.contentformatauto.value;
				}

				if(document.adminForm.cols && document.adminForm.cols.value > 1){
					tag += '| cols:' + document.adminForm.cols.value;
				}
				if(window.document.getElementById('jflangauto') && window.document.getElementById('jflangauto').value != ''){
					tag += '| lang:' + window.document.getElementById('jflangauto').value;
				}
				if(window.document.getElementById('jlang') && window.document.getElementById('jlang').value != ''){
					tag += '| language:' + window.document.getElementById('jlang').value;
				}

				if(window.document.getElementById('tagsauto')){
					var tmp = 0;
					for(var i = 0; i < window.document.getElementById('tagsauto').length; i++){
						if(window.document.getElementById('tagsauto')[i].selected){
							if(tmp == 0){
								tag += '| tags:' + window.document.getElementById('tagsauto')[i].value;
								tmp = 1;
							}else{
								tag += ',' + window.document.getElementById('tagsauto')[i].value;
							}
						}
					}
				}

				tag += '}';

				setTag(tag);
			}
			//-->
		</script>
		<div class="onelineblockoptions">
			<table width="100%" class="acymailing_table">
				<tr>
					<td>
						<?php echo acymailing_translation('DISPLAY'); ?>
					</td>
					<td colspan="2">
						<?php echo acymailing_radio($contenttype, 'contenttypeauto', 'size="1" onclick="updateAutoTag();"', 'value', 'text', $this->params->get('default_type', 'intro')); ?>
					</td>
					<td id="languagesauto">
						<?php $jflanguages = acymailing_get('type.jflanguages');
						$jflanguages->onclick = 'onchange="updateAutoTag();"';
						$jflanguages->id = 'jflangauto';
						echo $jflanguages->display('langauto');
						if(empty($jflanguages->found)){
							echo $jflanguages->displayJLanguages('jlangauto');
						}
						?>
					</td>
				</tr>
				<tr id="formatauto" class="acyplugformat">
					<td valign="top">
						<?php echo acymailing_translation('FORMAT'); ?>
					</td>
					<td valign="top">
						<?php echo $this->acypluginsHelper->getFormatOption('tagcontent', 'TOP_LEFT', false, 'updateAutoTag'); ?>
					</td>
					<td valign="top"><?php echo acymailing_translation('DISPLAY_PICTURES'); ?></td>
					<td valign="top"><?php echo acymailing_radio($picts, 'pictauto', 'size="1" onclick="updateAutoTag();"', 'value', 'text', $this->params->get('default_pict', '1')); ?>
						<span id="pictsizeauto" <?php if($this->params->get('default_pict', '1') != 'resized') echo 'style="display:none;"'; ?> ><br/><?php echo acymailing_translation('CAPTCHA_WIDTH') ?>
							<input name="pictwidthauto" type="text" onchange="updateAutoTag();" value="<?php echo $this->params->get('maxwidth', '150'); ?>" style="width:30px;"/>
							x <?php echo acymailing_translation('CAPTCHA_HEIGHT') ?>
							<input name="pictheightauto" type="text" onchange="updateAutoTag();" value="<?php echo $this->params->get('maxheight', '150'); ?>" style="width:30px;"/>
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo acymailing_translation('CLICKABLE_TITLE'); ?>
					</td>
					<td>
						<?php echo acymailing_radio($titlelink, 'titlelinkauto', 'size="1" onclick="updateAutoTag();"', 'value', 'text', $this->params->get('default_titlelink', 'link')); ?>
					</td>
					<td>
						<?php echo acymailing_translation('AUTHOR_NAME'); ?>
					</td>
					<td>
						<?php echo acymailing_radio($authorname, 'authorauto', 'size="1" onclick="updateAutoTag();"', 'value', 'text', (string)$this->params->get('default_author', '0')); ?>
					</td>
				</tr>
				<tr>
					<?php if(version_compare(JVERSION, '3.1.0', '>=')){ ?>
						<td valign="top">
							<?php echo acymailing_translation('TAGS'); ?>
						</td>
						<td>
							<?php
							$form = JForm::getInstance('acytagcontenttags', JPATH_SITE.DS.'components'.DS.'com_acymailing'.DS.'params'.DS.'tagcontenttags.xml');
							foreach($form->getFieldset('tagcontenttagfield') as $field){
								echo $field->input;
							}
							?>
						</td>
					<?php }else{ ?>
						<td colspan="2"></td>
					<?php } ?>
					<td valign="top"><?php echo acymailing_translation('FIELD_COLUMNS'); ?></td>
					<td valign="top">
						<select name="cols" style="width:150px" onchange="updateAutoTag();" size="1">
							<?php for($o = 1; $o < 11; $o++) echo '<option value="'.$o.'">'.$o.'</option>'; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo acymailing_translation('MAX_ARTICLE'); ?>
					</td>
					<td>
						<input type="text" name="max_article" style="width:50px" value="20" onchange="updateAutoTag();"/>
					</td>
					<td>
						<?php echo acymailing_translation('ACY_ORDER'); ?>
					</td>
					<td>
						<?php
						$values = array('id' => 'ACY_ID', 'ordering' => 'ACY_ORDERING', 'created' => 'CREATED_DATE', 'modified' => 'MODIFIED_DATE', 'title' => 'FIELD_TITLE', 'hits' => 'ACY_HITS');
						if(ACYMAILING_J16) $values['publish_up'] = 'COM_CONTENT_PUBLISHED_DATE';
						echo $this->acypluginsHelper->getOrderingField($values, 'id', 'DESC', 'updateAutoTag');
						?>
					</td>
				</tr>
				<?php if($this->params->get('metaselect')){ ?>
					<tr>
						<td>
							<?php echo acymailing_translation('META_KEYWORDS'); ?>
						</td>
						<td colspan="3">
							<input type="text" name="meta_article" style="width:200px" value="" onchange="updateAutoTag();"/>
						</td>
					</tr>
				<?php } ?>
				<?php if($type == 'autonews'){ ?>
					<tr>
						<td>
							<?php echo acymailing_translation('MIN_ARTICLE'); ?>
						</td>
						<td>
							<input type="text" name="min_article" style="width:50px" value="1" onchange="updateAutoTag();"/>
						</td>
						<td>
							<?php echo acymailing_translation('JOOMEXT_FILTER'); ?>
						</td>
						<td>
							<?php $filter = acymailing_get('type.contentfilter');
							$filter->onclick = "updateAutoTag();";
							echo $filter->display('contentfilter', '|filter:created'); ?>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td>
						<?php echo acymailing_translation('SHARE'); ?>
					</td>
					<?php
					$cpt = 1;
					foreach($socialMedias as $key => $oneSocial){
						if($cpt == 4){
							$cpt = 1;
							echo '</tr><tr><td/>';
						}
						echo '<td><input value="'.$key.'" name="autosocialshare" id="auto'.$key.'" type="checkbox" onclick="updateAutoTag();" /> ';
						echo '<label for="auto'.$key.'">'.$oneSocial.'</label></td>';
						$cpt++;
					}
					while($cpt != 4){
						$cpt++;
						echo '<td/>';
					}
					?>
				</tr>
			</table>
<?php
		if(version_compare($jversion, '3.7.0', '>=') && !empty($customFields)){
			echo '<div class="onelineblockoptions">
					<span class="acyblocktitle">'.acymailing_translation('EXTRA_FIELDS').'</span>
					<table class="acymailing_table" cellpadding="1">';
			foreach($groups as $oneGroup){
				echo '<tr><td style="font-weight: bold;">'.$oneGroup->title.'</td>';
				$i = 1;
				foreach($customFields as $oneCF){
					if($oneCF->group_id != $oneGroup->id) continue;
					if($i == 4){
						$i = 1;
						echo '</tr><tr><td/>';
					}
					echo '<td><input value="'.$oneCF->id.'" name="customfieldsauto" id="autocf_'.$oneCF->id.'" type="checkbox" onclick="updateAutoTag();"/>';
					echo '<label style="margin-left:5px" for="autocf_'.$oneCF->id.'">'.$oneCF->title.'</label></td>';
					$i++;
				}
				while($i != 4){
					$i++;
					echo '<td/>';
				}
				echo '</tr>';
			}
			echo '</table></div>';
		}
?>
		</div>

		<div class="onelineblockoptions">
			<table class="acymailing_table" cellpadding="1" width="100%">
				<thead>
				<tr>
					<th class="title"></th>
					<?php if(!ACYMAILING_J16){ ?>
						<th class="title">
							<?php echo acymailing_translation('SECTION'); ?>
						</th>
					<?php } ?>
					<th class="title">
						<?php echo acymailing_translation('TAG_CATEGORIES'); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$k = 0;
				if(!ACYMAILING_J16){
					?>
					<tr id="content_sec0_cat0" class="<?php echo "row$k"; ?>" onclick="applyAutoContent(0,0,'<?php echo "row$k" ?>');" style="cursor:pointer;">
						<td class="acytdcheckbox"></td>
						<td style="font-weight: bold;">
							<?php
							echo acymailing_translation('ACY_ALL');
							?>
						</td>
						<td style="text-align:center;font-weight: bold;">
							<?php
							echo acymailing_translation('ACY_ALL');
							?>
						</td>
					</tr>

					<?php
				}

				$k = 1 - $k;
				$currentSection = '';
				foreach($categories as $row){

					if(!ACYMAILING_J16 && $currentSection != $row->section){
						?>
						<tr id="content_sec<?php echo $row->secid ?>_cat0" class="<?php echo "row$k"; ?>" onclick="applyAutoContent(<?php echo $row->secid ?>,0,'<?php echo "row$k" ?>');" style="cursor:pointer;">
							<td class="acytdcheckbox"></td>
							<td style="font-weight: bold;">
								<?php
								echo $row->section;
								?>
							</td>
							<td style="text-align:center;font-weight: bold;">
								<?php
								echo acymailing_translation('ACY_ALL');
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
						$currentSection = $row->section;
					}
					if(!ACYMAILING_J16){
						?>
						<tr id="content_sec<?php echo $row->secid ?>_cat<?php echo $row->catid ?>" class="<?php echo "row$k"; ?>" onclick="applyAutoContent(<?php echo $row->secid ?>,<?php echo $row->catid ?>,'<?php echo "row$k" ?>');" style="cursor:pointer;">
							<td class="acytdcheckbox"></td>
							<td>
							</td>
							<td>
								<?php
								echo $row->category;
								?>
							</td>
						</tr>
						<?php
					}else{ ?>
						<tr id="content_cat<?php echo $row->id ?>" class="<?php echo "row$k"; ?>" onclick="applyAutoContent(<?php echo $row->id ?>,'<?php echo "row$k" ?>');" style="cursor:pointer;">
							<td class="acytdcheckbox"></td>
							<td>
								<?php
								echo $row->title;
								?>
							</td>
						</tr>
					<?php }
					$k = 1 - $k;
				}
				?>
				</tbody>
			</table>
		</div>
		<?php

		echo $tabs->endPanel();
		echo $tabs->endPane();
	}

	public function acymailing_replacetags(&$email, $send = true){
		$this->_replaceAuto($email);
		$this->_replaceArticles($email);
	}

	private function _replaceArticles(&$email){
		$tags = $this->acypluginsHelper->extractTags($email, 'joomlacontent');
		if(empty($tags)) return;

		$this->newslanguage = new stdClass();
		if(!empty($email->language)){
			$this->newslanguage = acymailing_loadObject('SELECT lang_id, lang_code FROM #__languages WHERE sef = '.acymailing_escapeDB($email->language).' LIMIT 1');
		}

		$this->currentcatid = -1;
		$this->readmore = empty($email->template->readmore) ? acymailing_translation('JOOMEXT_READ_MORE') : '<img class="readmorepict" src="'.ACYMAILING_LIVE.$email->template->readmore.'" alt="'.acymailing_translation('JOOMEXT_READ_MORE', true).'" />';

		require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';

		if($this->params->get('integration') == 'flexicontent' && file_exists(JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'helpers'.DS.'route.php')){
			require_once JPATH_SITE.DS.'components'.DS.'com_flexicontent'.DS.'helpers'.DS.'route.php';
		}

		$tagsReplaced = array();
		foreach($tags as $i => $oneTag){
			if(isset($tagsReplaced[$i])) continue;
			$tagsReplaced[$i] = $this->_replaceContent($oneTag);
		}

		$this->acypluginsHelper->replaceTags($email, $tagsReplaced, true);
	}

	private function _replaceContent(&$tag){
		$oldFormat = empty($tag->format);

		if($tag->id == 'current'){
			$article_id = acymailing_getVar('int', 'articleId');
			if(empty($article_id)) return;
			$tag->id = $article_id;
		}
		if(!ACYMAILING_J16){
			$query = 'SELECT a.*,b.name as authorname, c.alias as catalias, c.title as cattitle, c.image AS catpict, s.alias as secalias, s.title as sectitle FROM '.acymailing_table('content', false).' as a ';
			$query .= 'LEFT JOIN '.acymailing_table('users', false).' as b ON a.created_by = b.id ';
			$query .= ' LEFT JOIN '.acymailing_table('categories', false).' AS c ON c.id = a.catid ';
			$query .= ' LEFT JOIN '.acymailing_table('sections', false).' AS s ON s.id = a.sectionid ';
			$query .= 'WHERE a.id = '.$tag->id.' LIMIT 1';
		}else{
			$query = 'SELECT a.*,b.name as authorname, c.alias as catalias, c.title as cattitle, c.params AS catparams FROM '.acymailing_table('content', false).' as a ';
			$query .= 'LEFT JOIN '.acymailing_table('users', false).' as b ON a.created_by = b.id ';
			$query .= ' LEFT JOIN '.acymailing_table('categories', false).' AS c ON c.id = a.catid ';
			$query .= 'WHERE a.id = '.$tag->id.' LIMIT 1';
		}

		$article = acymailing_loadObject($query);

		if(empty($article)){
			if(acymailing_isAdmin()) acymailing_enqueueMessage('The article "'.$tag->id.'" could not be loaded', 'notice');
			return '';
		}

		if(empty($tag->lang) && !empty($this->newslanguage) && !empty($this->newslanguage->lang_code)) $tag->lang = $this->newslanguage->lang_code.','.$this->newslanguage->lang_id;

		$this->acypluginsHelper->translateItem($article, $tag, 'content');

		$varFields = array();
		foreach($article as $fieldName => $oneField){
			$varFields['{'.$fieldName.'}'] = $oneField;
		}

		$this->acypluginsHelper->cleanHtml($article->introtext);
		$this->acypluginsHelper->cleanHtml($article->fulltext);


		if($this->params->get('integration') == 'jreviews' && !empty($article->images)){
			$firstpict = explode('|', trim(reset(explode("\n", $article->images))).'|||||||');
			if(!empty($firstpict[0])){
				$picturePath = file_exists(ACYMAILING_ROOT.'images'.DS.'stories'.DS.str_replace('/', DS, $firstpict[0])) ? ACYMAILING_LIVE.'images/stories/'.$firstpict[0] : ACYMAILING_LIVE.'images/'.$firstpict[0];
				$myPict = '<img src="'.$picturePath.'" alt="" hspace="5" style="margin:5px" align="left" border="'.intval($firstpict[5]).'" />';
				$article->introtext = $myPict.$article->introtext;
			}
		}
		$completeId = $article->id;
		$completeCat = $article->catid;

		if(!empty($article->alias)) $completeId .= ':'.$article->alias;
		if(!empty($article->catalias)) $completeCat .= ':'.$article->catalias;

		if(empty($tag->itemid)){
			if(!ACYMAILING_J16){
				$completeSec = $article->sectionid;
				if(!empty($article->secalias)) $completeSec .= ':'.$article->secalias;
				if($this->params->get('integration') == 'flexicontent' && class_exists('FlexicontentHelperRoute')){
					$link = FlexicontentHelperRoute::getItemRoute($completeId, $completeCat, $completeSec);
				}else{
					$link = ContentHelperRoute::getArticleRoute($completeId, $completeCat, $completeSec);
				}
			}else{
				if($this->params->get('integration') == 'flexicontent' && class_exists('FlexicontentHelperRoute')){
					$link = FlexicontentHelperRoute::getItemRoute($completeId, $completeCat);
				}else{
					$link = ContentHelperRoute::getArticleRoute($completeId, $completeCat);
				}
			}
		}else{
			$link = 'index.php?option=com_content&view=article&id='.$completeId.'&catid='.$completeCat;
		}


		if($this->params->get('integration') == 'flexicontent' && !class_exists('FlexicontentHelperRoute')){
			$link = 'index.php?option=com_flexicontent&view=items&id='.$completeId;
		}elseif($this->params->get('integration') == 'jaggyblog'){
			$link = 'index.php?option=com_jaggyblog&task=viewpost&id='.$completeId;
		}

		if(!empty($tag->itemid)) $link .= '&Itemid='.$tag->itemid;
		if(!empty($tag->lang)) $link .= (strpos($link, '?') ? '&' : '?').'lang='.substr($tag->lang, 0, strpos($tag->lang, ACYMAILING_J16 ? '-' : ','));
		if(!empty($tag->autologin)) $link .= (strpos($link, '?') ? '&' : '?').'user={usertag:username|urlencode}&passw={usertag:password|urlencode}';

		if(empty($tag->lang) && !empty($article->language) && $article->language != '*'){
			if(!isset($this->langcodes[$article->language])){
				$this->langcodes[$article->language] = acymailing_loadResult('SELECT sef FROM #__languages WHERE lang_code = '.acymailing_escapeDB($article->language).' ORDER BY `published` DESC LIMIT 1');
				if(empty($this->langcodes[$article->language])) $this->langcodes[$article->language] = $article->language;
			}
			$link .= (strpos($link, '?') ? '&' : '?').'lang='.$this->langcodes[$article->language];
		}

		$nonsefLink = $link;
		$mainurl = acymailing_mainURL($nonsefLink);
		$nonsefLink = $mainurl.$nonsefLink;

		$link = acymailing_frontendLink($link);
		$varFields['{link}'] = $link;

		$afterTitle = '';
		$afterArticle = '';
		$contentText = '';
		$pictPath = '';

		if(!empty($tag->author)){
			$authorName = empty($article->created_by_alias) ? $article->authorname : $article->created_by_alias;
			if($tag->type == 'title') $afterTitle .= '<br />';
			$afterTitle .= '<span class="authorname">'.$authorName.'</span><br />';
		}

		$dateFormat = empty($tag->dateformat) ? acymailing_translation('DATE_FORMAT_LC2') : $tag->dateformat;
		if(!empty($tag->created)){
			if($tag->type == 'title') $afterTitle .= '<br />';
			$varFields['{createddate}'] = acymailing_date($article->created, $dateFormat);
			$afterTitle .= '<span class="createddate">'.$varFields['{createddate}'].'</span><br />';
		}

		if(!empty($tag->modified)){
			if($tag->type == 'title') $afterTitle .= '<br />';
			$varFields['{modifieddate}'] = acymailing_date($article->modified, $dateFormat);
			$afterTitle .= '<span class="modifieddate">'.$varFields['{modifieddate}'].'</span><br />';
		}

		if(!isset($tag->pict) && $tag->type != 'title'){
			if($this->params->get('removepictures', 'never') == 'always' || ($this->params->get('removepictures', 'never') == 'intro' && $tag->type == "intro")){
				$tag->pict = 0;
			}else{
				$tag->pict = 1;
			}
		}

		if(strpos($article->introtext, 'jseblod') !== false && file_exists(ACYMAILING_ROOT.'plugins'.DS.'content'.DS.'cckjseblod.php')){
			global $mainframe;
			include_once(ACYMAILING_ROOT.'plugins'.DS.'content'.DS.'cckjseblod.php');
			if(function_exists('plgContentCCKjSeblod')){
				$paramsContent = JComponentHelper::getParams('com_content');
				$article->text = $article->introtext.$article->fulltext;
				plgContentCCKjSeblod($article, $paramsContent);
				$article->introtext = $article->text;
				$article->fulltext = '';
			}
		}

		if($tag->type != "title"){
			if($tag->type == "intro"){
				$forceReadMore = false;
				$mytag = new stdClass();
				$mytag->wrap = $this->params->get('wordwrap', 0);
				if(empty($article->fulltext)){
					$article->introtext = $this->acypluginsHelper->wrapText($article->introtext, $mytag);
					if(!empty($this->acypluginsHelper->wraped)) $forceReadMore = true;
				}
			}

			if(empty($article->fulltext) || $tag->type != "text"){
				$contentText .= $article->introtext;
			}

			if($tag->type != "intro" && !empty($article->fulltext)){
				if($tag->type != "text" && !empty($article->introtext) && !preg_match('#^<[div|p]#i', trim($article->fulltext))){
					$contentText .= '<br />';
				}
				$contentText .= $article->fulltext;
			}

			$contentText = $this->acypluginsHelper->wrapText($contentText, $tag);
			if(!empty($this->acypluginsHelper->wraped)) $forceReadMore = true;

			if(!empty($tag->clean)){
				$contentText = strip_tags($contentText, '<p><br><span><ul><li><h1><h2><h3><h4><a>');
			}

			$varFields['{picthtml}'] = '';
			if(ACYMAILING_J16 && !empty($article->images) && !empty($tag->pict) && empty($tag->nomainimage)){
				$picthtml = '';
				$images = json_decode($article->images);
				$pictVar = ($tag->type == 'intro') ? 'image_intro' : 'image_fulltext';
				$floatVar = ($tag->type == 'intro') ? 'float_intro' : 'float_fulltext';
				if(!empty($images->$pictVar)){
					if($images->$floatVar != 'right'){
						if(empty($tag->format)) $tag->format = 'TOP_LEFT';
						$images->$floatVar = 'left';
					}elseif(empty($tag->format)) $tag->format = 'TOP_RIGHT';
					$style = 'float:'.$images->$floatVar.';padding-'.(($images->$floatVar == 'right') ? 'left' : 'right').':10px;padding-bottom:10px;';
					if(!empty($tag->link) && empty($tag->nopictlink)) $picthtml .= '<a target="_blank" href="'.$link.'" style="text-decoration:none" >';
					$alt = '';
					$altVar = $pictVar.'_alt';
					if(!empty($images->$altVar)) $alt = $images->$altVar;
					$picthtml .= '<img'.(empty($tag->nopictstyle) ? ' style="'.$style.'"' : '').' alt="'.$alt.'" border="0" src="'.acymailing_rootURI().$images->$pictVar.'" />';
					$pictPath = acymailing_rootURI().$images->$pictVar;
					if(!empty($tag->link) && empty($tag->nopictlink)) $picthtml .= '</a>';
					$varFields['{picthtml}'] = $picthtml;
				}
			}

			$contentText = preg_replace('/^\s*(<img[^>]*>)\s*(?:<br[^>]*>\s*)*/i', '$1', $contentText);

			if(!empty($tag->custom)){
				$tag->custom = explode(',', $tag->custom);
				acymailing_arrayToInteger($tag->custom);

				$articleCFValues = acymailing_loadObjectList('SELECT fv.value, f.id, f.fieldparams, f.params, f.type, f.label, f.name, f.default_value 
																FROM #__fields AS f 
																LEFT JOIN #__fields_values AS fv ON fv.field_id = f.id AND fv.item_id = '.intval($tag->id).' 
																WHERE  f.id IN ('.implode(',', $tag->custom).')');

				$fields = array();
				foreach($articleCFValues as $oneVal){
					$fields[$oneVal->id]['values'][] = $oneVal->value;
					$fields[$oneVal->id]['field'] = $oneVal;
				}

				foreach($fields as $oneField){
					if(!empty($oneField['field']->fieldparams)) $oneField['field']->fieldparams = json_decode($oneField['field']->fieldparams, true);
					$oneField['field']->params = json_decode($oneField['field']->params, true);

					if($oneField['values'][0] === NULL){
						if(($oneField['field']->type == 'user' && empty($oneField['field']->default_value)) || ($oneField['field']->type != 'user' && strlen($oneField['field']->default_value) == 0)) continue;
						$oneField['values'] = array($oneField['field']->default_value);
					}

					foreach($oneField['values'] as &$oneFieldVal){
						switch($oneField['field']->type){
							case 'radio':
							case 'list':
							case 'checkboxes':
								foreach($oneField['field']->fieldparams['options'] as $oneOPT){
									if($oneOPT['value'] == $oneFieldVal){
										$oneFieldVal = $oneOPT['name'];
										break;
									}
								}
								break;

							case 'usergrouplist':
								if(empty($this->usergroups)) $this->usergroups = acymailing_loadObjectList('SELECT id, title FROM #__usergroups', 'id');

								$oneFieldVal = $this->usergroups[$oneFieldVal]->title;
								break;

							case 'imagelist':
								if($oneFieldVal == -1){
									$oneFieldVal = NULL;
									continue;
								}

								if(strlen($oneField['field']->fieldparams['directory']) > 1) $oneFieldVal = '/'.$oneFieldVal;
								else $oneField['field']->fieldparams['directory'] = '';
								$oneFieldVal = '<img src="images/'.$oneField['field']->fieldparams['directory'].$oneFieldVal.'" />';
								break;

							case 'url':
								$oneFieldVal = '<a target="_blank" href="'.$oneFieldVal.'">'.$oneFieldVal.'</a>';
								break;

							case 'sql':
								if(empty($oneField['field']->options)){
									$oneField['field']->options = acymailing_loadObjectList($oneField['field']->fieldparams['query'], 'value');
								}

								$oneFieldVal = $oneField['field']->options[$oneFieldVal]->text;
								break;

							case 'user':
								$oneFieldVal = acymailing_currentUserName($oneFieldVal);
								break;

							case 'media':
								$oneFieldVal = '<img src="'.$oneFieldVal.'" />';
								break;

							case 'calendar':
								$format = $oneField['field']->fieldparams['showtime'] == '1' ? 'Y-m-d H:i' : 'Y-m-d';
								$oneFieldVal = acymailing_date(strtotime($oneFieldVal), $format);
								break;
						}
					}

					$replaceme = trim(implode(', ', $oneField['values']), ', ');
					if(empty($replaceme)) continue;

					$varFields['{custom:'.$oneField['field']->name.'}'] = $replaceme;

					if($oneField['field']->params['showlabel'] == '1'){
						$label = $oneField['field']->label.': ';
						if($oneField['field']->type == 'imagelist') $label .= '<br/>';
						$replaceme = $label.$replaceme;
					}
					$afterArticle .= '<br />'.$replaceme;
				}
			}
			
			if(file_exists(JPATH_SITE.DS.'plugins'.DS.'attachments') && empty($tag->noattach)){
				try{
					$query = 'SELECT display_name, url, filename '.'FROM #__attachments '.'WHERE (parent_entity = "article" '.'AND parent_id = '.intval($tag->id).')';
					if(ACYMAILING_J16){
						$query .= ' OR (parent_entity = "category" '.'AND parent_id = '.intval($article->catid).')';
					}
					$attachments = acymailing_loadObjectList($query);
				}catch(Exception $e){
					$attachments = array();
				}

				if(!empty($attachments)){
					$afterArticle .= '<br />'.acymailing_translation('ATTACHED_FILES').' :';
					foreach($attachments as $oneAttachment){
						$afterArticle .= '<br /><a target="_blank" href="'.$oneAttachment->url.'">'.(empty($oneAttachment->display_name) ? $oneAttachment->filename : $oneAttachment->display_name).'</a>';
					}
				}
			}

			if(!empty($tag->share)){
				$links = array();
				$shareOpt = explode(',', $tag->share);
				foreach($shareOpt as $socialNetwork){
					$knownNetwork = true;
					$socialNetwork = strtolower(trim($socialNetwork));
					if($socialNetwork == 'facebook'){
						$linkShare = 'http://www.facebook.com/sharer.php?u='.urlencode($nonsefLink).'&t='.urlencode($article->title);
						$picSrc = (file_exists(ACYMAILING_MEDIA.'plugins'.DS.'facebook.png') ? 'media/com_acymailing/plugins/facebook.png' : 'media/com_acymailing/images/facebookshare.png');
						$altText = 'Facebook';
					}elseif($socialNetwork == 'twitter'){
						$text = acymailing_translation_sprintf('SHARE_TEXT', $nonsefLink);
						$linkShare = 'http://twitter.com/home?status='.urlencode($text);
						$picSrc = (file_exists(ACYMAILING_MEDIA.'plugins'.DS.'twitter.png') ? 'media/com_acymailing/plugins/twitter.png' : 'media/com_acymailing/images/twittershare.png');
						$altText = 'Twitter';
					}elseif($socialNetwork == 'linkedin'){
						$linkShare = 'http://www.linkedin.com/shareArticle?mini=true&url='.urlencode($nonsefLink).'&title='.urlencode($article->title);
						$picSrc = (file_exists(ACYMAILING_MEDIA.'plugins'.DS.'linkedin.png') ? 'media/com_acymailing/plugins/linkedin.png' : 'media/com_acymailing/images/linkedin.png');
						$altText = 'LinkedIn';
					}elseif($socialNetwork == 'google'){
						$linkShare = 'https://plus.google.com/share?url='.urlencode($nonsefLink);
						$picSrc = (file_exists(ACYMAILING_MEDIA.'plugins'.DS.'google.png') ? 'media/com_acymailing/plugins/google.png' : 'media/com_acymailing/images/google_plusshare.png');
						$altText = 'Google+';
					}elseif($socialNetwork == 'mailto'){
						$linkShare = 'mailto:?subject='.urlencode($article->title).'&body='.urlencode($article->title.' ('.$nonsefLink.')');
						$picSrc = (file_exists(ACYMAILING_MEDIA.'plugins'.DS.'mailto.png') ? 'media/com_acymailing/plugins/mailto.png' : 'media/com_acymailing/images/mailto.png');
						$altText = 'MailTo';
					}else{
						$knownNetwork = false;
						acymailing_display('Network not found: '.$socialNetwork.'. Availables networks are facebook, twitter, linkedin, google and mailto.', 'warning');
					}
					if($knownNetwork){
						array_push($links, '<a target="_blank" href="'.$linkShare.'" title="'.acymailing_translation_sprintf('SOCIAL_SHARE', $altText).'"><img alt="'.$altText.'" src="'.$picSrc.'" /></a>');
					}
				}
				$afterArticle .= '<br />'.(!empty($tag->sharetxt) ? $tag->sharetxt.' ' : '').implode(' ', $links);
			}
		}

		if(!empty($tag->jtags) && version_compare(JVERSION, '3.1.0', '>=')){
			$tags = acymailing_loadObjectList('SELECT t.id, t.alias, t.title FROM #__tags AS t JOIN #__contentitem_tag_map AS m ON t.id = m.tag_id WHERE t.published = 1 AND m.type_alias = "com_content.article" AND m.content_item_id = '.intval($tag->id));
			if(!empty($tags)){
				$afterArticle .= '<br />';
				foreach($tags as $oneTag){
					$afterArticle .= ' <a target="_blank" href="index.php?option=com_tags&view=tag&id='.$oneTag->id.'-'.$oneTag->alias.'">'.$oneTag->title.'</a> ';
				}
			}
		}

		$readMoreText = empty($tag->readmore) ? $this->readmore : $tag->readmore;
		$varFields['{readmore}'] = '<a class="acymailing_readmore_link" style="text-decoration:none;" target="_blank" href="'.$link.'"><span class="acymailing_readmore">'.$readMoreText.'</span></a>';

		if($tag->type == "intro" && empty($tag->noreadmore) && (!empty($article->fulltext) || $forceReadMore)){
			if(!empty($afterArticle)) $afterArticle .= '<br />';
			$afterArticle .= $varFields['{readmore}'];
		}

		$format = new stdClass();
		$format->tag = $tag;
		$format->title = empty($tag->notitle) ? $article->title : '';
		$format->afterTitle = $afterTitle;
		$format->afterArticle = $afterArticle;
		$format->imagePath = $pictPath;
		$format->description = $contentText;
		$format->link = empty($tag->link) ? '' : $link;
		$format->cols = 2;
		$result = $this->acypluginsHelper->getStandardDisplay($format);

		if(!empty($tag->theme)){
			if(preg_match('#<img[^>]*>#Uis', $article->introtext.$article->fulltext, $pregresult)){
				$cleanContent = strip_tags($result, '<p><br><span><ul><li><h1><h2><h3><h4><a>');
				$tdwidth = (empty($tag->maxwidth) ? $this->params->get('maxwidth', 150) : $tag->maxwidth) + 20;
				$result = '<table cellspacing="0" width="500" cellpadding="0" border="0" ><tr><td class="contentpicture" width="'.$tdwidth.'" valign="top" align="center"><a href="'.$link.'" target="_blank" style="border:0px;text-decoration:none">'.$pregresult[0].'</a></td><td class="contenttext">'.$cleanContent.'</td></tr></table>';
			}
		}

		if($tag->type != 'title') $result = '<div class="acymailing_content">'.$result.'</div>';

		if(!(empty($tag->cattitle) && empty($tag->catpict)) && ((!strpos($article->catid, ',') && $this->currentcatid != $article->catid) || (strpos($article->catid, ',') && !in_array($this->currentcatid, explode(',', $article->catid))))){
			if(strpos($article->catid, ',')){
				$catids = explode(',', $article->catid);
				$this->currentcatid = $catids[0];
			}else{
				$this->currentcatid = $article->catid;
			}

			if(ACYMAILING_J16){
				$params = json_decode($article->catparams);
				$article->catpict = $params->image;
			}

			$resultTitle = $article->cattitle;

			if(!empty($tag->catpict) && !empty($article->catpict)){
				$style = '';
				if(!empty($tag->catmaxwidth)) $style .= 'max-width:'.intval($tag->catmaxwidth).'px;';
				if(!empty($tag->catmaxheight)) $style .= 'max-height:'.intval($tag->catmaxheight).'px;';
				$resultTitle = '<img'.(empty($style) ? '' : ' style="'.$style.'"').' alt="" src="'.$article->catpict.'" />';
				if(!empty($tag->cattitlelink)) $resultTitle = '<a target="_blank" href="index.php?option=com_content&view=category&id='.$this->currentcatid.'">'.$resultTitle.'</a>';
			}else{
				if(!empty($tag->cattitlelink)) $resultTitle = '<a target="_blank" href="index.php?option=com_content&view=category&id='.$this->currentcatid.'">'.$resultTitle.'</a>';
				$resultTitle = '<h3 class="cattitle">'.$resultTitle.'</h3>';
			}

			$result = $resultTitle.$result;
		}

		if($oldFormat){
			if(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'tagcontent_html.php')){
				ob_start();
				require(ACYMAILING_MEDIA.'plugins'.DS.'tagcontent_html.php');
				$result = ob_get_clean();
			}elseif(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'tagcontent.php')){
				ob_start();
				require(ACYMAILING_MEDIA.'plugins'.DS.'tagcontent.php');
				$result = ob_get_clean();
			}
		}elseif(!empty($tag->template) && file_exists(ACYMAILING_MEDIA.'plugins'.DS.$tag->template)){
			ob_start();
			require(ACYMAILING_MEDIA.'plugins'.DS.$tag->template);
			$result = ob_get_clean();
		}
		$result = str_replace(array_keys($varFields), $varFields, $result);

		$result = $this->acypluginsHelper->removeJS($result);

		$tag->maxheight = empty($tag->maxheight) ? $this->params->get('maxheight', 150) : $tag->maxheight;
		$tag->maxwidth = empty($tag->maxwidth) ? $this->params->get('maxwidth', 150) : $tag->maxwidth;
		$result = $this->acypluginsHelper->managePicts($tag, $result);

		if(!empty($tag->maxchar) && strlen(strip_tags($result)) > $tag->maxchar){
			$result = strip_tags($result);
			for($i = $tag->maxchar; $i > 0; $i--){
				if($result[$i] == ' ') break;
			}
			if(!empty($i)) $result = substr($result, 0, $i).@$tag->textafter;
		}

		return $result;
	}

	private function _replaceAuto(&$email){
		$this->acymailing_generateautonews($email);
		if(empty($this->tags)) return;
		$this->acypluginsHelper->replaceTags($email, $this->tags, true);
	}

	public function acymailing_generateautonews(&$email){
		$time = time();

		$tags = $this->acypluginsHelper->extractTags($email, 'autocontent');
		$return = new stdClass();
		$return->status = true;
		$return->message = '';
		$this->tags = array();

		if(empty($tags)) return $return;

		foreach($tags as $oneTag => $parameter){
			if(isset($this->tags[$oneTag])) continue;
			$allcats = explode('-', $parameter->id);
			$selectedArea = array();
			foreach($allcats as $oneCat){
				if(!ACYMAILING_J16){
					$sectype = substr($oneCat, 0, 3);
					$num = substr($oneCat, 3);
					if(empty($num)) continue;
					if($sectype == 'cat'){
						$selectedArea[] = 'catid = '.(int)$num;
					}elseif($sectype == 'sec'){
						$selectedArea[] = 'sectionid = '.(int)$num;
					}
				}else{
					if(empty($oneCat)) continue;
					$selectedArea[] = intval($oneCat);
				}
			}

			$query = 'SELECT DISTINCT a.id FROM `#__content` as a ';
			$where = array();

			if(!empty($parameter->tags) && version_compare(JVERSION, '3.1.0', '>=')){
				$tagsArray = explode(',', $parameter->tags);
				acymailing_arrayToInteger($tagsArray);
				if(!empty($tagsArray)){
					foreach($tagsArray as $oneTagId){
						$query .= 'JOIN #__contentitem_tag_map AS tagsmap'.$oneTagId.' ON (a.id = tagsmap'.$oneTagId.'.content_item_id AND tagsmap'.$oneTagId.'.type_alias LIKE "com_content.article" AND tagsmap'.$oneTagId.'.tag_id = '.$oneTagId.') ';
					}
				}
			}

			if(!empty($parameter->featured)){
				if(ACYMAILING_J16){
					$where[] = 'a.featured = 1';
				}else{
					$query .= 'JOIN `#__content_frontpage` as b ON a.id = b.content_id ';
					$where[] = 'b.content_id IS NOT NULL';
				}
			}

			if(!empty($parameter->nofeatured)){
				if(ACYMAILING_J16){
					$where[] = 'a.featured = 0';
				}else{
					$query .= 'LEFT JOIN `#__content_frontpage` as b ON a.id = b.content_id ';
					$where[] = 'b.content_id IS NULL';
				}
			}

			if(ACYMAILING_J16 && !empty($parameter->subcats) && !empty($selectedArea)){
				$catinfos = acymailing_loadObjectList('SELECT lft,rgt FROM #__categories WHERE id IN ('.implode(',', $selectedArea).')');
				if(!empty($catinfos)){
					$whereCats = array();
					foreach($catinfos as $onecat){
						$whereCats[] = 'lft > '.$onecat->lft.' AND rgt < '.$onecat->rgt;
					}
					$othercats = acymailing_loadResultArray('SELECT id FROM #__categories WHERE ('.implode(') OR (', $whereCats).')');
					$selectedArea = array_merge($selectedArea, $othercats);
				}
			}

			if($this->newMulticats && (!empty($selectedArea) || !empty($parameter->excludedcats))) $query .= ' JOIN `#__multicats_content_catid` as mcc ON a.id = mcc.item_id ';

			if(!empty($selectedArea)){
				if(!ACYMAILING_J16){
					$where[] = implode(' OR ', $selectedArea);
				}else{
					$filter_cat = '`catid` IN ('.implode(',', $selectedArea).')';
					if(file_exists(JPATH_SITE.DS.'components'.DS.'com_multicats')){
						if($this->newMulticats){
							$filter_cat = 'mcc.`catid` REGEXP "^([0-9]+,)*'.implode('(,[0-9]+)*$" OR mcc.`catid` REGEXP "^([0-9]+,)*', $selectedArea).'(,[0-9]+)*$"';
						}else{
							$filter_cat = '`catid` REGEXP "^([0-9]+,)*'.implode('(,[0-9]+)*$" OR `catid` REGEXP "^([0-9]+,)*', $selectedArea).'(,[0-9]+)*$"';
						}
					}
					$where[] = $filter_cat;
				}
			}

			if(!empty($parameter->excludedcats)){
				$excludedCats = explode('-', $parameter->excludedcats);
				acymailing_arrayToInteger($excludedCats);
				$filter_cat = '`catid` NOT IN ("'.implode('","', $excludedCats).'")';
				if(file_exists(JPATH_SITE.DS.'components'.DS.'com_multicats')){
					if($this->newMulticats){
						$filter_cat = 'mcc.`catid` NOT REGEXP "^([0-9]+,)*'.implode('(,[0-9]+)*$" AND mcc.`catid` NOT REGEXP "^([0-9]+,)*', $excludedCats).'(,[0-9]+)*$"';
					}else{
						$filter_cat = '`catid` NOT REGEXP "^([0-9]+,)*'.implode('(,[0-9]+)*$" AND `catid` NOT REGEXP "^([0-9]+,)*', $excludedCats).'(,[0-9]+)*$"';
					}
				}
				$where[] = $filter_cat;
			}

			if(!empty($parameter->filter) && !empty($email->params['lastgenerateddate'])){
				$condition = '(`publish_up` > \''.date('Y-m-d H:i:s', $email->params['lastgenerateddate'] - date('Z')).'\' AND `publish_up` < \''.date('Y-m-d H:i:s', $time - date('Z')).'\')';
				$condition .= ' OR (`created` > \''.date('Y-m-d H:i:s', $email->params['lastgenerateddate'] - date('Z')).'\' AND `created` < \''.date('Y-m-d H:i:s', $time - date('Z')).'\')';
				if($parameter->filter == 'modify'){
					$modify = '(`modified` > \''.date('Y-m-d H:i:s', $email->params['lastgenerateddate'] - date('Z')).'\' AND `modified` < \''.date('Y-m-d H:i:s', $time - date('Z')).'\')';
					if(!empty($parameter->maxpublished)) $modify = '('.$modify.' AND `publish_up` > \''.date('Y-m-d H:i:s', time() - date('Z') - ((int)$parameter->maxpublished * 60 * 60 * 24)).'\')';
					$condition .= ' OR '.$modify;
				}

				$where[] = $condition;
			}

			if(!empty($parameter->maxcreated)){
				$date = $parameter->maxcreated;
				if(strpos($parameter->maxcreated, '[time]') !== false) $date = acymailing_replaceDate(str_replace('[time]', '{time}', $parameter->maxcreated));
				if(!is_numeric($date)) $date = strtotime($parameter->maxcreated);
				if(empty($date)){
					acymailing_display('Wrong date format ('.$parameter->maxcreated.' in '.$oneTag.'), please use YYYY-MM-DD', 'warning');
				}
				$where[] = '`created` < '.acymailing_escapeDB(date('Y-m-d H:i:s', $date)).' OR `publish_up` < '.acymailing_escapeDB(date('Y-m-d H:i:s', $date));
			}else{
				$where[] = '`publish_up` < \''.date('Y-m-d H:i:s', $time - date('Z')).'\'';
			}

			if(!empty($parameter->mincreated)){
				$date = $parameter->mincreated;
				if(strpos($parameter->mincreated, '[time]') !== false) $date = acymailing_replaceDate(str_replace('[time]', '{time}', $parameter->mincreated));
				if(!is_numeric($date)) $date = strtotime($parameter->mincreated);
				if(empty($date)){
					acymailing_display('Wrong date format ('.$parameter->mincreated.' in '.$oneTag.'), please use YYYY-MM-DD', 'warning');
				}
				$where[] = '`created` > '.acymailing_escapeDB(date('Y-m-d H:i:s', $date)).' OR `publish_up` > '.acymailing_escapeDB(date('Y-m-d H:i:s', $date));
			}


			if(!empty($parameter->meta)){
				$allMetaTags = explode(',', $parameter->meta);
				$metaWhere = array();
				foreach($allMetaTags as $oneMeta){
					if(empty($oneMeta)) continue;
					$metaWhere[] = "`metakey` LIKE '%".acymailing_getEscaped($oneMeta, true)."%'";
				}
				if(!empty($metaWhere)) $where[] = implode(' OR ', $metaWhere);
			}

			$where[] = '`publish_down` > \''.date('Y-m-d H:i:s', $time - date('Z')).'\' OR `publish_down` = 0';
			if(empty($parameter->unpublished)){
				$where[] = 'state = 1';
			}else{
				$where[] = 'state = 0';
			}

			if(!ACYMAILING_J16){
				if(isset($parameter->access)){
					$where[] = 'access <= '.intval($parameter->access);
				}else{
					if($this->params->get('contentaccess', 'registered') == 'registered'){
						$where[] = 'access <= 1';
					}elseif($this->params->get('contentaccess', 'registered') == 'public') $where[] = 'access = 0';
				}
			}elseif(isset($parameter->access)){
				if(strpos($parameter->access, ',')){
					$allAccess = explode(',', $parameter->access);
					acymailing_arrayToInteger($allAccess);
					$where[] = 'access IN ('.implode(',', $allAccess).')';
				}else{
					$where[] = 'access = '.intval($parameter->access);
				}
			}

			if(ACYMAILING_J16 && !empty($parameter->language)){
				$allLanguages = explode(',', $parameter->language);
				$langWhere = 'language IN (';
				foreach($allLanguages as $oneLanguage){
					$langWhere .= acymailing_escapeDB(trim($oneLanguage)).',';
				}
				$where[] = trim($langWhere, ',').')';
			}

			$query .= ' WHERE ('.implode(') AND (', $where).')';
			if(!empty($parameter->order)){
				$ordering = explode(',', $parameter->order);
				if($ordering[0] == 'rand'){
					$query .= ' ORDER BY rand()';
				}else{
					$query .= ' ORDER BY `'.acymailing_secureField($ordering[0]).'` '.acymailing_secureField($ordering[1]).' , a.`id` DESC';
				}
			}

			$start = '';
			if(!empty($parameter->start)) $start = intval($parameter->start).',';

			if(empty($parameter->max)) $parameter->max = 100;

			$query .= ' LIMIT '.$start.(int)$parameter->max;

			$allArticles = acymailing_loadResultArray($query);

			if(!empty($parameter->min) && count($allArticles) < $parameter->min){
				$return->status = false;
				$return->message = 'Not enough articles for the tag '.$oneTag.' : '.count($allArticles).' / '.$parameter->min.' between '.acymailing_getDate($email->params['lastgenerateddate']).' and '.acymailing_getDate($time);
			}

			$stringTag = empty($parameter->noentrytext) ? '' : $parameter->noentrytext;
			if(!empty($allArticles)){
				if(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'autocontent.php')){
					ob_start();
					require(ACYMAILING_MEDIA.'plugins'.DS.'autocontent.php');
					$stringTag = ob_get_clean();
				}else{
					$arrayElements = array();
					$numArticle = 1;
					foreach($allArticles as $oneArticleId){
						$args = array();
						$args[] = 'joomlacontent:'.$oneArticleId;
						$args[] = 'num:'.$numArticle++;
						if(!empty($parameter->invert) && $numArticle % 2 == 1) $args[] = 'invert';
						if(!empty($parameter->type)) $args[] = 'type:'.$parameter->type;
						if(!empty($parameter->custom)) $args[] = 'custom:'.$parameter->custom;
						if(!empty($parameter->format)) $args[] = 'format:'.$parameter->format;
						if(!empty($parameter->template)) $args[] = 'template:'.$parameter->template;
						if(!empty($parameter->jtags)) $args[] = 'jtags';
						if(!empty($parameter->link)) $args[] = 'link';
						if(!empty($parameter->author)) $args[] = 'author';
						if(!empty($parameter->autologin)) $args[] = 'autologin';
						if(!empty($parameter->cattitle)) $args[] = 'cattitle';
						if(!empty($parameter->cattitlelink)) $args[] = 'cattitlelink';
						if(!empty($parameter->lang)) $args[] = 'lang:'.$parameter->lang;
						if(!empty($parameter->theme)) $args[] = 'theme';
						if(!empty($parameter->clean)) $args[] = 'clean';
						if(!empty($parameter->notitle)) $args[] = 'notitle';
						if(!empty($parameter->nopictstyle)) $args[] = 'nopictstyle';
						if(!empty($parameter->nopictlink)) $args[] = 'nopictlink';
						if(!empty($parameter->created)) $args[] = 'created';
						if(!empty($parameter->noattach)) $args[] = 'noattach';
						if(!empty($parameter->itemid)) $args[] = 'itemid:'.$parameter->itemid;
						if(!empty($parameter->noreadmore)) $args[] = 'noreadmore';
						if(isset($parameter->pict)) $args[] = 'pict:'.$parameter->pict;
						if(!empty($parameter->wrap)) $args[] = 'wrap:'.$parameter->wrap;
						if(!empty($parameter->maxwidth)) $args[] = 'maxwidth:'.$parameter->maxwidth;
						if(!empty($parameter->maxheight)) $args[] = 'maxheight:'.$parameter->maxheight;
						if(!empty($parameter->readmore)) $args[] = 'readmore:'.$parameter->readmore;
						if(!empty($parameter->dateformat)) $args[] = 'dateformat:'.$parameter->dateformat;
						if(!empty($parameter->textafter)) $args[] = 'textafter:'.$parameter->textafter;
						if(!empty($parameter->maxchar)) $args[] = 'maxchar:'.$parameter->maxchar;
						if(!empty($parameter->share)) $args[] = 'share:'.$parameter->share;
						if(!empty($parameter->sharetxt)) $args[] = 'sharetxt:'.$parameter->sharetxt;
						if(!empty($parameter->catpict)) $args[] = 'catpict';
						if(!empty($parameter->catmaxwidth)) $args[] = 'catmaxwidth:'.$parameter->catmaxwidth;
						if(!empty($parameter->catmaxheight)) $args[] = 'catmaxheight:'.$parameter->catmaxheight;
						if(!empty($parameter->nomainimage)) $args[] = 'nomainimage';
						$arrayElements[] = '{'.implode('|', $args).'}';
					}
					$stringTag = $this->acypluginsHelper->getFormattedResult($arrayElements, $parameter);
				}
			}
			$this->tags[$oneTag] = $stringTag;
		}

		return $return;
	}
}//endclass
