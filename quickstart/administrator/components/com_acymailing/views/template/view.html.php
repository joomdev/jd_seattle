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


class TemplateViewTemplate extends acymailingView{

	var $selection = array('a.tempid', 'a.name', 'a.description', 'a.created', 'a.published', 'a.premium', 'a.ordering', 'a.thumb');
	var $filters = array();
	var $button = true;
	var $chosen = false;

	function display($tpl = null){

		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$config = acymailing_config();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName().$this->getLayout();
		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'a.ordering', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'asc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$pageInfo->category = acymailing_getUserVar($paramBase.".category", 'category', '0', 'string');

		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$this->filters[] = "a.name LIKE $searchVal OR a.description LIKE $searchVal OR a.tempid LIKE $searchVal";
		}

		if(!empty($pageInfo->category) && $pageInfo->category != acymailing_translation('ACY_ALL_CATEGORIES')){
			$this->filters[] = 'a.category LIKE '.acymailing_escapeDB($pageInfo->category);
		}

		$query = 'SELECT '.implode(',', $this->selection).' FROM '.acymailing_table('template').' as a';
		if(!empty($this->filters)){
			$query .= ' WHERE ('.implode(') AND (', $this->filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		try{
			$this->rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);
		}catch(Exception $e){
			$this->rows = null;
		}

		if($this->rows === null){
			acymailing_display(isset($e) ? $e->getMessage() : substr(strip_tags(acymailing_getDBError()), 0, 200).'...', 'error');
			if(file_exists(ACYMAILING_BACK.'install.joomla.php')){
				include_once(ACYMAILING_BACK.'install.joomla.php');
				$installClass = new acymailingInstall();
				$installClass->fromVersion = '4.1.0';
				$installClass->update = true;
				$installClass->updateSQL();
			}
		}

		$queryCount = 'SELECT COUNT(a.tempid) FROM '.acymailing_table('template').' as a';
		if(!empty($this->filters)){
			$queryCount .= ' WHERE ('.implode(') AND (', $this->filters).')';
		}
		
		$pageInfo->elements->total = acymailing_loadResult($queryCount);
		$pageInfo->elements->page = count($this->rows);

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		if($this->button){
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->popup('import', acymailing_translation('IMPORT'), acymailing_completeLink("template&task=upload", true), 450, 250);

			$acyToolbar->custom('export', acymailing_translation('ACY_EXPORT'), 'export', true);
			$acyToolbar->divider();
			$acyToolbar->add();
			$acyToolbar->edit();
			if(acymailing_isAllowed($config->get('acl_templates_copy', 'all'))){
				$acyToolbar->copy();
			}
			if(acymailing_isAllowed($config->get('acl_templates_delete', 'all'))) $acyToolbar->delete();

			$acyToolbar->divider();
			$acyToolbar->help('template', 'listing');
			$acyToolbar->setTitle(acymailing_translation('ACY_TEMPLATES'), 'template');
			$acyToolbar->display();
		}


		$toggleClass = acymailing_get('helper.toggle');

		$order = new stdClass();
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}

		$filters = new stdClass();


