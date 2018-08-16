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

class EditorController extends acymailingController{

	function __construct($config = array()){
		parent::__construct($config);
		acymailing_setNoTemplate();
		
		
		if(!acymailing_isAdmin()){
			acymailing_addStyle(false, ACYMAILING_CSS.'acyicon.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'acyicon.css'));
		}
		$this->registerDefaultTask('browse');
	}

	function browse(){
		$this->_setCss();
		$this->_setJs();
		$this->_displayHTML();
	}

	private function _setCss(){
		if(acymailing_getVar('none', 'inpopup', '') == 'true'){
			$height_acy_media_browser_table = 420;
			$height_acy_media_browser_list = 310;
			$width_acy_media_browser_actions = 393;
			$width_acy_media_browser_hidden_elements = 395;
			$height_acy_media_browser_image_details = 415;
			$width_acy_media_browser_buttons_block = 365;
			$width_acy_media_browser_url_input = 60;
		}else{
			$height_acy_media_browser_table = 540;
			$height_acy_media_browser_list = 450;
			$width_acy_media_browser_actions = 522;
			$width_acy_media_browser_hidden_elements = 522;
			$height_acy_media_browser_image_details = 550;
			$width_acy_media_browser_buttons_block = 492;
			$width_acy_media_browser_url_input = 70;
		}

		$css = "
			#import_from_url, #upload_image {
				display: none;
			}

			#acy_media_browser_hidden_elements, #acy_media_browser_buttons_block, #acy_media_browser_buttons_block {
				transition: all 0.3s ease;
			}

