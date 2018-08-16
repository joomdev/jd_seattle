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


class EmailViewEmail extends acymailingView{

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();


		parent::display($tpl);
	}

	function form(){
		$mailid = acymailing_getCID('mailid');
		if(empty($mailid)) $mailid = acymailing_getVar('string', 'mailid');

		$mailClass = acymailing_get('class.mail');
		$mail = $mailClass->get($mailid);

		if(empty($mail)){
			$config = acymailing_config();

			$mail = new stdClass();
			$mail->created = time();
			$mail->fromname = $config->get('from_name');
			$mail->fromemail = $config->get('from_email');
			$mail->replyname = $config->get('reply_name');
			$mail->replyemail = $config->get('reply_email');
			$mail->subject = '';
			$mail->type = acymailing_getVar('string', 'type');
			$mail->published = 1;
			$mail->visible = 0;
			$mail->html = 1;
			$mail->body = '';
			$mail->altbody = '';
			$mail->tempid = 0;
			$mail->alias = '';
		};

		$values = new stdClass();
		$values->maxupload = (acymailing_bytes(ini_get('upload_max_filesize')) > acymailing_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize');


		$toggleClass = acymailing_get('helper.toggle');

		if(acymailing_isAdmin()){
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->custom('', acymailing_translation('ACY_TEMPLATES'), 'template', false, 'displayTemplates(); return false;');
			$acyToolbar->custom('', acymailing_translation('TAGS'), 'tag', false, 'try{IeCursorFix();}catch(e){}; displayTags(); return false;');
			$acyToolbar->divider();
			$acyToolbar->custom('test', acymailing_translation('SEND_TEST'), 'send', false);
			$acyToolbar->custom('apply', acymailing_translation('ACY_APPLY'), 'apply', false);
			$acyToolbar->setTitle(acymailing_translation('ACY_EDIT'));
			$acyToolbar->topfixed = false;
			$acyToolbar->display();
		}

		$editor = acymailing_get('helper.editor');
		$editor->setTemplate($mail->tempid);
		$editor->name = 'editor_body';
		$editor->content = $mail->body;

		$js = "function updateAcyEditor(htmlvalue){";
		$js .= 'if(htmlvalue == \'0\'){window.document.getElementById("htmlfieldset").style.display = \'none\'}else{window.document.getElementById("htmlfieldset").style.display = \'block\'}';
		$js .= '}';

		$script = '
		var attachmentNb = 1;
		function addFileLoader(){
			if(attachmentNb > 9) return;
			window.document.getElementById("attachmentsdiv"+attachmentNb).style.display = "";
			attachmentNb++;
		}';

		$script .= "function deleteAttachment(i){
			document.getElementById('attachments'+i+'selection').innerHTML = '';
			document.getElementById('attachments'+i+'suppr').style.display = 'none';
			document.getElementById('attachments'+i).value = '';
			return;
		}";

		$script .= '
			document.addEventListener("DOMContentLoaded", function(){
				acymailing.submitbutton = function(pressbutton) {
					if (pressbutton == \'cancel\') {
						acymailing.submitform(pressbutton,document.adminForm);
						return;
					}';

		$url = acymailing_currentURL();
		if(strpos($url, 'send-in-article') !== false){
			$script .= '
						if(pressbutton == \'apply\' || pressbutton == \'test\'){
							var content = '.$editor->getContent().';
							var match = content.match(/{joomlacontent:current/);
							if(match == null){
								alert("'.acymailing_translation('ACY_TAG_ARTICLE').'");
								return false;
							}
						}';
		}

		$script .= 'if(window.document.getElementById("subject").value.length < 2){alert(\''.acymailing_translation('ENTER_SUBJECT', true).'\'); return false;}';
		$script .= $editor->jsCode();
		$script .= 'acymailing.submitform(pressbutton,document.adminForm);
				};
			 }); ';

		$script .= "var zoneToTag = 'editor';
		function insertTag(tag){
			if(zoneToTag == 'editor'){
				try{
					if(window.parent.tinymce){ parentTinymce = window.parent.tinymce; window.parent.tinymce = false; }
					jInsertEditorText(tag,'editor_body');
					if(typeof parentTinymce !== 'undefined'){ window.parent.tinymce = parentTinymce; }
					document.getElementById('iframetag').style.display = 'none';
					displayTags();
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

		$typeMail = 'news';
		if(strpos($mail->alias, 'notification') !== false){
			$typeMail = 'notification';
		}

		$iFrame = "'<iframe src=\'".acymailing_completeLink((acymailing_isAdmin() ? '' : 'front')."tag&task=tag&type=".$typeMail, true)."\' width=\'100%\' height=\'100%\' scrolling=\'auto\'></iframe>'";
		$script .= "var openTag = true;
					function displayTags(){
						var box = document.getElementById('iframetag');
						if(openTag){
							box.innerHTML = ".$iFrame.";
							box.style.display = 'block';
						}else{
							box.style.display = 'none';
						}
						
						if(openTag){
							box.className = 'slide_open';
						}else{
							box.className = box.className.replace('slide_open', '');
						}
						openTag = !openTag;
					}";

		$iFrame = "'<iframe src=\'".acymailing_completeLink((acymailing_isAdmin() ? '' : 'front')."template&task=theme", true)."\' width=\'100%\' height=\'100%\' scrolling=\'auto\'></iframe>'";
		$script .= "var openTemplate = true;
					function displayTemplates(){
						var box = document.getElementById('iframetemplate');
						if(openTemplate){
							box.innerHTML = ".$iFrame.";
							box.style.display = 'block';
						}else{
							box.style.display = 'none';
						}
						
						if(openTemplate){
							box.className = 'slide_open';
						}else{
							box.className = box.className.replace('slide_open', '');
						}
						openTemplate = !openTemplate;
					}";

		$script .= "function changeTemplate(newhtml,newtext,newsubject,stylesheet,fromname,fromemail,replyname,replyemail,tempid){
			if(newhtml.length>2){".$editor->setContent('newhtml')."}
			var vartextarea = document.getElementById('altbody');
			if(newtext.length>2) vartextarea.innerHTML = newtext;
			document.getElementById('tempid').value = tempid;
			
			if(fromname.length>1){document.getElementById('fromname').value = fromname;}
			if(fromemail.length>1){document.getElementById('fromemail').value = fromemail;}
			if(replyname.length>1){document.getElementById('replyname').value = replyname;}
			if(replyemail.length>1){document.getElementById('replyemail').value = replyemail;}
			if(newsubject.length>1){
				var subjectObj = document.getElementById('subject');
				if(subjectObj.tagName.toLowerCase() == 'input'){
					subjectObj.value = newsubject;
				}else{
				    subjectObj.innerHTML = newsubject;
				}
			}
			
			".$editor->setEditorStylesheet('tempid')."
			document.getElementById('iframetemplate').style.display = 'none';
			displayTemplates();
		}";

		$plugin = acymailing_getPlugin('acymailing', 'tagcontent');
		$this->params = new acyParameter($plugin->params);
		$this->acypluginsHelper = acymailing_get('helper.acyplugins');

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

		$picts = array();
		$picts[] = acymailing_selectOption("1", acymailing_translation('JOOMEXT_YES'));
		$pictureHelper = acymailing_get('helper.acypict');
		if($pictureHelper->available()) $picts[] = acymailing_selectOption("resized", acymailing_translation('RESIZED'));
		$picts[] = acymailing_selectOption("0", acymailing_translation('JOOMEXT_NO'));

		if($mail->html == 1){
			$script .= "var zoneEditor = 'editor_body';";
		}else{
			$script .= "var zoneEditor = 'altbody';";
		}

		$script .= '
		
		var zoneToTag = \'altbody\';
		function initTagZone(html){ if(html == 0){ zoneEditor = \'altbody\'; }else{ zoneEditor = \'editor_body\'; }}
		
		var previousSelection = false;
		function insertTagCurrent(){
		var tag = \'{joomlacontent:current|\';
			var display = document.querySelector(\'input[name = "contenttype"]:checked\').value;
			var format = document.getElementById(\'contentformat\').value;
			var displayPict = document.querySelector(\'input[name = "pict"]:checked\').value;
			var clickTitle = document.querySelector(\'input[name = "titlelink"]:checked\').value;
			var author = document.querySelector(\'input[name = "author"]:checked\').value;
			var facebook = document.getElementById(\'facebook\').checked ;
			var linkedin = document.getElementById(\'linkedin\').checked;
			var twitter = document.getElementById(\'twitter\').checked;
			var google = document.getElementById(\'google\').checked;
			
			if(display == \'title\'){
				tag = tag + \' type:\'+display+\'|\';
			}else{
				tag = tag + \' type:\'+display+\'| format:\'+format+\'| pict:\'+displayPict+\'|\';
			}
			
			if(clickTitle == \'link\'){
				tag = tag + \' link|\';
			}
			if(author == \'author\'){
				tag = tag + \' author|\';
			}
			if(facebook || linkedin || twitter || google){
				tag = tag + \' share:\';
				if(facebook) tag = tag + \'facebook,\';
				if(linkedin) tag = tag + \'linkedin,\';
				if(twitter) tag = tag + \'twitter,\';
				if(google) tag = tag + \'google,\';
				tag = tag.slice(0, -1);
				tag = tag + \'|\';
			}
			tag = tag.slice(0, -1);
			tag = tag + \'}\';
			if(zoneEditor == \'editor_body\'){
				try{
					jInsertEditorText(tag,\'editor_body\',previousSelection);
					return true;
				} catch(err){
					alert(\'Your editor does not enable AcyMailing to automatically insert the tag, please copy / paste it manually in your Newsletter\');
					return false;
				}
			} else{
				try{
					simpleInsert(document.getElementById(zoneToTag), tag);
					return true;
				} catch(err){
					alert(\'Error inserting the tag in the \'+ zoneToTag + \'zone.Please copy / paste it manually in your Newsletter.\');
					return false;
				}
			}
		}
		
		function updateTag(){
			var display = document.querySelector(\'input[name = "contenttype"]:checked\').value;
			if(display == \'title\'){
				document.getElementById(\'format\').style.display = \'none\' ;
			}
			else if(display != \'title\'){
				document.getElementById(\'format\').style.display = \'table-row\' ;
			}
		}';

		acymailing_addScript(true, $js.$script);

		$this->picts = $picts;
		$this->titlelink = $titlelink;
		$this->authorname = $authorname;
		$this->contenttype = $contenttype;
		$this->toggleClass = $toggleClass;
		$this->editor = $editor;
		$this->values = $values;
		$this->mail = $mail;
		$tabs = acymailing_get('helper.acytabs');
		$this->tabs = $tabs;
	}

	function listing(){
		$article_id = acymailing_getVar('int', 'articleId');
		if(empty($article_id)) return;

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();

		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$selectedCategory = acymailing_getUserVar($paramBase."filter_category", 'filter_category', 0, 'string');

		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = "a.name LIKE $searchVal OR a.description LIKE $searchVal OR a.listid LIKE $searchVal";
		}
		$filters[] = "a.type = 'list'";
		if(!empty($selectedCategory)) $filters[] = 'a.category = '.acymailing_escapeDB($selectedCategory);

		if(!acymailing_isAdmin()){
			$listClass = acymailing_get('class.list');
			$lists = $listClass->getFrontendLists('listid');

			$filters[] = 'listid IN ('.implode(',', array_keys($lists)).')';
		}

		$query = 'SELECT a.*, d.name as creatorname, d.username, d.email';
		$query .= ' FROM '.acymailing_table('list').' as a';
		$query .= ' LEFT JOIN '.acymailing_table('users', false).' as d on a.userid = d.id';
		$query .= ' WHERE ('.implode(') AND (', $filters).')';
		$query .= ' ORDER BY a.name ASC';

		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);

		$queryCount = 'SELECT COUNT(a.listid) FROM  '.acymailing_table('list').' as a';
		if(!empty($pageInfo->search)) $queryCount .= ' LEFT JOIN '.acymailing_table('users', false).' as d on a.userid = d.id';
		$queryCount .= ' WHERE ('.implode(') AND (', $filters).')';

		$pageInfo->elements->total = acymailing_loadResult($queryCount);
		$pageInfo->elements->page = count($rows);

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		if(acymailing_isAdmin()){
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->custom('sendArticle', acymailing_translation('SEND'), 'send');
			$acyToolbar->setTitle(acymailing_translation('ACY_SELECT_LIST'), 'list');
			$acyToolbar->display();
		}

		$filters = new stdClass();
		$listcategoryType = acymailing_get('type.categoryfield');
		$filters->category = $listcategoryType->getFilter('list', 'filter_category', $selectedCategory, ' onchange="document.adminForm.submit();"');

		acymailing_addStyle(true, '.acyicon-send + span { display: inline-block !important; margin-left: 5px; }');

		$this->filters = $filters;
		$this->rows = $rows;
		$this->pageInfo = $pageInfo;
		$this->pagination = $pagination;
	}
}