		$this->filters = $filters;
		$this->order = $order;
		$this->toggleClass = $toggleClass;
		$this->rows = $this->rows;
		$this->pageInfo = $pageInfo;
		$this->pagination = $pagination;
	}

	function form(){
		$tempid = acymailing_getCID('tempid');
		$config = acymailing_config();

		if(!empty($tempid)){
			$templateClass = acymailing_get('class.template');
			$template = $templateClass->get($tempid);
			if(!empty($template->body)) $template->body = acymailing_absoluteURL($template->body);

			if(empty($template->tempid)){
				acymailing_display('Template '.$tempid.' not found', 'error');
				$tempid = 0;
			}
		}

		if(empty($tempid)){
			$template = new stdClass();
			$template->body = '';
			$template->tempid = 0;
			$template->published = 1;
			$template->access = 'all';
			$template->category = '';
			$template->thumb = '';
			$template->readmore = '';
			$template->header = '';
		}

		$editor = acymailing_get('helper.editor');
		$editor->setTemplate($template->tempid);
		$editor->name = 'editor_body';
		$editor->content = $template->body;
		$editor->prepareDisplay();

		$script = '
			document.addEventListener("DOMContentLoaded", function(){
				acymailing.submitbutton = function(pressbutton) {
					if (pressbutton == \'cancel\') {
						acymailing.submitform(pressbutton,document.adminForm);
						return;
					}
					
					if(pressbutton == \'save\' || pressbutton == \'test\' || pressbutton == \'apply\'){
						var emailVars = ["fromemail","replyemail"];
						var val = "";
						for(var key in emailVars){
							if(isNaN(key)) continue;
							val = document.getElementById(emailVars[key]).value;
							if(!validateEmail(val, emailVars[key])){
								return;
							}
						}
					}';
		$script .= 'if(window.document.getElementById("name").value.length < 2){alert(\''.acymailing_translation('ENTER_TITLE', true).'\'); return false;}';
		$script .= "if(pressbutton == 'test' && window.document.getElementById('sendtest') && window.document.getElementById('sendtest').style.display == 'none'){ window.document.getElementById('sendtest').style.display = 'block'; return false;}";
		$script .= $editor->jsCode();
		$script .= 'acymailing.submitform(pressbutton,document.adminForm);
				};
			 }); ';

		$script .= "var zoneToTag = 'editor';
			function insertTag(tag){
				if(zoneToTag == 'editor'){
					try{
						jInsertEditorText(tag,'editor_body');
						return true;
					} catch(err){
						alert('Your editor does not enable AcyMailing to automatically insert the tag, please copy/paste it manually in your Newsletter');
						return false;
					}
				}else{
					try{
						simpleInsert(zoneToTag, tag);
						return true;
					} catch(err){
						alert('Error inserting the tag in the '+ zoneToTag + 'zone. Please copy/paste it manually in your Newsletter.');
						return false;
					}
				}
			}
			function simpleInsert(myField, myValue) {
				myField = document.getElementById(myField);
				if (document.selection) {
					myField.focus();
					sel = document.selection.createRange();
					sel.text = myValue;
				} else if (myField.selectionStart || myField.selectionStart == '0') {
					var startPos = myField.selectionStart;
					var endPos = myField.selectionEnd;
					myField.value = myField.value.substring(0, startPos)
						+ myValue
						+ myField.value.substring(endPos, myField.value.length);
				} else if (myField.tagName == 'DIV') {
					myField.innerHTML += myValue;
					document.getElementById('subject').value += myValue;
				} else {
					myField.value += myValue;
				}
			}
			document.addEventListener('DOMContentLoaded', function(){
				setTimeout(function() {
					document.getElementById('htmlfieldset').addEventListener('click', function(){
						zoneToTag = 'editor';
					});	
					
					var ediframe = document.getElementById('htmlfieldset').getElementsByTagName('iframe');
					if(ediframe && ediframe[0]){
						var children = ediframe[0].contentDocument.getElementsByTagName('*');
						for (var i = 0; i < children.length; i++) {
							children[i].addEventListener('click', function(){
								zoneToTag = 'editor';
							});			
						}
					}		
				}, 1000);
			});";

		$script .= 'function addStyle(){
			var myTable=window.document.getElementById("classtable");
			var newline = document.createElement(\'tr\');
			var column = document.createElement(\'td\');
			var column2 = document.createElement(\'td\');
			var input = document.createElement(\'input\');
			var input2 = document.createElement(\'input\');
			input.type = \'text\';
			input2.type = \'text\';
			input.style.width = \'180px\';
			input2.style.width = \'200px\';
			input.name = \'otherstyles[classname][]\';
			input2.name = \'otherstyles[style][]\';
			input.placeholder = "'.str_replace('"', '\"', acymailing_translation('CLASS_NAME', true)).'";
			input2.placeholder = "'.str_replace('"', '\"', acymailing_translation('CSS_STYLE', true)).'";
			column.appendChild(input);
			column2.appendChild(input2);
			newline.appendChild(column);
			newline.appendChild(column2);
			myTable.appendChild(newline);
		}';

		$script .= 'var currentValueId = \'\';
				function showthediv(valueid, e){
					if(currentValueId != valueid){
						try{
							document.getElementById(\'wysija\').style.left = jQuery(e.target).position().left-50+"px";
							document.getElementById(\'wysija\').style.top = jQuery(e.target).position().top-40+"px";
						}catch(err){
							document.getElementById(\'wysija\').style.left = e.x-50+"px";
							document.getElementById(\'wysija\').style.top = e.y-40+"px";
						}
						currentValueId = valueid;
					}
					document.getElementById(\'wysija\').style.display = \'block\';
					initDiv();
				}

				function spanChange(span){
					input = currentValueId;
					if (document.getElementById(span).className == span.toLowerCase()+"elementselected"){
						document.getElementById(span).className = span.toLowerCase()+"element";
						if(span == "B"){
							document.getElementById("name_"+currentValueId).style.fontWeight = "";
							document.getElementById("style_"+currentValueId).value = document.getElementById("style_"+currentValueId).value.replace(/font-weight *: *bold(;)?/i, "");
						}
						if(span == "I"){
							document.getElementById("name_"+currentValueId).style.fontStyle = "";
							document.getElementById("style_"+currentValueId).value = document.getElementById("style_"+currentValueId).value.replace(/font-style *: *italic(;)?/i, "");
						}
						if(span == "U"){
							document.getElementById("name_"+currentValueId).style.textDecoration="";
							document.getElementById("style_"+currentValueId).value = document.getElementById("style_"+currentValueId).value.replace(/text-decoration *: *underline(;)?/i,"");
						}

					}else{
						 document.getElementById(span).className = span.toLowerCase()+"elementselected";
						if(span == "B"){
							document.getElementById("name_"+currentValueId).style.fontWeight = "bold";
							document.getElementById("style_"+currentValueId).value = document.getElementById("style_"+currentValueId).value + "font-weight:bold;";
						}
						if(span == "I"){
							document.getElementById("name_"+currentValueId).style.fontStyle = "italic";
							document.getElementById("style_"+currentValueId).value = document.getElementById("style_"+currentValueId).value + "font-style:italic;";
						}
						if(span == "U"){
							document.getElementById("name_"+currentValueId).style.textDecoration="underline";
							document.getElementById("style_"+currentValueId).value = document.getElementById("style_"+currentValueId).value + "text-decoration:underline;";
						}
					}
				}
				function getValueSelect(){
					selec = currentValueId;
					var myRegex2 = new RegExp(/font-size *:[^;]*;/i);
					var MyValue = document.getElementById("style_select_wysija").value;
					document.getElementById("name_"+currentValueId).style.fontSize = MyValue;
					if(document.getElementById("style_"+currentValueId).value.search(myRegex2) != -1){
						if(MyValue == ""){
							document.getElementById("style_"+currentValueId).value = document.getElementById("style_"+currentValueId).value.replace(myRegex2, "");
						}else{
							document.getElementById("style_"+currentValueId).value = document.getElementById("style_"+currentValueId).value.replace(myRegex2, "font-size:"+MyValue+";");
						}
					}else{
						document.getElementById("style_"+currentValueId).value = document.getElementById("style_"+currentValueId).value + "font-size:"+MyValue+";";
					}
				}

				function initDiv(){

					var RegexSize = new RegExp(/font-size *:[^;]*(;)?/gi);
					var RegexColor = new RegExp(/([^a-z-])color *:[^;]*(;)?/gi);


					document.getElementById("colorexamplewysijacolor").style.backgroundColor = "#000000";
					document.getElementById("colordivwysijacolor").style.display = "none";
					spaced = document.getElementById("style_"+currentValueId).value.substr(0,1);
					if(spaced != " "){
						stringToQuery = \' \' + document.getElementById("style_"+currentValueId).value;
					}else{
						stringToQuery = document.getElementById("style_"+currentValueId).value;
					}
					NewColor = stringToQuery.match(RegexColor);
					if(NewColor != null){
						NewColor = NewColor[0].match(/:[^;!]*/gi);
						NewColor = NewColor[0].replace(/(:| )/gi,"");
						document.getElementById("colorexamplewysijacolor").style.backgroundColor = NewColor;
					}


					document.getElementById("U").className = "uelement";
					document.getElementById("I").className = "ielement";
					document.getElementById("B").className = "belement";

					if(document.getElementById("style_"+currentValueId).value.search(/font-weight: *bold(;)?/i) != -1){
						document.getElementById("B").className += "selected";
					}
					if(document.getElementById("style_"+currentValueId).value.search(/font-style: *italic(;)?/i) != -1){
						document.getElementById("I").className += "selected";
					}
					if(document.getElementById("style_"+currentValueId).value.search(/text-decoration: *underline(;)?/i) != -1){
						document.getElementById("U").className += "selected";
					}


					NewSize = stringToQuery.match(RegexSize);
					document.getElementById("style_select_wysija").options[0].selected = true;
					if(NewSize != null){
						NewSize = NewSize[0].match(/:[^;]*/gi);
						NewSize = NewSize[0].replace(" ","");
						NewSize = NewSize.substr(1);
						for(var i = 0; i < document.getElementById("style_select_wysija").length; i++){
							if(document.getElementById("style_select_wysija").options[i].value == NewSize){
								document.getElementById("style_select_wysija").options[i].selected = true;
							}
						}
					}
				}';

		acymailing_addScript(true, $script);

		$installedPlugin = acymailing_getPlugin('acymailing', 'emojis');
		if(!empty($installedPlugin)){
			$params = new acyParameter($installedPlugin->params);
			if(acymailing_isPluginEnabled('acymailing', 'emojis') && $params->get('subject', 1) == 1) {
				if(!ACYMAILING_J30){
					acymailing_addScript(false, ACYMAILING_JS.'jquery/jquery-1.9.1.min.js?v='.filemtime(ACYMAILING_ROOT.'media'.DS.'com_acymailing'.DS.'js'.DS.'jquery'.DS.'jquery-1.9.1.min.js'));
					acymailing_addScript(false, ACYMAILING_JS.'jquery/jquery-ui.min.js?v='.filemtime(ACYMAILING_ROOT.'media'.DS.'com_acymailing'.DS.'js'.DS.'jquery'.DS.'jquery-ui.min.js'));
				}
				acymailing_addScript(false, acymailing_rootURI().'plugins/editors/acyeditor/acyeditor/ckeditor/plugins/smiley/emojionearea.js?v='.filemtime(ACYMAILING_ROOT.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'acyeditor'.DS.'ckeditor'.DS.'plugins'.DS.'smiley'.DS.'emojionearea.js'));
				acymailing_addScript(false, acymailing_rootURI().'plugins/editors/acyeditor/acyeditor/ckeditor/plugins/smiley/dialogs/emojimap.js?v='.filemtime(ACYMAILING_ROOT.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'acyeditor'.DS.'ckeditor'.DS.'plugins'.DS.'smiley'.DS.'dialogs'.DS.'emojimap.js'));
				acymailing_addStyle(false, acymailing_rootURI().'plugins/editors/acyeditor/acyeditor/ckeditor/plugins/smiley/emojionearea.css?v='.filemtime(ACYMAILING_ROOT.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'acyeditor'.DS.'ckeditor'.DS.'plugins'.DS.'smiley'.DS.'emojionearea.css'));

				acymailing_addScript(true, '
					jQuery(document).ready(function() {
						jQuery("#subject").emojioneArea({
							pickerPosition: "bottom",
							shortnames: true
						});
					});
				');
			}
		}

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$infos = new stdClass();
		$infos->test_selection = acymailing_getUserVar($paramBase.".test_selection", 'test_selection', '', 'string');
		$infos->test_group = acymailing_getUserVar($paramBase.".test_group", 'test_group', '', 'string');
		$infos->test_emails = acymailing_getUserVar($paramBase.".test_emails", 'test_emails', '', 'string');


		$acyToolbar = acymailing_get('helper.toolbar');
		if(acymailing_isAllowed($config->get('acl_tags_view', 'all'))) $acyToolbar->popup('tag', acymailing_translation('TAGS'), acymailing_completeLink("tag&task=tag&type=news", true), 780, 550);
		$acyToolbar->custom('test', acymailing_translation('SEND_TEST'), 'send', false);
		$acyToolbar->divider();
		$acyToolbar->addButtonOption('apply', acymailing_translation('ACY_APPLY'), 'apply', false);
		$acyToolbar->save();
		$acyToolbar->cancel();
		$acyToolbar->divider();
		$acyToolbar->help('template', 'templatecreation');
		$acyToolbar->setTitle(acymailing_translation('ACY_TEMPLATE'), 'template&task=edit&tempid='.$tempid);
		$acyToolbar->display();


		$this->editor = $editor;
		$testreceiverType = acymailing_get('type.testreceiver');
		$this->testreceiverType = $testreceiverType;
		$this->template = $template;
		$colorBox = acymailing_get('type.color');
		$this->colorBox = $colorBox;
		$this->infos = $infos;

		$tabs = acymailing_get('helper.acytabs');
		$this->tabs = $tabs;
	}

	function theme(){
		$this->selection[] = 'a.*';
		$this->filters[] = 'a.published = 1';

		if(acymailing_level(3)){
			$groups = acymailing_getGroupsByUser(acymailing_currentUserId(), false);
			$condGroup = '';
			foreach($groups as $group){
				$condGroup .= ' OR a.access LIKE (\'%,'.$group.',%\')';
			}
			$this->filters[] = 'a.access = \'all\''.$condGroup;
		}

		$this->button = false;
		acymailing_display(acymailing_translation('CHANGE_TEMPLATE'), 'warning', false);
		$this->listing();

		$js = "function applyTemplate(tempid){
			window.parent.changeTemplate(window.document.getElementById('htmlcontent_'+tempid).innerHTML,
										window.document.getElementById('textcontent_'+tempid).innerHTML,
										window.document.getElementById('subject_'+tempid).innerHTML,
										window.document.getElementById('stylesheet_'+tempid).innerHTML,
										window.document.getElementById('fromname_'+tempid).innerHTML,
										window.document.getElementById('fromemail_'+tempid).innerHTML,
										window.document.getElementById('replyname_'+tempid).innerHTML,
										window.document.getElementById('replyemail_'+tempid).innerHTML,
										tempid);
			acymailing.closeBox(true); }";
		acymailing_addScript(true, $js);
	}

	function upload(){
		if(acymailing_isNoTemplate()){
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->custom('doupload', acymailing_translation('IMPORT'), 'import', false);
			$acyToolbar->divider();
			$acyToolbar->help('template-upload');
			$acyToolbar->setTitle(acymailing_translation('ACY_TEMPLATE'));
			$acyToolbar->topfixed = false;
			$acyToolbar->display();
		}
	}
}