			#acy_media_browser_table{
				height:".$height_acy_media_browser_table."px;
				width:100%;
				margin: 0px;
				border: 1px solid rgb(233, 233, 233);
				box-shadow: 4px 4px 4px -4px rgba(0, 0, 0, 0.1);
			}

			#acy_media_browser_path_dropdown{
				float:left;
				margin-left:15px;
				margin-top:15px;
				width:60%;
			}

			#acy_media_browser_global_create_folder{
				width:28%;
				float:right;
				margin-top:15px;
				margin-right:10px;
			}

			#acy_media_browser_create_folder{
				width:100%;
			}

			#create_folder_btn{
				margin-top:0px;
			}

			#acy_media_browser_area_create_folder{
				position:absolute;
				z-index:10;
				margin-top:5px;
				border:1px solid #e9e9e9;
				height:0px;
				width:150px;
				background-color:#f6f6f6;
			}

			#subFolderName{
				width:80%;
				margin-left:7px;
				margin-top:5px;
			}

			#acy_media_browser_area_create_folder .btn{
				float:right;
				margin-right:5px
			}

			#acy_media_browser_message{
				height:450px;
				overflow:auto;
				margin:0px;
				padding:5px;
				border-bottom: 1px solid rgb(233, 233, 233);
			}

			#acy_media_browser_list{
				height:".$height_acy_media_browser_list."px;
				overflow-x:hidden;
				margin:0px;
				padding:0px;
				border-bottom: 1px solid rgb(233, 233, 233);
			}

			.acy_media_browser_image_size{
				color: #AAAAAA;
			}

			#acy_media_browser_actions{
				text-align:center;
				box-shadow: 0px -4px 4px -4px rgba(0, 0, 0, 0.3);
				width:".$width_acy_media_browser_actions."px;
				overflow:hidden;
				height: 100px;
			}

			#acy_media_browser_containing_block{
				height: 70px;
				width:522px;
			}

			#acy_media_browser_buttons_block{
				padding:22px 15px 0px;
				width: ".$width_acy_media_browser_buttons_block."px;
				float:left;
				display:inline-block;
			}

			#acy_media_browser_hidden_elements{
					width:".$width_acy_media_browser_hidden_elements."px;
			}

			#acy_media_browser_url_input{
				width:".$width_acy_media_browser_url_input."%;
				margin:0px;
			}

			#acy_media_browser_insert_message{
				margin-top:5px;
			}

			#acy_media_browser_image_details_row{
				width:35%;
				vertical-align:top;
				background-color: rgb(246, 246, 246);
				border: 1px solid rgb(233, 233, 233);
				font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;
				font-size: 13px;
				line-height: 18px;
				color: rgb(102, 102, 102);
			}

			#acy_media_browser_image_details{
				position: relative;
				width: 85%;
				overflow-x:hidden;
				height: ".$height_acy_media_browser_image_details."px;
				padding: 15px;
			}

			#acy_media_browser_image_selected_info{
				width:230px;
				float:left;
				margin-bottom:10px;
			}

			#acy_media_browser_image_selected_details label{
				font-weight: bold;
			}

			#acy_media_browser_image_selected_details input {
				margin-bottom: 7px;
				}

			#acy_media_browser_image_selected_details select {
				margin-bottom: 7px;
				}

			.alert{
					padding: 8px 35px 8px 14px;
					margin-bottom: 18px;
					text-shadow: 0px 1px 0px rgba(255, 255, 255, 0.5);
					background-color: rgb(252, 248, 227);
					border: 1px solid rgb(251, 238, 213);
					border-radius: 4px;
			}

			.alert-error{
				background-color: rgb(242, 222, 222);
				border-color: rgb(238, 211, 215);
				color: rgb(185, 74, 72);
			}

			.alert-success {
					background-color: rgb(223, 240, 216);
					border-color: rgb(214, 233, 198);
					color: rgb(70, 136, 71);
			}

			li.acy_media_browser_images {position: relative; height: 135px; width:135px; display:inline-block; margin:14px; margin-top:7px; text-align:center; border: 1px solid #eee;}
			.acy_media_browser_images img{max-height:135px; width:auto; max-width:135px; vertical-align:top;}

			.acy_media_browser_images img.acy_media_browser_delete{height:24px; width:24px; vertical-align:top; position:absolute; right:0px; top:0px; z-index:990; cursor: pointer;}
			#acy_media_browser_list .acy_media_browser_image_size{color: #666; text-shadow:1px 1px 1px #ffffff; font-weight:normal}

			#confirmBoxMM{
				width: 370px;
				background: rgba(255, 255, 255, 0.8);
				border: 1px solid #d6d6d6;
				padding: 5px;
				border-radius: 5px;
				box-shadow: 1px 1px 5px #dddddd;
				-moz-box-shadow: 1px 1px 5px #dddddd;
				-webkit-box-shadow: 1px 1px 5px #dddddd;
				position: absolute;
				left: 234px;
				top: 150px;
				z-index: 999;
			}

			#acy_popup_content{
				background-color: #fff;
				padding: 20px;
				text-align: center;
				color: #706f6f;
			}

			.acy_folder_name{
				color: #5e93c0
			}

		";
		if(!ACYMAILING_J30){
			$css = $css."#acy_media_browser_area_create_folder .btn{
					margin-top:30px;
					margin-right:20px;
				}
				#subFolderName{
					margin-left:13px;
				}
			";
		}
		echo '<style>'.$css.'</style>';
	}

	private function _setJs(){
		$websiteurl = rtrim(acymailing_rootURI(), '/').'/';

		acymailing_addScript(false, $websiteurl.ACYMAILING_MEDIA_FOLDER.'/js/jquery/jquery-1.9.1.min.js?v='.@filemtime(ACYMAILING_ROOT.str_replace('/', DS, ACYMAILING_MEDIA_FOLDER).DS.'js'.DS.'jquery'.DS.'jquery-1.9.1.min.js'));

		$imageZone = acymailing_getVar('array', 'image_zone', array(), '');
		if(empty($imageZone)){
			$getAdditionalTags = "
					var selectedImageWidth = document.getElementById('acy_media_browser_image_width').value;
					var selectedImageHeight = document.getElementById('acy_media_browser_image_height').value;
					var selectedImageAlign = document.getElementById('acy_media_browser_image_align').value;
					var selectedImageBorder = document.getElementById('acy_media_browser_image_border').value;
					var selectedImageMargin = document.getElementById('acy_media_browser_image_margin').value;

					var width = ''; var height =''; var align=''; var border = ''; var margin = '';
					if(selectedImageWidth>0) width =  ' width:' + selectedImageWidth + 'px; ';
					if(selectedImageHeight>0) height = ' height:' +  selectedImageHeight + 'px; ';
					if(selectedImageAlign) align = 'float:' + selectedImageAlign + ';';
					if(selectedImageWidth>0 && selectedImageAlign.trim()=='center') align = 'margin:auto;';
					if(selectedImageBorder) border = ' border:' +  selectedImageBorder + '; ';
					if(selectedImageBorder>0 ) border = ' border: solid ' +  selectedImageBorder + 'px; ';
					if(selectedImageMargin>0) margin = ' margin:' +  selectedImageMargin + 'px; ';
					else if(selectedImageMargin) margin = ' margin:' +  selectedImageMargin + '; ';
					var imgSize = ' height =\"' + selectedImageHeight + '\" width = \"' + selectedImageWidth + '\"';
							";
			$sizeAndAlignTags = " style=\"' + height + width + align + border + margin +'\" ";

			$insertImage = "window.parent.insertImageTag(tag, previousSelection);";
		}else{
			$getAdditionalTags = "var selectedImageRef = document.getElementById('acy_media_browser_image_target').value; ";
			$sizeAndAlignTags = "";
			$insertImage = "window.parent.jInsertEditorText(tag, this.editor);";
		}

		if(acymailing_getVar('none', 'inpopup', '') == 'true'){
			$imgMaxHeight = 150;
			$slideValue = -395;
		}else{
			$imgMaxHeight = 190;
			$slideValue = -522;
		}
		
		$js = "
				var previousSelection = window.parent.getPreviousSelection();

				function checkSelected(imageZone) {

					if(imageZone){
						var editor = window.parent.CKEDITOR.editor;

						o = this._getUriObject(window.self.location.href);
						q = this._getQueryObject(o.query);
						zone = decodeURIComponent(q.e_name);

						var html = window.parent.getSelectedHTML(zone);
						var parsedSelection = jQuery.parseHTML(html);

						if(!parsedSelection)
							return false;

						if(parsedSelection[0].tagName == 'A'){
							var parsedImage = jQuery.parseHTML(parsedSelection[0].innerHTML);
							parsedImage = parsedImage[0];

							if(parsedSelection[0].href)
									document.getElementById('acy_media_browser_image_target').value =  parsedSelection[0].href;
						}else if(parsedSelection[0].tagName == 'IMG'){
							var parsedImage = parsedSelection[0];
						}

						if(!parsedImage) return false;

						var name = parsedImage.src.substr(parsedImage.src.lastIndexOf('/') + 1);
						if(parsedImage.src.substring(0,4)=='http'){
							var imageUrl =  parsedImage.src;
						}else{
							var imageUrl =  '".ACYMAILING_LIVE."' + parsedImage.src;
						}
						var width = parsedImage.width;
						var height = parsedImage.height;
						displayImageFromUrl(imageUrl, 'success', name, width, height);
						if(parsedImage.alt)
							document.getElementById('acy_media_browser_image_title').value =  parsedImage.alt;
					}else{
						var editor =  window.parent.editor;
						var sel = editor.getSelection();
						var ranges = sel.getRanges();
						var el = new window.parent.CKEDITOR.dom.element('div');
						for (var i = 0, len = ranges.length; i < len; ++i) {
								el.append(ranges[i].cloneContents());
						}

						if(el.getFirst() && el.getFirst().getName() == 'a'){
							var selection = el.getFirst().getHtml();
							var selectedImageRef = el.getFirst().getAttribute('href');
						} else{
							var selection = el.getHtml();
						}

						var parsedSelection = jQuery.parseHTML(selection);

						if(!parsedSelection)
							return false;
							
						if(parsedSelection[0].tagName == 'IMG'){
							var name = parsedSelection[0].src.substr(parsedSelection[0].src.lastIndexOf('/') + 1);
							var width = parsedSelection[0].width;
							var height = parsedSelection[0].height;
							if($(selection).attr('src').substring(0,4) == 'http'){
								var imageUrl =  $(selection).attr('src');
							}else{
								var imageUrl =  '".ACYMAILING_LIVE."' + $(selection).attr('src');
							}
							displayImageFromUrl(imageUrl, 'success', name, width, height);

							if(parsedSelection[0].alt)
								document.getElementById('acy_media_browser_image_title').value =  parsedSelection[0].alt;
							if(parsedSelection[0].style.width)
								document.getElementById('acy_media_browser_image_width').value =  parsedSelection[0].style.width.slice(0,-2);
							if(parsedSelection[0].style.height)
								document.getElementById('acy_media_browser_image_height').value =  parsedSelection[0].style.height.slice(0,-2);
							if(parsedSelection[0].style.cssFloat)
								document.getElementById('acy_media_browser_image_align').value =  parsedSelection[0].style.cssFloat;
							if(parsedSelection[0].style.margin)
								document.getElementById('acy_media_browser_image_margin').value =  parsedSelection[0].style.margin;
							if(parsedSelection[0].style.border)
								document.getElementById('acy_media_browser_image_border').value =  parsedSelection[0].style.border;
							if(parsedSelection[0].className)
								document.getElementById('acy_media_browser_image_class').value =  parsedSelection[0].className;
							if(selectedImageRef)
								document.getElementById('acy_media_browser_image_linkhref').value = selectedImageRef;
						}
					}
				}

				function removeAllListener(el) {
					var elClone = el.cloneNode(true);
					el.parentNode.replaceChild(elClone, el);
					return elClone;
				}

				function addResizeDragListener(src) {
					var elements = document.getElementsByClassName('drag-resize');
					for(var i = 0; i < elements.length; i++) {
						var element = elements[i];
						element = removeAllListener(element);
						
						if(navigator.userAgent.indexOf('Firefox') > 0) {
							var currentlyDrag = false;
							element.addEventListener('mousedown', function(event) {
								currentlyDrag = true;
							});
	
							element.addEventListener('mousemove', function(event) {
								if(!currentlyDrag) return;
								var scaleValue = event.offsetX;
								if(scaleValue < 0) return false;
								preloadCanvas(src, scaleValue+50);
							});
							
							document.addEventListener('mouseup', function(event) {
								currentlyDrag = false;
							});
						}else{
							element.addEventListener('drag', function(event) {
								var scaleValue = event.offsetX;
								if(scaleValue < 0) return false;
								preloadCanvas(src, scaleValue);
							});
	
							element.addEventListener('dragstart', function(event) {
								if(typeof event.dataTransfer.setDragImage === 'function'){
									var dragIcon = document.createElement('img');
									event.dataTransfer.setDragImage(dragIcon, 0, 0);
								}
							});
						}
					}
				}

				function addCropDragListener(src) {
					var elements = document.getElementsByClassName('drag-resize');
					for(var i = 0; i < elements.length; i++) {
						var element = elements[i];
						element = removeAllListener(element);

						if(navigator.userAgent.indexOf('Firefox') > 0) {
							var currentlyCrop = false;
							element.addEventListener('mousedown', function(event) {
								currentlyCrop = true;
								var coords = {x: event.offsetX, y: event.offsetY, screenX: event.screenX, screenY: event.screenY};
								this.setAttribute('initial-click', JSON.stringify(coords));
							});
	
							element.addEventListener('mousemove', function(event) {
								if(!currentlyCrop) return;
								var coords = JSON.parse(this.getAttribute('initial-click'));
								var width = (event.screenX - coords.screenX);
								var height = (event.screenY - coords.screenY);
								drawRectangle(src, coords.x, coords.y, width, height);
							});
							
							document.addEventListener('mouseup', function(event) {
								if(!currentlyCrop) return;
								currentlyCrop = false;
								
								var coords = JSON.parse(element.getAttribute('initial-click'));
								element.removeAttribute('initial-click');
								var width = (event.screenX - coords.screenX);
								var height = (event.screenY - coords.screenY);
								if(width < 0) {
									width = Math.abs(width);
									coords.x = coords.x - width;
								}
								if (height < 0) {
									height = Math.abs(height);
									coords.y = coords.y - height;
								}
	
								cropImage(src, coords.x, coords.y, width, height)
							});
						}else{
							element.addEventListener('dragend', function(event) {
								var coords = JSON.parse(this.getAttribute('initial-click'));
								this.removeAttribute('initial-click');
								var width = (event.screenX - coords.screenX);
								var height = (event.screenY - coords.screenY);
								if(width < 0) {
									width = Math.abs(width);
									coords.x = coords.x - width;
								}
								if (height < 0) {
									height = Math.abs(height);
									coords.y = coords.y - height;
								}
	
								cropImage(src, coords.x, coords.y, width, height)
							});
	
							element.addEventListener('dragstart', function(event) {
								var coords = {x: event.offsetX, y: event.offsetY, screenX: event.screenX, screenY: event.screenY};
								this.setAttribute('initial-click', JSON.stringify(coords));
								
								if(typeof event.dataTransfer.setDragImage === 'function'){
									var dragIcon = document.createElement('img');
									event.dataTransfer.setDragImage(dragIcon, 0, 0);
								}
							});
	
							element.addEventListener('drag', function(event) {
								var coords = JSON.parse(this.getAttribute('initial-click'));
								var width = (event.screenX - coords.screenX);
								var height = (event.screenY - coords.screenY);
								drawRectangle(src, coords.x, coords.y, width, height);
							});
						}
					}
				}

				function roundedCorner() {
					var selectedImage = document.getElementById('acy_media_browser_selected_image');
					if(typeof selectedImage == 'undefined') return false;

					var canvas = document.getElementById('edition-canvas');
					var ctx = canvas.getContext('2d');

					ctx.clearRect(0, 0, canvas.width, canvas.height);

					var image = new Image();
					image.src = selectedImage.src;

					canvas.width = image.width;
					canvas.height = image.height;

					image.onload = function(event) {
						var radius = document.getElementById('radius-image').value;
						roundedRectangle(0, 0, this.width, this.height, radius, ctx);
						ctx.clip();
						ctx.drawImage(this, 0, 0, this.width, this.height);
					}
				}

				function roundedRectangle(x, y, width, height, radius, ctx) {
				    ctx.beginPath();
				    ctx.moveTo(x + radius, y);
				    ctx.lineTo(x + width - radius, y);
				    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
				    ctx.lineTo(x + width, y + height - radius);
				    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
				    ctx.lineTo(x + radius, y + height);
				    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
				    ctx.lineTo(x, y + radius);
				    ctx.quadraticCurveTo(x, y, x + radius, y);
				    ctx.closePath();
				}

				function cancelModification() {
					var selectedImage = document.getElementById('acy_media_browser_selected_image');
					var imageWidth = document.getElementById('acy_media_browser_image_width').value;
						
					if(typeof selectedImage == 'undefined') return false;
					preloadCanvas(selectedImage.src, imageWidth);
				}

				function changeToCrop() {
					var selectedImage = document.getElementById('acy_media_browser_selected_image');
					var imageWidth = document.getElementById('acy_media_browser_image_width').value;

					if(typeof selectedImage == 'undefined') return false;

					addCropDragListener(selectedImage.src);
					preloadCanvas(selectedImage.src, imageWidth);
				}

				function changeToScale() {
					var selectedImage = document.getElementById('acy_media_browser_selected_image');
					var imageWidth = document.getElementById('acy_media_browser_image_width').value;

					if(typeof selectedImage == 'undefined') return false;

					addResizeDragListener(selectedImage.src);
					preloadCanvas(selectedImage.src, imageWidth);
				}

				function validateImageModification() {
					var canvas = document.getElementById('edition-canvas');
					var dataURL = canvas.toDataURL('image/png');
					document.getElementById('imagedata').value = dataURL;
					
					var form = document.getElementById('form-edition');
					var queryString = form.action;
					var dataString = form.toQueryString();
					
					var xhr = new XMLHttpRequest();
					xhr.open('POST', queryString);
					xhr.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");
					xhr.onload = function(){
						closePanel();
						window.location.href = window.location.href;
					};
					xhr.send(dataString);
					
					return false;
				}

				function closePanel() {
					document.getElementById('image-edition').classList.add('hidden-edition');
				}

				function drawRectangle(src, sx, sy, sw, sh) {
					var canvas = document.createElement('canvas');
					canvas.id = 'edition-canvas';
					var machin = document.getElementById('edition-canvas');
					var parent = machin.parentElement;
					parent.removeChild(machin);
					parent.appendChild(canvas);
					
					canvas = document.getElementById('edition-canvas');
					
					
					var ctx = canvas.getContext('2d');
					var image = new Image();
					image.src = src;

					canvas.width = image.width;
					canvas.height = image.height;

					ctx.drawImage(image, 0, 0, image.width, image.height);
					ctx.rect(sx, sy, sw, sh);
					ctx.strokeStyle='red';
					ctx.stroke();
				}

				function cropImage(src, sx, sy, sw, sh) {
					var canvas = document.getElementById('edition-canvas');
					var ctx = canvas.getContext('2d');
					var image = new Image();
					image.src = src;

					ctx.clearRect(0, 0, canvas.width, canvas.height);

					canvas.width = sw;
					canvas.height = sh;

					ctx.drawImage(image, sx, sy, sw, sh, 0, 0, sw, sh);
				}

				function preloadCanvas(src, width) {
					var canvas = document.getElementById('edition-canvas');
					var ctx = canvas.getContext('2d');
					var image = new Image();
					image.src = src;

					ctx.clearRect(0, 0, canvas.width, canvas.height);

					var ratio = image.width / image.height;

					canvas.width = width;
					canvas.height = (width / ratio);

					ctx.drawImage(image, 0, 0, width, (width / ratio));
				}

				function displayImageEdition() {
					var selectedImage = document.getElementById('acy_media_browser_selected_image');
					var imageWidth = document.getElementById('acy_media_browser_image_width').value;

					if(selectedImage == null) return false;
					addResizeDragListener(selectedImage.src);
					preloadCanvas(selectedImage.src, imageWidth);
					document.getElementById('pathtosave').value = document.getElementById('currentPath').value;

					document.getElementById('image-edition').classList.toggle('hidden-edition');
				}

				function displayImageFromUrl(url, result, name, width, height, fromUrl){
					if(result=='success'){
							var infos = '<div style=\"width:100%; display:block: height:1px; float:left; margin-top:10px;\"></div>';
							document.getElementById('acy_media_browser_image_selected').innerHTML='<img id=\"acy_media_browser_selected_image\" src=\"' + url + '\"  style=\"border: 1px solid rgb(233, 233, 233); float:left; margin-right:15px; max-width: 230px; max-height:".$imgMaxHeight."px;\"></img>'+infos;
							document.getElementById('acy_media_browser_image_selected').style.display=\"\";
							if(!name){ var name = url.substr(url.lastIndexOf('/') + 1); }
							if(width){
								document.getElementById('acy_media_browser_image_selected_info').innerHTML='<div><span id=\"acy_media_browser_image_selected_name\" style=\"font-weight:bold;\"> '+name+'</span><br />'+width+'x'+height+'<br />';
								var widthField = document.getElementById('acy_media_browser_image_width');
								var heightField = document.getElementById('acy_media_browser_image_height');
								if(widthField) widthField.value = width;
								if(heightField) heightField.value = height;
							}
							document.getElementById('acy_media_browser_image_selected_info').style.display=\"\";
							if(fromUrl){
								document.getElementById('acy_media_browser_insert_message').innerHTML='<span style=\"color:green;\">".str_replace("'", "\'", acymailing_translation('IMAGE_FOUND'))."</span>';
							}
					}else{
							document.getElementById('acy_media_browser_image_selected').innerHTML=\"\";
							document.getElementById('acy_media_browser_image_selected').style.display=\"none\";
							document.getElementById('acy_media_browser_image_selected_info').innerHTML=\"\";
							if(fromUrl){
								if(result='error'){
									document.getElementById('acy_media_browser_insert_message').innerHTML='<span style=\"color:red;\">".str_replace("'", "\'", acymailing_translation('IMAGE_NOT_FOUND'))."</span>';
								}else if(result='timeout'){
									document.getElementById('acy_media_browser_insert_message').innerHTML='<span style=\"color:red;\">".str_replace("'", "\'", acymailing_translation('IMAGE_TIMEOUT'))."</span>';
								}
							}
					}
				}


				function calculateSize(newHeight, newWidth){
					if((newHeight == '' && newWidth == '') || (newHeight == '' && newWidth == 0) || (newHeight == 0 && newWidth == '')) return;
					var img = document.getElementById('acy_media_browser_selected_image');
					if(!img) return;

					if(newHeight == 0)
						document.getElementById('acy_media_browser_image_height').value =  parseInt(img.naturalHeight * (newWidth / img.naturalWidth));

					if(newWidth == 0)
						document.getElementById('acy_media_browser_image_width').value =  parseInt(img.naturalWidth * (newHeight / img.naturalHeight));
				}


				function testImage(url, callback, timeout) {
					timeout = timeout || 5000;
						var timedOut = false, timer;
						var img = new Image();
						img.onerror = img.onabort = function() {
								if (!timedOut) {
										clearTimeout(timer);
										callback(url, \"error\", '', '', '',true);
								}
						};
						img.onload = function() {
								if (!timedOut) {
										clearTimeout(timer);
										callback(url, \"success\",'','', '',true);
								}
						};
						img.src = url;
						timer = setTimeout(function() {
								timedOut = true;
								callback(url, \"timeout\", '', '', '', true);
						}, timeout);
				}

				function displayAppropriateField(id){
					if(id==\"import_from_url_btn\"){
							document.getElementById('upload_image').style.display=\"none\";
							document.getElementById('import_from_url').style.display=\"block\";

							jQuery('#acy_media_browser_buttons_block').css('width', '0');
							jQuery('#acy_media_browser_buttons_block').css('opacity', '0');
							jQuery('#acy_media_browser_hidden_elements').css('width', '522px');
							jQuery('#acy_media_browser_hidden_elements').css('opacity', '1');

					}else if(id==\"upload_image_btn\"){
							document.getElementById('upload_image').style.display=\"block\";
							document.getElementById('import_from_url').style.display=\"none\";

							jQuery('#acy_media_browser_buttons_block').css('width', '0');
							jQuery('#acy_media_browser_buttons_block').css('opacity', '0');
							jQuery('#acy_media_browser_hidden_elements').css('width', '522px');
							jQuery('#acy_media_browser_hidden_elements').css('opacity', '1');

					}else if(id == \"create_folder_btn\"){
						if(document.getElementById('acy_media_browser_area_create_folder').style.display == \"none\"){
							document.getElementById('acy_media_browser_area_create_folder').style.display = \"\";
							jQuery('#acy_media_browser_area_create_folder').stop().animate({height: '85px'},400);
						}else{
							document.getElementById('acy_media_browser_area_create_folder').style.display = \"none\";
							jQuery('#acy_media_browser_area_create_folder').stop().animate({height: '0px'},400);
						}
					}else{
							jQuery('#acy_media_browser_hidden_elements').css('width', '0');
							jQuery('#acy_media_browser_hidden_elements').css('opacity', '0');
							jQuery('#acy_media_browser_buttons_block').css('width', '522px');
							jQuery('#acy_media_browser_buttons_block').css('opacity', '1');

					}
				}

				function toggleImageInfo(id, action){
					if(action==\"display\"){
							document.getElementById('acy_media_browser_image_info_'+id+'').style.display = \"\";
					}else{
							document.getElementById('acy_media_browser_image_info_'+id+'').style.display = \"none\";
					}
				}

				function _getQueryObject(q) {
					var vars = q.split(/[&;]/);
					var rs = {};
					if (vars.length){
						for(var i = 0 ; i<vars.length ; i++){
							var val = vars[i];
							var keys = val.split('=');
							if (keys.length && keys.length == 2) rs[encodeURIComponent(keys[0])] = encodeURIComponent(keys[1]);
						}
					}
					return rs;
				}

				function _getUriObject(u){
					var bits = u.match(/^(?:([^:\/?#.]+):)?(?:\/\/)?(([^:\/?#]*)(?::(\d*))?)((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[\?#]|$)))*\/?)?([^?#\/]*))?(?:\?([^#]*))?(?:#(.*))?/);
					
					return (bits)
						? {uri: bits[0], scheme: bits[1], authority: bits[2], domain: bits[3], port: bits[4], path: bits[5], directory: bits[6], file: bits[7], query: bits[8], fragment: bits[9]}
						: null;
				}

				function validateImage(){
					var urlInput = document.getElementById('acy_media_browser_url_input').value;
					var urlImageName = urlInput.substr(urlInput.lastIndexOf('/') + 1);
					var selectedImageName = '';
					if(document.getElementById('acy_media_browser_image_selected_name'))
					var selectedImageName = document.getElementById('acy_media_browser_image_selected_name').innerHTML;

					var selectedImageAlt = document.getElementById('acy_media_browser_image_title').value;
					var selectedImageRef = '';
					if(document.getElementById('acy_media_browser_image_linkhref'))
						var selectedImageRef = document.getElementById('acy_media_browser_image_linkhref').value;
					var selectedImageUrl = document.getElementById('acy_media_browser_selected_image').src;
					var imgSize = '';
					var selectedImageClass = '';
					if(document.getElementById('acy_media_browser_image_class'))
						var selectedImageClass = document.getElementById('acy_media_browser_image_class').value;

					".$getAdditionalTags."

					o = this._getUriObject(window.self.location.href);
					q = this._getQueryObject(o.query);
					this.editor = decodeURIComponent(q.e_name);

					var dropdown = document.getElementById('acy_media_browser_files_path');
					var path = dropdown.value;
					var base = ' ".ACYMAILING_LIVE." ';

					if(urlInput!='http://' && selectedImageName.trim()==urlImageName.trim()){
							var tag = '<img ' + imgSize + ' src=\"' + urlInput + '\" alt=\"' + selectedImageAlt + '\" ".$sizeAndAlignTags." class=\"' + selectedImageClass + '\" />';
					}else{
							var tag = '<img ' + imgSize + ' src=\"' + selectedImageUrl + '\" alt=\"' + selectedImageAlt + '\" ".$sizeAndAlignTags." class=\"' + selectedImageClass + '\" />';
					}

					if(selectedImageRef){
							tag = '<a href=\"' + selectedImageRef + '\">' + tag + '</a>';
					}

					".$insertImage."
					return false;
				}

				function changeFolder(folderName){
					var url = window.location.href;
					if (url.indexOf('?') > -1){
							var lastParam = url.substring(url.lastIndexOf('&') + 1);
							if(url.indexOf('pictName') > -1){
								var temp = url.split('&');
								for(var i=0;i<temp.length;i++){
									if(temp[i].indexOf('pictName') > -1){
										temp.splice(i, 1);
										i--;
									}
								}
								url = temp.join('&');
								lastParam = url.substring(url.lastIndexOf('&') + 1);
							}
							if(lastParam == 'task=createFolder')url = url.replace(lastParam,'task=browse&e_name=ACY_NAME_AREA');
							lastParam = lastParam.split('=');
							if(lastParam=='selected_folder')
								url = url.replace(lastParam, 'selected_folder='+folderName);
							else
								url += '&selected_folder='+folderName;
					}else{
							 url += '?selected_folder='+folderName;
					}
					window.location.href = url;
				}

				function confirmBox(type, pictName, originalName){
					if(type == 'delete'){
						document.getElementById('confirmTxtMM').innerHTML = '".acymailing_translation('ACY_VALIDDELETEITEMS')."<br /><span class=\"acy_folder_name\">('+pictName+')</span><br />';
						document.getElementById('textBtnAction').innerHTML = '".acymailing_translation('ACY_DELETE')."';
						document.getElementById('confirmOkMM').className = 'acymailing_button acymailing_button_delete';
						document.getElementById('iconAction').className = 'acyicon-delete';
					}else{
						document.getElementById('confirmTxtMM').innerHTML =  '".acymailing_translation('ACY_REPLACE_FILE_TEXT')."<br />';
						document.getElementById('textBtnAction').innerHTML = '".acymailing_translation('ACY_REPLACE_FILE')."';
						document.getElementById('confirmOkMM').className = 'acymailing_button';
						document.getElementById('iconAction').className = 'acyicon-edit';
					}

					var divDelete = document.getElementById('confirmOkMM');
					divDelete.onclick = function(){
						if(type == 'delete'){
							reloadAndAction(type, pictName);
						}else{
							reloadAndAction(type, pictName, originalName);
						}
					}
					var divConfirm = document.getElementById('confirmBoxMM');
					divConfirm.style.display = 'inline';
				}

				function reloadAndAction(type, pictName, originalName){
					var urlPict = window.location.href;
					var lastParam = urlPict.substring(urlPict.lastIndexOf('&') + 1);
					if(lastParam.indexOf('pictName=') > -1){
						urlPict = urlPict.substring(0, urlPict.indexOf('pictName=')-1);
					}
					if(lastParam.indexOf('pictRename=') > -1){
						urlPict = urlPict.substring(0, urlPict.indexOf('pictRename=')-1);
						lastParam = urlPict.substring(urlPict.lastIndexOf('&') + 1);
						if(lastParam.indexOf('originalName=') > -1){
							urlPict = urlPict.substring(0, urlPict.indexOf('originalName=')-1);
						}
					}

					if(urlPict.indexOf('?') > -1){
						if(type == 'delete'){
							window.location.href = urlPict + '&pictName=' + pictName;
						}else{
							window.location.href = urlPict + '&originalName=' + originalName + '&pictRename=' + pictName;
						}
					} else{
						if(type == 'delete'){
							window.location.href = urlPict + '?pictName=' + pictName;
						}else{
							window.location.href = urlPict + '?originalName=' + originalName + '&pictRename=' + pictName;
						}
					}
				}
				function changeDisplay(event){
					if(document.getElementById('displayPict').style.display == ''){
						display('list');
					}else{
						display('icons');
					}
				}
				function display(type){
					if(type == 'list'){
						document.getElementById('displayPict').style.display = 'none';
						document.getElementById('displayLine').style.display = '';
						document.getElementById('btn_change_display').title = '".acymailing_translation('ACY_DISPLAY_ICON')."';
						document.getElementById('iconTypeDisplay').className = 'acyicon-image_view';
					}else{
						document.getElementById('displayPict').style.display = '';
						document.getElementById('displayLine').style.display = 'none';
						document.getElementById('btn_change_display').title = '".acymailing_translation('ACY_DISPLAY_NOICON')."';
						document.getElementById('iconTypeDisplay').className = 'acyicon-list_view';
					}
				}
			";

		acymailing_addScript(true, $js);
	}

	private function _displayHTML(){

		$mediaFolders = acymailing_getFilesFolder('media', true);

		$receivedFolder = acymailing_getUserVar(ACYMAILING_COMPONENT.".acyeditor.selected_folder", 'selected_folder', '', 'string');
		$defaultFolder = reset($mediaFolders);

		if(!empty($receivedFolder)){
			$allowed = false;
			foreach($mediaFolders as $oneMedia){
				if(preg_match('#^'.preg_quote(rtrim($oneMedia, '/')).'[a-z_0-9\-/]*$#i', $receivedFolder)){
					$allowed = true;
					break;
				}
			}
			if($allowed){
				$defaultFolder = $receivedFolder;
			}else{
				acymailing_display('You are not allowed to access this folder', 'error');
			}
		}

		$uploadPath = acymailing_cleanPath(ACYMAILING_ROOT.trim(str_replace('/', DS, trim($defaultFolder)), DS));

		$uploadedImage = acymailing_getVar('array', 'uploadedImage', array(), 'files');
		if(!empty($uploadedImage)){
			if(!empty($uploadedImage['name'])){
				$this->imageName = acymailing_importFile($uploadedImage, $uploadPath, true);
				if(!empty($this->imageName)){
					$uploadMessage = 'success';
				}else $uploadMessage = 'error';
			}else{
				$uploadMessage = 'error';
				$this->message = acymailing_translation('BROWSE_FILE');
			}
		}

		if(empty($uploadedImage)){
			$pictToDelete = acymailing_getVar('string', 'pictName', '');
			$originalName = acymailing_getVar('string', 'originalName', '');
			$pictToRename = acymailing_getVar('string', 'pictRename', '');
			if(!empty($originalName) && !empty($pictToRename)){
				$pictToDelete = $originalName;
			}
			if(!empty($pictToDelete) && file_exists($uploadPath.DS.$pictToDelete)){
				$checkPictNews = acymailing_loadResultArray('SELECT mailid FROM #__acymailing_mail WHERE body LIKE \'%src="'.ACYMAILING_LIVE.$defaultFolder.'/'.$pictToDelete.'"%\'');
				$checkPictTemplate = acymailing_loadResultArray('SELECT tempid FROM #__acymailing_template WHERE body LIKE \'%src="'.ACYMAILING_LIVE.$defaultFolder.'/'.$pictToDelete.'"%\'');

				if(!empty($checkPictNews) || !empty($checkPictTemplate)){
					foreach($checkPictNews as $k => $oneNews){
						$checkPictNews[$k] = '<a href="" onclick="window.parent.document.location.href=\''.acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'newsletter&task=edit&mailid='.$oneNews).'\'">'.$oneNews.'</a>';
					}
					if(acymailing_isAdmin()){
						foreach($checkPictTemplate as $k => $oneTmpl){
							$checkPictTemplate[$k] = '<a href="" onclick="window.parent.document.location.href=\''.acymailing_completeLink('template&task=edit&tempid='.$oneTmpl).'\'">'.$oneTmpl.'</a>';
						}
					}
					acymailing_display(acymailing_translation_sprintf('ACY_CANT_DELETE', (!empty($checkPictNews) ? implode($checkPictNews, ', ') : '-'), (!empty($checkPictTemplate) ? implode($checkPictTemplate, ', ') : '-')), 'error');
				}else{
					if(acymailing_deleteFile($uploadPath.DS.$pictToDelete)){
						acymailing_display(acymailing_translation('ACY_DELETED_PICT_SUCCESS'), 'success');
					}else{
						acymailing_display(acymailing_translation('ACY_DELETED_PICT_ERROR'), 'error');
					}
				}
			}
			if(!empty($originalName) && !empty($pictToRename)){
				if(acymailing_moveFile($uploadPath.DS.$pictToRename, $uploadPath.DS.$originalName)){
					acymailing_display(acymailing_translation('ACY_REPLACED_PICT_SUCCESS'), 'success');
				}else{
					acymailing_display(acymailing_translation('ACY_REPLACED_PICT_ERROR'), 'error');
				}
			}
		}
		?>

		<div id="acy_media_browser">
			<!-- <br style="font-size:1px"/> -->
			<table id="acy_media_browser_table" style="height:420px;">
				<tr>
					<td style="width:65%; vertical-align:top;">
						<?php

						$folders = acymailing_generateArborescence($mediaFolders);
						$filetreeType = acymailing_get('type.filetree');

						echo '<div style="display:inline-block;width:100%;">';
						echo '<form method="post" action="'.acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'editor&task=createFolder').'" style="margin: 0;">';
						echo '<div id="acy_media_browser_path_dropdown" >';
						$filetreeType->display($folders, $defaultFolder, 'acy_media_browser_files_path', 'changeFolder(path)');
						echo '</div>';

						echo '<div id="acy_media_browser_global_create_folder" >';

						echo '<div id="acy_media_browser_create_folder" >';
						echo '<button id="create_folder_btn" class="btn" onclick="displayAppropriateField(this.id)" type="button" style="width:100%; min-height: 24px;" >'.acymailing_translation('CREATE_FOLDER').'</button>';
						echo '</div>';

						echo '<div id="acy_media_browser_area_create_folder" style=\'display:none;\'>';
						echo '<input id="subFolderName" name="subFolderName" type="text" placeholder="'.acymailing_translation('FOLDER_NAME').'" name="text" required="required" />';
						echo '<input type="submit" class="acymailing_button" style="position: absolute;bottom: 9px;right: 9px;" value="'.acymailing_translation('ACY_APPLY').'" />';
						echo '</div>';

						echo '</div>';
						echo acymailing_formToken();
						echo '</form>';

						echo '<div style="margin-top: 15px; display: inline-block;"><button style="float: right;" class="btn" onclick="changeDisplay(event);" id="btn_change_display" title="'.acymailing_translation('ACY_DISPLAY_NOICON').'"><i id="iconTypeDisplay" class="acyicon-list_view"></i></button></div>';

						echo '</div>';


						acymailing_createDir($uploadPath);
						
						$files = acymailing_getFiles($uploadPath);

						echo '<div id="displayPict"><ul id="acy_media_browser_list">';

						if(!empty($uploadMessage) && !empty($this->message)){
							if($uploadMessage == 'success'){
								acymailing_display($this->message);
							}elseif($uploadMessage == 'error'){
								acymailing_display($this->message, 'error');
							}
						}

						$images = array();
						$imagesFound = false;

						$lineDisplay = '<table class="acymailing_smalltable" style="margin: 0;">';
						foreach($files as $k => $file){
							if(strrpos($file, '.') === false) continue;

							$ext = strtolower(substr($file, strrpos($file, '.') + 1));
							$extensions = array('jpg', 'jpeg', 'png', 'gif');
							if(!in_array($ext, $extensions)) continue;

							$imagesFound = true;
							$images[] = $file;
							$imageSize = getimagesize($uploadPath.DS.$file);
							?>
							<li class="acy_media_browser_images" id="acy_media_browser_images_<?php echo $k; ?>" onmouseover="toggleImageInfo(<?php echo $k; ?>, 'display')" onmouseout="toggleImageInfo(<?php echo $k; ?>, 'hide')">
								<img class="acy_media_browser_image" id="acy_media_browser_image_<?php echo $k; ?>" src="<?php echo ACYMAILING_LIVE.$defaultFolder.'/'.$file.'?v='.@filemtime(ACYMAILING_ROOT.$defaultFolder.'/'.$file); ?>"/>
								<a href="#" onclick="displayImageFromUrl('<?php echo ACYMAILING_LIVE.$defaultFolder.'/'.$file; ?>', 'success', '<?php echo $file; ?>', <?php echo empty($imageSize[0]) ? "null,null" : "'".$imageSize[0]."', '".$imageSize[1]."'"; ?>); return false;">
									<div id="acy_media_browser_image_info_<?php echo $k; ?>"
										 style="box-shadow: 1px 1px 2px 1px rgba(0, 0, 0, 0.2); text-shadow:1px 1px 1px #ffffff; border:2px solid #fff; padding-top:40px; text-align:center; vertical-align:middle; color:#333; font-weight:bold; position:absolute; top:0px; left:0px; bottom:0px; right:0px; display:none; background-color: rgba(255,255,255,0.8);">
										<img class="acy_media_browser_delete" id="acy_media_browser_delete_<?php echo $k; ?>" src="<?php echo ACYMAILING_LIVE.ACYMAILING_MEDIA_FOLDER.DS.'images'.DS.'editor'.DS.'delete.png'; ?>" onclick="confirmBox('delete', '<?php echo $file; ?>')"/>
										<?php echo $file; ?><br/>
										<span class="acy_media_browser_image_size"><?php echo empty($imageSize[0]) ? 0 : $imageSize[0].'x'.$imageSize[1]; ?> - <?php echo round((filesize($uploadPath.DS.$file) * 0.0009765625), 2).' ko'; ?><br/></span>
									</div>
								</a>
							</li>
							<?php
							$lineDisplay .= '<tr>';
							$lineDisplay .= '<td width="30" style="padding-left: 10px;"><a href="#" onclick="displayImageFromUrl(\''.ACYMAILING_LIVE.$defaultFolder.'/'.$file.'\', \'success\', \''.$file.'\', '.(empty($imageSize[0]) ? "null,null" : $imageSize[0].",".$imageSize[1]).'); return false;"><img src="'.ACYMAILING_LIVE.$defaultFolder.'/'.$file.'?v='.@filemtime(ACYMAILING_ROOT.$defaultFolder.'/'.$file).'" style="max-width: 24px" /></a></td>';
							$lineDisplay .= '<td><a href="#" onclick="displayImageFromUrl(\''.ACYMAILING_LIVE.$defaultFolder.'/'.$file.'\', \'success\', \''.$file.'\', '.(empty($imageSize[0]) ? "null,null" : $imageSize[0].",".$imageSize[1]).'); return false;">'.$file.'</a></td>';
							$lineDisplay .= '<td><img class="acy_attachment_delete" id="acy_media_browser_delete_'.$k.'" src="'.ACYMAILING_LIVE.'media'.DS.ACYMAILING_COMPONENT.DS.'images'.DS.'editor'.DS.'delete.png" onclick="confirmBox(\'delete\', \''.$file.'\')"/></td>';


							$lineDisplay .= '</tr>';
						}
						$lineDisplay .= '</table>';
						if(!$imagesFound){
							acymailing_display(acymailing_translation('NO_FILE_FOUND'), 'warning');
						}
						echo '</ul></div>';
						?>
						<div id="displayLine" style="display: none; text-align: left; height: 450px; overflow-x: hidden;">
							<?php
							if(!$imagesFound){
								acymailing_display(acymailing_translation('NO_FILE_FOUND'), 'warning');
							}else{
								echo $lineDisplay;
							} ?>
						</div>
						<!-- Here we give the possibility to import a file or specify and url -->
						<div id="acy_media_browser_actions">
							<div id="acy_media_browser_containing_block">
								<div id="acy_media_browser_buttons_block">
									<button type="button" class="acymailing_button_grey" id="button_editimage" onclick="displayImageEdition();"><?php echo acymailing_translation('IMAGE_EDIT') ?></button>
									<button type="button" class="acymailing_button_grey" id="upload_image_btn" onclick="displayAppropriateField(this.id)"> <?php echo acymailing_translation('UPLOAD_NEW_IMAGE'); ?></button>
									<?php echo acymailing_translation('ACY_OR'); ?>
									<button type="button" class="acymailing_button_grey" id="import_from_url_btn" onclick="displayAppropriateField(this.id)"> <?php echo acymailing_translation('INSERT_IMAGE_FROM_URL'); ?> </button>
								</div>
								<div id="acy_media_browser_hidden_elements">
									<div id="upload_image" style="position: relative; padding-top:5px;	display:none; text-align: center;">
										<form method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" style="margin:0px; margin-top:3px;">
											<input type="file" style="width:auto;" name="uploadedImage"/><br/>
											<input type="hidden" name="task" value="browse"/>
											<input type="hidden" name="selected_folder" value="<?php echo htmlspecialchars($defaultFolder, ENT_COMPAT, 'UTF-8'); ?>"/>
											<?php echo acymailing_formToken(); ?>
										</form>
										<button class="acymailing_button" type="button" onclick="acymailing.submitbutton();"> <?php echo acymailing_translation('IMPORT'); ?> </button>
										<span style="position:absolute; top:5px; left:5px;" id="acy_back_from_upload" onclick="displayAppropriateField(this.id)"><a href="javascript:void(0);">&#8592 <?php echo acymailing_translation('MEDIA_BACK'); ?></a></span>
									</div>
									<div id="import_from_url" style="padding-top:9px; position:relative; ">
										<input type="text" id="acy_media_browser_url_input" class="inputbox" oninput="testImage(this.value, displayImageFromUrl)" value="http://"/>
										<div id="acy_media_browser_insert_message"></div>
										<span style="position:absolute; top:5px; left:5px;" id="acy_back_from_url" onclick="displayAppropriateField(this.id)"><a href="javascript:void(0);">&#8592 <?php echo acymailing_translation('MEDIA_BACK'); ?></a></span>
									</div>
								</div>
							</div>
						</div>
					</td>
					<!-- IMAGE INFORMATION -->
					<td id="acy_media_browser_image_details_row">
						<div id="acy_media_browser_image_details">
							<div id="acy_media_browser_image_selected" style=" max-width:230px; max-height:190px; display:none;	margin:auto; margin-bottom:10px;"></div>
							<div id="acy_media_browser_image_selected_info" style=""></div>
							<div id="acy_media_browser_image_selected_details">
								<label for="acy_media_browser_image_title" style="float:left;"><?php echo acymailing_translation('ACY_TITLE'); ?></label>
								<input type="text" id="acy_media_browser_image_title" class="inputbox" style="width:100%" value=""/>
								<?php $imageZone = acymailing_getVar('array', 'image_zone', array(), '');
								if(!empty($imageZone)){ ?>
									<input type="hidden" id="acy_media_browser_image_width" value=""/>
									<label for="acy_media_browser_image_target"><?php echo acymailing_translation('ACY_LINK'); ?></label>
									<input type="text" id="acy_media_browser_image_target" placeholder="<?php echo ACYMAILING_LIVE; ?>..." class="inputbox" style="width:100%" value=""/>
								<?php }else{ ?>
									<label for="acy_media_browser_image_width" style="display:inline;"><?php echo acymailing_translation('CAPTCHA_WIDTH'); ?></label>    <input type="text" id="acy_media_browser_image_width" style="width:23%;" value="" oninput="calculateSize(0, this.value)"/>
									<br/><label for="acy_media_browser_image_height" style="display:inline;"><?php echo acymailing_translation('CAPTCHA_HEIGHT'); ?></label>    <input type="text" id="acy_media_browser_image_height" style="width:22%;" value="" oninput="calculateSize(this.value, 0)"/>
									<br/><label for="acy_media_browser_image_align" style="display:inline;"><?php echo acymailing_translation('ALIGNMENT'); ?></label>
									<select id="acy_media_browser_image_align" class="chzn-done" style="width:50%">
										<option value=""><?php echo acymailing_translation('NOT_SET'); ?></option>
										<option value="left"><?php echo acymailing_translation('ACY_LEFT'); ?></option>
										<option value="right"><?php echo acymailing_translation('ACY_RIGHT'); ?></option>
									</select><br/>
									<label for="acy_media_browser_image_margin" style="display:inline;"><?php echo acymailing_translation('ACY_MARGIN'); ?></label>    <input type="text" style="width:23%;" id="acy_media_browser_image_margin" value=""/><br/>
									<label for="acy_media_browser_image_border" style="display:inline;"><?php echo acymailing_translation('ACY_BORDER'); ?></label>    <input type="text" style="width:23%;" id="acy_media_browser_image_border" value=""/><br/>
									<label for="acy_media_browser_image_class" style="display:inline;"><?php echo acymailing_translation('ACY_CLASS'); ?></label>    <input type="text" style="width:50%;" id="acy_media_browser_image_class" value=""/>
									<input type="hidden" id="acy_media_browser_image_linkhref" value=""/>
								<?php } ?>
							</div>
							<button class="acymailing_button" type="button" onclick="validateImage();parent.acymailing.closeBox();" style=" position:absolute; bottom:6px; right:6px; "><?php echo acymailing_translation('INSERT'); ?> </button>
						</div>
					</td>
				</tr>
			</table>
			<div class="hidden-edition" id="image-edition">
				<div id="image-edition-content">
					<div class="drag-resize" draggable="true">
						<canvas id="edition-canvas"></canvas>
					</div>
				</div>
				<div class="image-edition-toolbar">
					<br />
					<?php echo acymailing_translation('ACY_IMAGE_EFFECTS') ?><br/>
					<button style="display: inline-block;width:127px;vertical-align: bottom;" type="button" class="acymailing_button_grey" onclick="roundedCorner()"><?php echo acymailing_translation('ACY_EFFECT_ROUNDED') ?></button>
					<input style="font-size:18px;display: inline-block;width:<?php echo ACYMAILING_J30 ? '45' : '58'; ?>px;" type="number" id="radius-image" min="0" max="100" value="50"/>
					<button style="width:100%;" type="button" class="acymailing_button_grey" onclick="changeToCrop()"><?php echo acymailing_translation('ACY_EFFECT_CROP') ?></button>
					<button style="width:100%;" type="button" class="acymailing_button_grey" onclick="changeToScale()"><?php echo acymailing_translation('ACY_EFFECT_SCALE') ?></button>
					<button style="width:100%;" type="button" class="acymailing_button_grey" onclick="cancelModification()"><?php echo acymailing_translation('ACY_CANCEL') ?></button>
					<br/><br/>
					<?php $formAction = acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'editor&task=saveImage'); ?>
					<form style="text-align: center;" id="form-edition" method="post" action="<?php echo $formAction ?>" onsubmit="return false;">
						<input style="width:176px;padding:5px;border-radius:4px;" type="text" name="imagename" id="imagename" value="" placeholder="<?php echo acymailing_translation('ACY_IMAGE_NAME') ?>">
						<input type="hidden" name="imagedata" id="imagedata" value="">
						<input type="hidden" name="pathtosave" id="pathtosave" value="">
						<button style="width:48%;display:inline-block" type="button" class="acymailing_button_grey" onclick="if(document.getElementById('imagename').value == ''){alert('<?php echo str_replace("'", "\'", acymailing_translation('FILL_ALL')); ?>');return false;}validateImageModification()"><?php echo acymailing_translation('ACY_SAVE') ?></button>
						<button style="width:48%;display:inline-block" type="button" class="acymailing_button_grey" onclick="closePanel()"><?php echo acymailing_translation('ACY_CANCEL') ?></button>
						<?php echo acymailing_formToken(); ?>
					</form>
				</div>
			</div>
			<div class="confirmBoxMM" id="confirmBoxMM" style="display: none;">
				<div id="acy_popup_content">
					<span class="confirmTxtMM" id="confirmTxtMM"></span><br/>
					<button class="acymailing_button" id="confirmCancelMM" onclick="document.getElementById('confirmBoxMM').style.display='none';" style="padding: 6px 15px 6px 10px;">
						<i class="acyicon-cancel" style="margin-right: 5px; font-size: 16px;top: 2px; position: relative;"></i><?php echo acymailing_translation('ACY_CANCEL'); ?>
					</button>
					<button class="acymailing_button acymailing_button_delete" id="confirmOkMM" style="padding: 8px 15px 6px 10px;">
						<i class="acyicon-delete" id="iconAction" style="margin-right: 5px; font-size: 12px;"></i><span id="textBtnAction"><?php echo acymailing_translation('ACY_DELETE'); ?></span>
					</button>
				</div>
			</div>
		</div>
		<?php

		$imageZone = acymailing_getVar('array', 'image_zone', array(), '');
		if($imageZone){
			echo '<script>checkSelected(true);</script>';
		}else{
			echo '<script>checkSelected();</script>';
		}

		if(isset($uploadMessage) && $uploadMessage == 'success' && file_exists(ACYMAILING_ROOT.rtrim($defaultFolder, '/').'/'.$this->imageName)){
			$imageSize = getimagesize(ACYMAILING_LIVE.rtrim($defaultFolder, '/').'/'.$this->imageName);
			echo '<script> displayImageFromUrl(\''.ACYMAILING_LIVE.rtrim($defaultFolder, '/').'/'.$this->imageName.'\',\'success\', \''.$this->imageName.'\', '.(empty($imageSize[0]) ? "null,null" : $imageSize[0].",".$imageSize[1]).');</script>';
		}
	}

	public function saveImage(){
		acymailing_checkToken();
		$data = $_POST['imagedata'];
		$name = acymailing_getVar('string', 'imagename', '');
		$pathtosave = acymailing_getVar('path', 'pathtosave', '', 'post');

		$uri = substr($data, strpos($data, ",") + 1);
		file_put_contents(ACYMAILING_ROOT.$pathtosave.DS.$name.'.png', base64_decode($uri));
	}

	public function createFolder(){
		acymailing_checkToken();
		$folderName = str_replace(array('.', '-'), array('', '_'), strtolower(acymailing_getVar('cmd', 'subFolderName')));
		if(empty($folderName)){
			$this->browse();
			return false;
		}

		$directoryPath = acymailing_getVar('string', 'acy_media_browser_files_path').'/'.$folderName;

		$mediaFolders = acymailing_getFilesFolder('media', true);
		$allowed = false;
		foreach($mediaFolders as $oneMedia){
			if(preg_match('#^'.preg_quote($oneMedia).'[a-z_0-9\-/]*$#i', $directoryPath)){
				$allowed = true;
				break;
			}
		}
		if(!$allowed){
			acymailing_enqueueMessage('You are not allowed to create this folder', 'error');
			$this->browse();
			return false;
		}

		$directoryPath = str_replace('/', DS, $directoryPath);
		if(is_dir(ACYMAILING_ROOT.$directoryPath)){
			acymailing_enqueueMessage(acymailing_translation('FOLDER_ALREADY_EXISTS'), 'warning');
			$this->browse();
			return false;
		}
		if(!acymailing_createFolder(ACYMAILING_ROOT.$directoryPath)){
			acymailing_enqueueMessage(acymailing_translation_sprintf('WRITABLE_FOLDER', substr(ACYMAILING_ROOT.$directoryPath, 0, strrpos(ACYMAILING_ROOT.$directoryPath, DS)), 'error'));
			$this->browse();
			return false;
		}
		acymailing_setVar('selected_folder', acymailing_getVar('string', 'acy_media_browser_files_path').'/'.$folderName);
		$this->browse();
	}
}
