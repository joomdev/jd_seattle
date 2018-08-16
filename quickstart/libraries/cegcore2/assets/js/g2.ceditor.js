//! ceditor
//! version : 1.0.0 beta
//! authors : ceditor.js contributors
//! license : MIT
//! chronoengine.com
(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	
	$.G2.ceditor = {};
	var Editor = $.G2.ceditor;
	
	Editor.selection = false;
	
	Editor.active = {};
	Editor.active.region = false;
	Editor.active.range = false;
	
	Editor.const = {};
	Editor.const.zwspRegex = new RegExp('\u200B');
	
	Editor.status = {};
	Editor.status.keydown = false;
	
	Editor.settings = {
		'mode' : 'html',
		'output' : 'bbcode',
		'trim_output' : true,
		'paste_block' : 'pre',
		'paste_html' : true,
		//'paste_clean' : true,
		'text_block' : 'div',//p is default
	};
	
	Editor.settings.elements = {
		'blocks' : ['div', 'p', 'pre', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li'],
		'allowed' : ['div', 'p', 'pre', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ol', 'ul', 'li', 'strong', 'em', 'ins', 'mark', 'li', 'img'],
		'split' : ['div', 'p', 'pre', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li'],
		'join' : ['strong', 'em', 'ins', 'mark', 'ul', 'ol'],
		'empty' : ['br', 'hr', 'img', 'i'],
	};
	
	Editor.settings.attrs = {
		'region' : {'class' : 'ui segment top attached'},
		'tags' : {
			'pre' : {'class' : 'ui message cfu-code', 'style' : 'overflow:auto; border:none;'},
			'blockquote' : {'class' : 'ui segment black inverted'},
			'img' : {'class' : 'ui image bordered centered'},
		}
	};
	
	Editor.settings.weights = {
		'#text' : 0, 
		'br' : 0, 
		'strong' : 10, 
		'em' : 10, 
		'ins' : 10, 
		'mark' : 10, 
		'span' : 20, 
		'a' : 40, 
		'li' : 45, 
		'ol' : 50, 
		'h1' : 50, 'h2' : 50, 'h3' : 50, 'h4' : 50, 'h5' : 50, 'h6' : 50, 
		'p' : 50, 
		'blockquote' : 50, 
		'pre' : 60, 
		'div' : 100, 
	};
	
	Editor.ready = function(selector){
		$.each($(selector), function(i, editor){
			Editor.init(editor);
		});
		
		Editor.buttons.init();
		Editor.buttons.sync();
	};
	
	Editor.init = function(editor){
		
		if($(editor).data('ready')){
			return false;
		}
		
		if($(editor).is(':input')){
			var textarea = editor;
			var editor = Editor.region.create(textarea);
			//$(textarea).css('max-height', '50px');
		}else{
			var textarea = Editor.textarea.create(editor);
		}
		
		Editor.region.init(editor);
		
		$(editor).add($(textarea)).on('empty', function(){
			$(editor).empty();
			$(textarea).val('');
			Editor.history.clear(editor);
			$(editor).trigger('modified');
		});
		
		$(editor).on('modified', function(e, task){
			//$(textarea).val($(editor).html());
			$(textarea).val(Editor.region.output(editor, task));
		});
		
		//initialize the output
		$(textarea).val(Editor.region.output(editor));
		$(textarea).css('display', 'none');
		
		//init the region content
		Editor.region.prepare(editor);
		
		$(editor).on('mousedown', function(e){
			if(e.button === 0){
				Editor.range.clear();
			}
		});
		
		$(document).off('mouseup').on('mouseup', function(e){
			Editor.setSelection();
			
			if(Editor.selection){
				Editor.range.prepare('selection');
				
				$(Editor.region.get()).trigger('selected');
				//$(Editor.region.get()).trigger('modified');
			}
		});
		
		$(editor).on('click', function(e){
			if(Editor.selection === false){
				var range = document.createRange();
				range.selectNodeContents(editor);
				range.collapse(false);
				Editor.range.set(range);
				
				$(editor).trigger('selected');
			}
		});
		
		$(editor).on('keydown', function(e){
			if(e.which == 13){
				e.preventDefault();
				Editor.range.prepare('break');
				Editor.input.Enter(e);
			}else if(e.which == 8 || e.which == 46){
				e.preventDefault();
				Editor.range.prepare('remove');
				Editor.input.Remove(e);
			}else if(e.which == 37 || e.which == 39){
				
			}else{
				Editor.range.prepare('type');
			}
		});
		
		$(editor).on('keyup', function(e){
			
			var range = Editor.range.get();
			
			if(e.which == 13){
				Editor.region.clean(range);
			}else if(e.which == 8 || e.which == 46){
				Editor.region.clean(range, 'prev');
			}
			
			$(editor).trigger('modified');
		});
		
		$(editor).on('input paste', function(e){
			if(e.originalEvent.clipboardData){
				e.preventDefault();
				Editor.range.prepare('paste');
				
				var pasted = e.originalEvent.clipboardData.getData('text/plain');
				var pastedHTML = e.originalEvent.clipboardData.getData('text/html');
				//console.log(pastedHTML);
				//var text = document.createTextNode(pasted);
				var range = Editor.range.get();
				
				if(!range.collapsed){
					range.extractContents();
				}
				
				var empty = document.createTextNode('\u200B');
				
				if(pastedHTML.length && Editor.settings.paste_html === true){
					var tmp = $('<div>').html(pastedHTML).get(0);
					
					$.each($(tmp).find('*'), function(k, node){
						if($(node).is(Editor.settings.elements.allowed.toString())){
							while(node.attributes.length > 0){
								node.removeAttribute(node.attributes[0].name);
							}
							
							if(Editor.settings.attrs.tags[node.nodeName.toLowerCase()] != undefined){
								$(node).attr(Editor.settings.attrs.tags[node.nodeName.toLowerCase()]);
							}
						}else{
							$(node).replaceWith($(node).contents());
						}
					});
					
					var nodes = $(tmp).contents();//children();
					var block = Editor.dom.block();
					
					if(block === false){
						var last = $(editor).children().last();
					}else{
						var last = block;
					}
					
					$.each(nodes, function(k, node){
						/*while(node.attributes.length > 0){
							node.removeAttribute(node.attributes[0].name);
						}*/
						if($(node).text().trim().length == 0 && $(node).children().length == 0){
							return true;
						}
						
						if(Editor.settings.paste_clean === true){
							//$(node).html($(node).text());
						}
						
						if($(node).is(Editor.settings.elements.blocks.toString())){
							$(last).after(node);
						
							last = node;
						}else{
							$(last).append(node);
						}
					});
					
					range.selectNodeContents(last);
					range.collapse(false);
				}else{
					var tmp = $('<div>').text(pasted);
					
					tmp.html(tmp.html().replace(/(\u0009)+/g, ' '));
					tmp.html(tmp.html().replace(/(?:\r\n|\r|\n)/g, '<br />'));
					
					range.insertNode(empty);
					
					var nodes = tmp.contents();
					
					var block = Editor.dom.block();
					var last = empty;
					$.each(nodes, function(k, node){
						$(last).after(node);
						last = node;
					});
					
					range.setStartAfter(last);
					range.setEndAfter(last);
					
					if(Editor.settings.paste_block && (pasted.indexOf('\u0009') > -1)){
						var newBlock = Editor.dom.changeTag({'name':Editor.settings.paste_block}, block);
						range.selectNodeContents(newBlock);
						range.collapse(false);
					}
				}
				Editor.range.set(range);
				
				Editor.region.clean(range);
				
				$(editor).trigger('modified');
			}
		});
		
		$(editor).on('modified selected', function(e){
			Editor.setSelection();
			
			Editor.region.prepare();
			
			Editor.buttons.sync();
		});
		/*
		$(editor).on('mousedown', function(e){
			Editor.range.clear();
			
			Editor.active.region = this;
			
			Editor.region.prepare();
			//console.log(33);
		});
		
		$(editor).on('keydown', function(e){
			Editor.active.region = this;
			
			if(e.which == 13){
				e.preventDefault();
				Editor.range.prepare('break');
				Editor.status.keydown = e;
				Editor.input.Enter(e);
			}else if(e.which == 8 || e.which == 46){
				e.preventDefault();
				Editor.range.prepare('remove');
				Editor.status.keydown = e;
				Editor.input.Remove(e);
			}else if(e.which == 37 || e.which == 39){
				
			}else{
				Editor.status.keydown = false;
				Editor.range.prepare('type');
			}
			
		});
		
		$(editor).on('input paste', function(e){
			if(e.originalEvent.clipboardData){
				e.preventDefault();
				//console.log(e);
				var text = document.createTextNode(e.originalEvent.clipboardData.getData('text/plain'));
				var range = Editor.range.get();
				
				if(!range.collapsed){
					range.extractContents();
				}
				
				range.insertNode(text);
				
				range.setStartAfter(text);
				range.setEndAfter(text);
			}
		});
		
		$(document).off('mouseup keyup').on('mouseup keyup', function(e){
			Editor.selection = false;
			//console.log(e);
		});
		
		$(editor).on('mouseup keyup', function(e){
			e.stopPropagation();
			Editor.selection = false;
			Editor.setSelection();
			
			if(Editor.selection){
				$(Editor.region.get()).trigger('modified');
			}
		});
		
		$(editor).on('modified', function(e){
			Editor.setSelection();
			
			Editor.region.prepare();
			
			Editor.buttons.sync();
		});
		*/
	}
	
	Editor.getSelection = function(){
		var selection;
		if(window.getSelection){
			selection = window.getSelection();
		}else if (document.selection && document.selection.type != 'Control'){
			selection = document.selection;
		}
		
		return selection;
	}
	Editor.setSelection = function(){
		this.selection = Editor.getSelection();
		
		if($(this.selection.anchorNode).closest('[data-editor="region"]').length == 0 || $(this.selection.focusNode).closest('[data-editor="region"]').length == 0){
			this.selection = false;
		}else{
			//Editor.range.prepare();
		}
	}
	
	Editor.buttons = {};
	Editor.buttons.init = function(){
		$('[data-editor="button"]').off('click').on('click', function(){
			if($(this).data('region')){
				var region = $('[data-editor="region"][data-name="'+$(this).data('region')+'"]');
				if(region.length && !region.get(0).isSameNode(Editor.active.region)){
					return false;
				}
			}
			
			var task = $(this).data('task');
			var tag = $(this).data('tag');
			var fn = $(this).data('fn');
			
			Editor.range.prepare('button');
			
			if(fn != undefined){
				if(fn.name == 'modal'){
					Editor.modal.show(fn.id, task, tag);
				}else if(fn.name == 'popup'){
					//var $popup = $(id).first();
					var $popup = $(this).popup({
						'popup' : $(fn.id).first(),
						'on' : 'click',
						'position' : 'top center'
					}).popup('toggle').popup('reposition');
				}
			}else{
				Editor.app.apply(task, tag);
				
				//if(task != 'undo'){
					$(Editor.region.get()).trigger('modified', [task]);
				//}
			}
		});
		
		Editor.buttons.extra();
	}
	
	Editor.buttons.sync = function(){
		$.each($('[data-editor="button"]'), function(k, btn){
			if($(this).data('region')){
				var region = $('[data-editor="region"][data-name="'+$(this).data('region')+'"]');
				if(region.length && !region.get(0).isSameNode(Editor.active.region)){
					status = 'disabled';
					Editor.buttons.status(btn, status);
					return true;
				}
			}
			
			var task = $(this).data('task');
			var tag = $(this).data('tag');
			var fn = $(this).data('fn');
			var status;
			
			if(Editor.selection && Editor.range.can[task] != undefined && Editor.range.can[task](tag, Editor.range.get())){
				//$(btn).removeClass('disabled');
				
				if(Editor.range.has[task] != undefined && Editor.range.has[task](tag, Editor.range.get())){
					status = 'active';//$(btn).addClass('active');
				}else{
					status = 'enabled';//.removeClass('active');
				}
				
			}else{
				status = 'disabled';//$(btn).addClass('disabled').removeClass('active');
			}
			
			if(fn != undefined && fn.name == 'popup'){
				status = 'enabled';
				
				if($(fn.id).find('[data-editor="button"].disabled').length){
					status = 'disabled';
				}
				
				if($(fn.id).find('[data-editor="button"].active').length){
					status = 'active';
				}
				//Editor.buttons.status($('[data-editor="button"][data-name="'+$(btn).data('group')+'"]'), status);
			}
			
			if(texture = Editor.dom.texture()){
				status = 'disabled';
				
				if(task == 'texture' && tag.name == $(texture).data('name')){
					status = 'active';
				}
			}
			
			Editor.buttons.status(btn, status);
		});
	}
	
	Editor.buttons.status = function(btn, status){
		if(status == 'active'){
			$(btn).removeClass('disabled').addClass('active');
		}else if(status == 'disabled'){
			$(btn).addClass('disabled').removeClass('active');
		}else if(status == 'enabled'){
			$(btn).removeClass('disabled').removeClass('active');
		}
	}
	
	Editor.buttons.extra = function(){
		$('[data-editor="upload"]').off('click').on('click', function(){
			var btn = this;
			var formArea = $(btn).closest('.form');
			requestData = new FormData();
			
			$.each(formArea.find(':input').serializeArray(), function(key, input){
				requestData.append(input.name, input.value);
			});
			//add files data
			formArea.find('input[type="file"]').each(function(key, input){
				requestData.append($(input).attr('name'), $(input)[0].files[0]);
			});
			
			$.ajax({
				type: 'POST',
				url: $(btn).data('url'),
				data: requestData,
				processData: false,
				contentType: false,
				beforeSend: function(){
					formArea.addClass('loading');
				},
				error: function(xhr, textStatus, message){
					formArea.removeClass('loading');
					formArea.find('.results').html('<div class="ui message red">'+textStatus+':'+message+'</div>');
				},
				success: function(result){
					formArea.removeClass('loading');
					if(result.substring(0, 1) == '{' && result.slice(-1) == '}'){
						var json = JSON.parse(result);
						
						$.each(json, function(type, info){
							var resultContent = '';
							
							if(type == 'error'){
								resultContent = $('<div class="ui message red">'+info+'</div>');
							}else if(type == 'success'){
								resultContent = $('<div class="ui center aligned icon header green"><i class="circular checkmark icon green"></i>'+info+'</div>');
							}else{
								if(formArea.find('input[name="'+type+'"]').length){
									formArea.find('input[name="'+type+'"]').val(info);
								}
							}
							
							if(resultContent.length){
								formArea.find('.results').html(resultContent);
							}
						});
					}
					
					return true;
				}
			});
		});
	}
	
	Editor.modal = {};
	Editor.modal.show = function(id, task, tag){
		var range = Editor.range.get().cloneRange();
		
		var $modal = $(id).first();
		var $modalObj = $modal.modal({
			'detachable' : false, 
			'inverted' : true,
			'onHide' : function(){
				Editor.range.set(range);
				$(Editor.region.get()).trigger('modified');
			}
		});
		//clear previous values
		$modal.find(':input').val('');
		//fill fields
		var target = Editor.range.hasTag(tag, range);
		$.each(target.attributes, function(k, v){
			$modal.find(':input[name="'+v['name']+'"]').val(v['value']);
		});
		
		$modalObj.modal('show');
		
		$modal.find('.ui.button.apply').off('click').on('click', function(){
			var attrs = {};
			$.each($modal.find(':input').serializeArray(), function(k, v){
				attrs[v['name']] = v['value'];
			});
			
			tag['attrs'] = attrs;
			
			Editor.range.set(range);
			Editor.app.apply(task, tag);
			
			if(Editor.range.get()){
				var newRange = Editor.range.get().cloneRange();
				
				$modalObj.modal('hide');
				
				Editor.range.set(newRange);
			}else{
				$modalObj.modal('hide');
			}
			
			$(Editor.region.get()).trigger('modified');
			
		});
		
		$modal.find('.ui.button.cancel').off('click').on('click', function(){
			$modalObj.modal('hide');
			
			Editor.range.set(range);
			$(Editor.region.get()).trigger('modified');
		});
	}
	
	Editor.weight = {};
	Editor.weight.get = function(tagName){
		if(Editor.settings.weights[tagName] != undefined){
			return Editor.settings.weights[tagName];
		}else{
			//console.log(tagName);
			return false;
			//return 0;
		}
	}
	
	Editor.range = {};
	Editor.range.get = function(){
		var range;
		if(Editor.selection !== false){
			try{
				range = Editor.selection.getRangeAt(0);
			}catch(e){
				range = false;
			}
		}else{
			range = false;
		}
		
		if(range === false){
			if(Editor.active.range){
				range = Editor.active.range;
			}else if(Editor.active.region){
				var range = document.createRange();
				range.selectNodeContents(Editor.active.region);
				range.collapse(false);
			}
		}
		
		Editor.active.range = range;
		
		return range;
	}
	
	Editor.range.set = function(range){
		if(range){
			var docSelection = Editor.getSelection();
			docSelection.removeAllRanges();
			docSelection.addRange(range);
			
			Editor.setSelection();
		}
	}
	
	Editor.range.clear = function(range){
		if(Editor.selection){
			Editor.selection.removeAllRanges();
			Editor.selection = false;
		}
	}
	
	Editor.range.hasTag = function (tag, range){
		if(range == undefined){
			range = this.get();
		}
		//console.log(tag);
		//console.log($(range.cloneRange().commonAncestorContainer.cloneNode()));
		
		var selected = $(range.cloneContents());
		if(selected.contents().length == 1){
			if(selected.contents().first().get(0).nodeName.toLowerCase() != '#text'){
				if(Editor.dom.compare(tag, selected.contents().first().get(0))){
					//return selected.contents().first().get(0);
					return $(range.startContainer).contents().get(range.startOffset);
				}
			}
		}
		//if(range.commonAncestorContainer.nodeName.toLowerCase() == tag.name){
		if(Editor.dom.compare(tag, range.commonAncestorContainer)){
			return range.commonAncestorContainer;
		}
		
		var parents = $(range.commonAncestorContainer).parentsUntil('[data-editor="region"]');
		
		var found = false;
		//console.log(parents);
		$.each(parents, function(i, parent){
			//if(parent.nodeName.toLowerCase() == tag.name){
			if(Editor.dom.compare(tag, parent)){
				found = parent; 
				return false;
			}
		});
		
		return found;
	}
	
	Editor.range.replaceWith = function(node, range){
		if(range == undefined){
			range = this.get();
		}
		
		range.extractContents();
		
		if(node.nodeName != undefined){
			range.insertNode(node);
		}else{
			$.each(node, function(k, vnode){
				range.insertNode(vnode);
			});
		}
	}
	
	Editor.range.can = {};
	Editor.range.can.format = function(tag, range){
		if(!range.collapsed){
			return true;
		}
		
		return false;
	}
	Editor.range.can.insert = function(tag, range){
		if(Editor.selection){
			return true;
		}
		
		return false;
	}
	Editor.range.can.recycle = function(tag, range){
		if(!range.collapsed){
			return true;
		}
		
		return false;
	}
	Editor.range.can.undo = function(tag, range){
		var region = Editor.region.get();
		if(Editor.history.get(region)){
			return true;
		}
		
		return false;
	}
	Editor.range.can.block = function(tag, range){
		if(block = Editor.dom.block() || !range.collapsed){
			return true;
		}
		
		return false;
	}
	Editor.range.can.list = function(tag, range){
		if(block = Editor.dom.block() || !range.collapsed){
			return true;
		}
		
		return false;
	}
	Editor.range.can.blockstyle = function(tag, range){
		if(block = Editor.dom.block()){
			return true;
		}
		
		return false;
	}
	Editor.range.can.inlinestyle = function(tag, range){
		if(!range.collapsed){
			return true;
		}
		
		return false;
	}
	Editor.range.can.link = function(tag, range){
		if(Editor.selection){
			parent = Editor.range.hasTag(tag, range);
			if(!range.collapsed){
				return true;
			}else{
				if(parent){
					return true;
				}
			}
		}
		
		return false;
	}
	Editor.range.can.image = function(tag, range){
		if(Editor.selection){
			return true;
		}
		
		return false;
	}
	Editor.range.can.texture = function(tag, range){
		if(Editor.selection){
			return true;
		}
		
		return false;
	}
	Editor.range.can.upload = function(tag, range){
		if(Editor.selection){
			return true;
		}
		
		return false;
	}
	
	Editor.range.has = {};
	Editor.range.has.format = function(tag, range){
		if(Editor.range.hasTag(tag, range)){
			return true;
		}
		
		return false;
	}
	Editor.range.has.block = function(tag, range){
		if(block = Editor.dom.block()){
			if(Editor.range.hasTag(tag, range)){
				return true;
			}
		}
		
		return false;
	}
	Editor.range.has.list = function(tag, range){
		//if(block = Editor.dom.block()){
			if(Editor.range.hasTag(tag, range)){
				return true;
			}
		//}
		
		return false;
	}
	Editor.range.has.blockstyle = function(attrs, range){
		if(block = Editor.dom.block()){
			var result = true;
			$.each(attrs, function(name, value){
				var preset = $(block).css(name);
				
				if(value != preset){
					result = false;
					return false;
				}
			});
			
			return result;
		}
		
		return false;
	}
	Editor.range.has.inlinestyle = function(tag, range){
		if(Editor.range.hasTag(tag, range)){
			return true;
		}
		
		return false;
	}
	Editor.range.has.link = function(tag, range){
		if(Editor.range.hasTag(tag, range)){
			return true;
		}
		
		return false;
	}
	Editor.range.has.image = function(tag, range){
		if(Editor.range.hasTag(tag, range)){
			return true;
		}
		
		return false;
	}
	Editor.range.has.texture = function(tag, range){
		/*if(Editor.range.hasTag(tag, range)){
			return true;
		}*/
		
		return false;
	}
	Editor.range.has.upload = function(tag, range){
		return false;
	}
	
	Editor.dom = {};
	Editor.dom.node = function(){
		if(range = Editor.range.get()){
			var newRange = range.cloneRange();
			newRange.collapse(true);
			
			if($(newRange.startContainer).is('[data-editor="region"]')){
				return false;
			}
			
			return newRange.startContainer;
		}
		
		return false;
	}
	
	Editor.dom.element = function(){
		if(node = Editor.dom.node()){
			var range = Editor.range.get();
			var element = node;
			
			if(range.startContainer.isSameNode(range.endContainer)){
				if(range.startContainer.nodeName.toLowerCase() != '#text'){
					if((range.endOffset - range.startOffset) == 1){
						element = range.startContainer.childNodes[range.startOffset];
					}
				}
			}
			
			if($(element).is('[data-editor="region"]')){
				return false;
			}
			
			if(element.nodeName.toLowerCase() != '#text'){
				return element;
			}else{
				if(!$(element).parent().is('[data-editor="region"]')){
					return $(element).parent().get(0);
				}
			}
			/*
			if(Editor.settings.elements.blocks.indexOf(node.nodeName.toLowerCase()) > -1){
				return node;
			}else{
				return $(node).closest(Editor.settings.elements.blocks.toString()).get(0);
			}
			*/
		}
		
		return false;
	}
	
	Editor.dom.block = function(node, accept_texture){
		if(node == undefined || node == false){
			node = Editor.dom.element();
		}
		
		if(accept_texture == undefined){
			accept_texture = false;
		}
		//if(node = Editor.dom.node()){
		if(node){
			var block;
			if(Editor.settings.elements.blocks.indexOf(node.nodeName.toLowerCase()) > -1){
				block = node;
			}else{
				block = $(node).closest(Editor.settings.elements.blocks.toString()).get(0);
			}
			
			if(!accept_texture && $(block).is('[data-editor="texture"]')){
				block = $(block).parent().closest(Editor.settings.elements.blocks.toString()).get(0);
			}
			
			if(!$(block).is('[data-editor="region"]')){
				return block;
			}
		}
		/*
		if(element = this.element()){
			if(!$(element).is('[data-editor]')){
				return element;
			}
		}
		*/
		return false;
	}
	
	Editor.dom.texture = function(){
		if(node = Editor.dom.element()){
			block = $(node).closest('[data-editor]').get(0);
			
			if($(block).is('[data-editor="texture"]')){
				return block;
			}
		}
		
		return false;
	}
	
	Editor.dom.changeTag = function(tag, block){
		var newBlock = Editor.dom.format(block, tag);
		$(block).replaceWith(newBlock);
		
		if(Editor.settings.attrs.tags[tag.name] != undefined){
			$(newBlock).attr(Editor.settings.attrs.tags[tag.name]);
		}
		
		return newBlock;
	}
	
	Editor.dom.equal = function(node1, node2){
		return $(node1).clone().empty().get(0).isEqualNode($(node2).clone().empty().get(0));
		/*
		var childCSS = Editor.dom.styles(newNode.get(0).lastChild);
		var cleanCSS = Editor.dom.styles(clean);
		var similar = true;
		$.each(childCSS, function(name, value){
			if(cleanCSS[name] != value){
				similar = false;
				return false;
			}
		});
		*/
	}
	
	Editor.dom.children = function(el, type){
		return $(el).find(':not(iframe)').addBack().contents().filter(function(){
			return this.nodeName.toLowerCase() == type;
		});
	}
	
	Editor.dom.compare = function(tag, node){
		var result = true;
		if(node.nodeName.toLowerCase() != tag.name){
			result = false;
		}
		
		if(result){
			if(tag.css != undefined){
				$.each(tag.css, function(name, value){
					var preset = $(node).css(name);
					if(preset != value){
						result = false;
						return false;
					}
				});
			}
			
			if(tag.attrs != undefined){
				$.each(tag.attrs, function(name, value){
					var preset = $(node).attr(name);
					if(preset != value){
						result = false;
						return false;
					}
				});
			}
			
			return result;
		}
		
		return false;
	}
	
	Editor.dom.split = function(element, range){
		var tagRange = document.createRange();
		tagRange.selectNodeContents(element);
		
		var tipRange = tagRange.cloneRange();
		tipRange.setEnd(range.startContainer, range.startOffset);
		
		var tailRange = tagRange.cloneRange();
		tailRange.setStart(range.endContainer, range.endOffset);
		
		var newNode1 = $(element).clone().empty().get(0);
		var newNode2 = $(element).clone().empty().get(0);
		
		var tipContent = tipRange.cloneContents();
		var tailContent = tailRange.cloneContents();
		
		if($(tipContent).text().trim().length == 0 && $(tipContent).children().length == 0){
			//tipContent = document.createElement('br');
			tipContent = document.createTextNode('\u200B');
		}
		//if($(tailContent).text().trim().length == 0){
		if($(tailContent).text().replace(/(\u200B)+/g, '').trim().length == 0 && $(tailContent).children().length == 0){
			//tailContent = document.createElement('br');
			tailContent = document.createTextNode('\u200B');
			
			if(['li', 'ul', 'ol'].indexOf(element.nodeName.toLowerCase()) == -1){
				var newNode2 = document.createElement(Editor.settings.text_block);
			}
		}
		
		$(newNode1).append(tipContent);
		$(newNode2).append(tailContent);
		
		$(element).before($(newNode1));
		$(element).before($(newNode2));
		$(element).remove();
		
		range.selectNodeContents(newNode2);
		range.collapse(true);
		
		return [newNode1, newNode2];
	}
	
	Editor.dom.styles = function(node){
		return $(node).css(['color']);
	}
	
	Editor.input = {};
	Editor.input.Enter = function(e){
		e.preventDefault();
		
		var block = Editor.dom.block();
		var range = Editor.range.get();
		
		if(!range.collapsed){
			range.extractContents();
			//Editor.range.prepare('break');
		}
		
		var split = block && (Editor.settings.elements.split.indexOf(block.nodeName.toLowerCase()) > -1);
		
		if(e.shiftKey){
			if(block){
				split = !split;
			}
		}
		
		if(block && split){
			if(block.nodeName.toLowerCase() == 'li' && $(block).text().replace(/(\u200B)+/g, '').length == 0){
				var ul = $(block).parent('ul, ol').first().get(0);
				range.selectNode(block);
				range.collapse(true);
				$(block).remove();
				
				var newNodes = Editor.dom.split(ul, range);
				var empty = document.createTextNode('\u200B');
				var p = document.createElement(Editor.settings.text_block);
				$(p).append(empty);
				$(newNodes[0]).after(p);
				range.selectNodeContents(empty);
				range.collapse(true);
			}else{
				var newNodes = Editor.dom.split(block, range);
			}
			
			//range.selectNodeContents(newNodes[1]);
			//range.collapse(true);
		}else{
			var newLine = $('<br />').get(0);//document.createElement('br');
			
			range.extractContents();
			
			var empty = document.createTextNode('\u200B');
			range.insertNode(empty);
			range.insertNode(newLine);
			
			range.setStartAfter(empty);
			range.setEndAfter(empty);
		}
		
		Editor.range.set(range);
		//Editor.region.clean(range);
	}
	
	Editor.find = {};
	Editor.find.node = function(node){
		if($(node).is('[data-editor="region"]')){
			return false;
		}
		
		return node;
	}
	Editor.find.block = function(node){
		var block = $(node).closest(Editor.settings.elements.blocks.toString());
		
		if(block.length && !block.is('[data-editor="region"]')){
			return block.get(0);
		}else{
			return false;
		}
	}
	Editor.find.texture = function(node){
		var texture = $(node).closest('[data-editor="texture"]');
		if(texture.length){
			return texture.get(0);
		}else{
			return false;
		}
	}
	Editor.find.selected = function(){
		var selected = false;
		
		if(range = Editor.range.get()){
			if(range.startContainer.isSameNode(range.endContainer)){
				if(range.startContainer.nodeName.toLowerCase() != '#text'){
					if((range.endOffset - range.startOffset) == 1){
						selected = range.startContainer.childNodes[range.startOffset];
					}else{
						selected = range.startContainer;
					}
				}else{
					selected = range.startContainer.parentElement;
				}
			}else{
				//multi selection
				selected = range.commonAncestorContainer;
			}
		}
		
		return selected;
	}
	
	Editor.range.nodes = function(range){
		var _iterator = document.createNodeIterator(
			range.commonAncestorContainer,
			NodeFilter.SHOW_ALL, // pre-filter
			{
				// custom filter
				acceptNode: function (node) {
				return NodeFilter.FILTER_ACCEPT;
				}
			}
		);

		var _nodes = [];
		while(currentNode = _iterator.nextNode()){console.log(currentNode);
			if(_nodes.length === 0 && !currentNode.isSameNode(range.startContainer)){
				continue;
			}
			
			_nodes.push(currentNode);
			
			if(currentNode.isSameNode(range.endContainer)){
				break;
			}
		}
		
		return _nodes;
	}
	
	Editor.range.prepare = function(act){
		var range = Editor.range.get();
		
		if(act == 'selection'){
			if(texture = Editor.find.texture(range.startContainer)){
				range.setStartBefore(texture);
			}
			
			if(texture = Editor.find.texture(range.endContainer)){
				range.setEndAfter(texture);
			}
		}else{
			if(range.collapsed){
				var node = Editor.find.node(range.startContainer);
				var block = Editor.find.block(range.startContainer);
				
				var empty = document.createTextNode('\u200B');
				//console.log(range);
				//console.log(block);
				if(block === false){
					var p = document.createElement(Editor.settings.text_block);
					range.insertNode(p);
					range.selectNodeContents(p);
					
					range.insertNode(empty);
					range.selectNodeContents(empty);
					range.collapse(false);
				}else if((node === false || node.nodeName.toLowerCase() != '#text')){
					range.insertNode(empty);
					
					if(empty.previousSibling && empty.previousSibling.nodeName.toLowerCase() == '#text'){
						range.setStart(empty.previousSibling, empty.previousSibling.length);
						range.setEnd(empty.previousSibling, empty.previousSibling.length);
						$(empty).remove();
					}else if(empty.nextSibling && empty.nextSibling.nodeName.toLowerCase() == '#text'){
						range.setStart(empty.nextSibling, 0);
						range.setEnd(empty.nextSibling, 0);
						$(empty).remove();
					}else{
						range.selectNodeContents(empty);
						range.collapse(false);
					}
				}
				/*
				if(act != 'type' && (node === false || node.nodeName.toLowerCase() != '#text')){
					var empty = document.createTextNode('\u200B');
					
					var block = Editor.find.block(range.startContainer);
					if(block === false){
						var p = document.createElement(Editor.settings.text_block);
						range.insertNode(p);
						range.selectNodeContents(p);
					}
					
					range.insertNode(empty);
					
					if(empty.previousSibling && empty.previousSibling.nodeName.toLowerCase() == '#text'){
						range.setStart(empty.previousSibling, empty.previousSibling.length);
						range.setEnd(empty.previousSibling, empty.previousSibling.length);
						$(empty).remove();
					}else if(empty.nextSibling && empty.nextSibling.nodeName.toLowerCase() == '#text'){
						range.setStart(empty.nextSibling, 0);
						range.setEnd(empty.nextSibling, 0);
						$(empty).remove();
					}else{
						range.selectNodeContents(empty);
						range.collapse(false);
					}
					
				}
				*/
				/*
				if(block = Editor.dom.block()){
					$(block).attr('data-modified', 'true');
				}
				*/
			}
			
			if(block = Editor.dom.block(range.startContainer)){
				$(block).attr('data-modified', 'true');
			}
			
			if(block = Editor.dom.block(range.endContainer)){
				$(block).attr('data-modified', 'true');
			}
		}
		
		if(act == 'remove'){
			
		}
		
		Editor.range.set(range);
	}
	
	Editor.range.prepare2222 = function(act){
		var range = Editor.range.get();
		//console.log(range);
		if(range.collapsed){
			var node = Editor.dom.node();
			var block = Editor.dom.block();
			
			//Editor.region.normalize();
			
			var insertEmpty = false;
			if(node === false || node.nodeName.toLowerCase() != '#text'){
				//no block and no node || no text node
				var reference = document.createTextNode('');
				range.insertNode(reference);
				
				var sibNode = Editor.dom.sibling(reference, '#text', true);
				/*
				if(prevSib){
					var sibNode = prevSib;
				}else if(nextSib){
					var sibNode = nextSib;
				}
				*/
				if(sibNode){
					if(sibNode.node.nodeName.toLowerCase() == '#text'){
						range.selectNodeContents(sibNode.node);
						if(sibNode.direction == 'previousSibling'){
							range.collapse(false);
						}else{
							range.collapse(true);
						}
					}else{
						if($(sibNode.node).is(Editor.settings.elements.empty.toString())){
							range.selectNode(sibNode.node);
							if(sibNode.direction == 'previousSibling'){
								range.collapse(false);
							}else{
								range.collapse(true);
							}
						}else{
							if(sibNode.direction == 'previousSibling'){
								range.setStart(sibNode.node, sibNode.node.length);
								range.setEnd(sibNode.node, sibNode.node.length);
							}else{
								range.setStart(sibNode.node, 0);
								range.setEnd(sibNode.node, 0);
							}
						}
						
						insertEmpty = true;
					}
				}else{
					insertEmpty = true;
				}
				
				if(insertEmpty){
					var empty = document.createTextNode('\u200B');
					range.insertNode(empty);
					range.selectNodeContents(empty);
					range.collapse(false);
				}
				
				$(reference).remove();
			}
			
		}else{
			
			if(Editor.settings.mode == 'html'){
				if($(range.startContainer).is('[data-editor="region"]')){
					var startRange = document.createRange();
					startRange.selectNodeContents(range.startContainer.childNodes[range.startOffset]);
					startRange.collapse(true);
					range.setStart(startRange.startContainer, startRange.startOffset);
				}
				
				if($(range.endContainer).is('[data-editor="region"]')){
					var endRange = document.createRange();
					endRange.selectNodeContents(range.endContainer.childNodes[range.endOffset - 1]);
					endRange.collapse(false);
					range.setEnd(endRange.endContainer, endRange.endOffset);
				}
				
			}
		}
		
		//check if texture
		if(texture = Editor.dom.texture()){
			if(act == 'remove'){
				range.selectNodeContents(texture);
			}else{
				var block = $('<p></p>');
				var empty = document.createTextNode('\u200B');
				$(texture).after(block);
				$(block).append(empty);
				range.selectNodeContents(empty);
				range.collapse(false);
			}
		}
		
		Editor.range.set(range);
	}
	//2 issues, empty text nodes added by range.prepare and getting the prev/next node for removal
	Editor.input.Remove = function(e){
		e.preventDefault();
		var extracted;
		
		var direction;
		if(e.which == 8){
			direction = 'prev';
		}else if(e.which == 46){
			direction = 'next';
		}
		
		Editor.range.remove(direction);
	}
	
	Editor.range.remove = function(direction){
		var node = Editor.dom.node();
		var element = Editor.dom.element();
		var block = Editor.dom.block();
		var range = Editor.range.get();
		var extracted;
		var jumped = false;
		var count = 0;
		
		if(node){
			var limit;
			if(direction == 'next'){
				limit = node.length;
			}else{
				limit = 0;
			}
			//console.log(range);
			if(range.collapsed){
				if(node.nodeName.toLowerCase() == '#text'){
					if(range.startOffset == limit){
						
						var sibNode = Editor.dom.sibling(node, '#text', direction);
						//console.log(sibNode);
						if(block && sibNode && (sibBlock = Editor.dom.block(sibNode.node)) && !block.isSameNode(sibBlock)){
							//merge 2 blocks
							if($(sibBlock).is('[data-editor="texture"]')){
								//$(sibBlock).remove();
							}else{
								if(direction == 'next'){
									var sibChild = $(block).contents().last().get(0);
									$(block).append($(sibBlock).contents());
									$(block).attr('data-modified', 'true');
									$(sibBlock).remove();
								}else{
									var sibChild = $(sibBlock).contents().last().get(0);
									$(sibBlock).append($(block).contents());
									$(sibBlock).attr('data-modified', 'true');
									$(block).remove();
								}
								range.selectNode(sibChild);
								range.collapse(false);
							}
							count++;
							jumped = true;
						}else{
							if(sibNode){
								if(sibNode.node.nodeName.toLowerCase() == '#text'){
									var start = sibNode.node.length - 1;
									var end = sibNode.node.length;
									if(direction == 'next'){
										start = 0;
										end = 1;
									}
									range.setStart(sibNode.node, start);
									range.setEnd(sibNode.node, end);
									extracted = range.extractContents();
									count++;
									/*
									if(sibNode.length){
										var last_char = sibNode.nodeValue.substr(sibNode.length - 1, 1);
										if(last_char == ' '){
											sibNode.nodeValue = sibNode.nodeValue.substr(0, sibNode.length - 1) + '\u00A0';
										}
									}
									*/
								}else{
									range.selectNode(sibNode.node);
									extracted = range.extractContents();
									count++;
								}
								
							}
						}
					}else{
						if(direction == 'next'){
							range.setEnd(range.startContainer, range.startOffset + 1);
						}else{
							range.setStart(range.startContainer, range.startOffset - 1);
						}
						//if this is the last character in the last node, add zwsp so the block does not collapse
						//if(range.startContainer.length == 1){
						if(range.startContainer.length == 1){
							//console.log(range);
							var empty = document.createTextNode('\u200B');
							$(range.startContainer).before(empty);
						}
						
						extracted = range.extractContents();
						count++;
					}
				}else{
					range.selectNode(node);
					extracted = range.extractContents();
					count++;
				}
			}else{
				extracted = range.extractContents();
			}
			
			//if the removed is a zero width space
			//if(($(extracted).contents().length == 1) && ($(extracted).contents().get(0).nodeName.toLowerCase() == '#text')){
			if(extracted && (extracted.childNodes.length == 1) && (extracted.childNodes[0].nodeName.toLowerCase() == '#text')){
				//if($(extracted).contents().get(0).length == 1 && Editor.const.zwspRegex.test($(extracted).contents().get(0).nodeValue)){
				if(extracted.childNodes[0].length == 1 && Editor.const.zwspRegex.test(extracted.childNodes[0].nodeValue)){
					if(jumped){
						//another block has been emptied, do not remove other chars
					}else{
						Editor.range.remove(direction);
					}
				}
			}else{
				//console.log($(extracted));
			}
			
			//Editor.region.clean(range, 'prev');
		}
	}
	/*
	Editor.input.Move = function(e){
		e.preventDefault();
		
		var direction;
		if(e.which == 37){
			direction = 'prev';
		}else if(e.which == 39){
			direction = 'next';
		}
		
		Editor.range.move(direction);
	}
	
	Editor.range.move = function(direction){
		var node = Editor.dom.node();
		var element = Editor.dom.element();
		var block = Editor.dom.block();
		var range = Editor.range.get();
		
		var startpoint;
		
		if(direction == 'next'){
			startpoint = {'node' : range.endContainer, 'offset' : range.endOffset};
		}else{
			startpoint = {'node' : range.startContainer, 'offset' : range.startOffset};
		}
		
		var findsib = false;
		if(startpoint.node.nodeName.toLowerCase() == '#text'){
			if(direction == 'next' && startpoint.offset == startpoint.node.length){
				findsib = true;
			}else if(direction == 'prev' && startpoint.offset == 0){
				findsib = true;
			}
			
			if(!findsib){
				if(direction == 'next'){
					range.setStart(startpoint.node, startpoint.offset + 1);
					range.setEnd(startpoint.node, startpoint.offset + 1);
				}else if(direction == 'prev'){
					range.setStart(startpoint.node, startpoint.offset - 1);
					range.setEnd(startpoint.node, startpoint.offset - 1);
				}
			}
		}
		
		if(findsib){
			var sibNode = Editor.dom.sibling(startpoint.node, '#text', direction);
			
			if(sibNode){
				if(sibNode.nodeName.toLowerCase() == '#text'){
					var sibBlock = Editor.dom.block(sibNode);
					var jump = block.isSameNode(sibBlock) ? 1 : 0;
					if(direction == 'next'){
						range.setStart(sibNode, jump);
						range.setEnd(sibNode, jump);
					}else if(direction == 'prev'){
						range.setStart(sibNode, sibNode.length - jump);
						range.setEnd(sibNode, sibNode.length - jump);
					}
				}else{
					
				}
				
			}
		}
	}
	*/
	Editor.dom.sibling = function(node, name, direction){
		var siblings = [];
		if(direction == 'next'){
			siblings.push('nextSibling');
		}else if(direction == 'prev'){
			siblings.push('previousSibling');
		}else if(direction === true){
			siblings.push('previousSibling', 'nextSibling');
		}
		
		var results = [];
		
		$.each(siblings, function(k, sibling){
			var distance = 0;
			var rnode = false;
			var _node = node;
			//console.log(_node);
			//console.log(_node[sibling]);
			while(_node[sibling] == null && !$(_node.parentNode).is('[data-editor="region"]')){
				_node = _node.parentNode;
				distance++;
			}
			//console.log(111);
			var targetNode = _node[sibling];
			
			if(targetNode){
				//we have found a previous node
				if(
					targetNode.nodeName.toLowerCase() == name || 
					$(targetNode).is(Editor.settings.elements.empty.toString()) || 
					$(targetNode).is('[data-editor="texture"]')
				){
					rnode = targetNode;
				}else{
					//previous node is not a text node, find the last text node inside
					targetNode.normalize(); //clean any empty text nodes
					//var textChildren = Editor.dom.children(targetNode, name);
					var textChildren = Editor.dom.contents(targetNode, name);
					if(textChildren.length){
						//return textChildren.last().get(0);
						if(direction == 'next'){
							rnode = textChildren.shift();
						}else{
							rnode = textChildren.pop();
						}
					}else{
						rnode = targetNode;
					}
				}
			}
			//console.log(rnode);
			results.push({'node' : rnode, 'distance' : distance, 'direction' : sibling});
		});
		//console.log(results);
		
		var r = false;
		$.each(results, function(k, robj){
			if(robj.node !== false){
				if(r === false || r.distance > robj.distance){
					r = robj;
				}
			}
		});
		//console.log(r);
		return r;
	}
	
	Editor.dom.contents = function(node, name){
		var pars = [];
		
		if(node.nodeName.toLowerCase() == name){
			pars.push(node);
		}
		
		if(node.childNodes.length > 0){
			$.each(node.childNodes, function(i, child){
				pars = pars.concat(Editor.dom.contents(child, name));
			});
		}
		
		return pars;
	}
	
	Editor.textarea = {};
	Editor.textarea.create = function(region){
		var textarea = $('<textarea>').attr('rows', 10).attr('cols', 100).get(0);
		if($(region).data('name')){
			$(textarea).attr('name', $(region).data('name'));
		}
		
		$(textarea).attr('data-ready', true);
		
		$(region).before(textarea);
		
		return textarea;
	}
	
	Editor.region = {};
	Editor.region.get = function(element){
		if(Editor.selection){
			return $(Editor.range.get().startContainer).closest('[data-editor="region"]').get(0);
		}else if(Editor.active.region){
			return Editor.active.region;
		}else{
			return $('[data-editor="region"]').first().get(0);
		}
	}
	
	Editor.region.create = function(textarea){
		var region = $('<div>').html($(textarea).val()).get(0);
		
		if($(textarea).attr('name')){
			$(region).attr('data-name', $(textarea).attr('name'));
		}
		
		$(region).attr('data-ready', true);
		$(textarea).attr('data-ready', true);
		
		$(region).attr(Editor.settings.attrs.region);
		$(region).css('min-height', $(textarea).outerHeight());
		
		$(textarea).after(region);
		
		return region;
	}
	
	Editor.region.init = function(region){
		$(region).attr('contenteditable', true);
		$(region).attr('data-editor', 'region');
		$(region).css('white-space', 'pre-wrap');
		
		$.each($(region).find('[data-editor="texture"]'), function(k, node){
			Editor.dom.texturize(node);
		});
		//console.log(region);
		$(region).on('selected', function(e){
			//console.log('selected');
			if($(region).children().last().is('[data-editor="texture"]')){
				var empty = document.createTextNode('\u200B');
				var p = document.createElement(Editor.settings.text_block);
				$(p).append(empty);
				$(region).append(p);
				
				if(range = Editor.range.get()){
					range.selectNodeContents(empty);
					range.collapse(true);
					Editor.region.focus(region);
					Editor.range.set(range)
				}
			}
		});
		
		$.each($(region).contents(), function(k, node){
			if($(node).is(Editor.settings.elements.blocks.toString()) && $(node).contents().length == 0){
				//empty block, add zwsp
				var empty = document.createTextNode('\u200B');
				$(node).append(empty);
			}
		});
		
		$(region).html(Editor.region.html(region));
		
		//Editor.history.snapshot(region);
	}
	
	Editor.region.focus = function(){
		/*setTimeout(function() {
			Editor.region.get().focus();
		}, 0);*/
	}
	
	Editor.region.prepare = function(region){
		if(region != undefined){
			
		}else{
			region = Editor.region.get();
		}
		
		var range = Editor.range.get();
		
		if(Editor.settings.mode == 'html'){
			if($(region).html().trim().length == 0 || $(region).html().trim() == '<br>' || $(region).html().trim().replace(/(\u200B)+/g, '').length == 0){
				$(region).empty();
				//Editor.range.store(range);
				var empty = document.createTextNode('\u200B');
				var p = document.createElement(Editor.settings.text_block);
				$(p).append(empty);
				
				//$(region).html('<p>\u200B</p>');
				$(region).append(p);
				
				Editor.region.focus();
				
				if(range){
					range.selectNodeContents($(region).children().first().get(0));
					range.collapse(false);
				
					Editor.range.set(range);
				}
			}else{
				
			}
		}
		
		$(region).find('img').off('click').on('click', function(){
			var newRange = document.createRange();
			newRange.selectNode(this);
			Editor.range.set(newRange);
			$(region).trigger('modified');
		});
		
		if(jQuery.fn.embed != undefined){
			$(region).find('.ui.embed').embed();
		}
		
		return region;
	}
	
	Editor.region.output = function(region, task){
		if(region != undefined){
			
		}else{
			region = Editor.region.get();
		}
		
		if($(region).find('[data-modified="true"]').length){
			$.each($(region).find('[data-modified="true"]'), function(k, modified){
				$(modified).removeAttr('data-modified');
			});
		}
		
		var output = $(region).html();
		
		if(task != 'undo'){
			Editor.history.snapshot(region, output);
		}
		
		output = output.replace(/(\u200B)+/g, '');
		//output = output.replace(/(\u00A0)+/g, ' ');
		//output = output.replace(/(&nbsp;)/g, ' ');
		
		var temp = $('<div>').html(output);
		if(temp.text().trim().length > 0 || $(region).find('img, iframe').length > 0){
			
			if(Editor.settings.trim_output === true){
				var removable = [];
				var content_found = false;
				$.each(temp.find(Editor.settings.elements.blocks.toString()), function(k, block){
					//if($(block).html().trim().length == 0){
					if($(block).children().not('br').length == 0 && $(block).text().trim().length == 0 && $(block).data('output') == undefined){
						var empty = document.createTextNode('\u200B');
						$(block).append(empty);
						removable.push(block);
					}else{
						if(content_found === false){
							$.each(removable, function(k, block){
								$(block).remove();
							});
						}
						removable = [];
						content_found = true;
					}
				});
				
				$.each(removable, function(k, block){
					$(block).remove();
				});
				
			}else{
				$.each(temp.find(Editor.settings.elements.blocks.toString()), function(k, block){
					if($(block).html().trim().length == 0){
						var empty = document.createTextNode('\u200B');
						$(block).append(empty);
					}
				});
			}
			
			output = temp.html();
		}else{
			output = '';
		}
		
		//Editor.history.snapshot(region, output);
		
		if(output.length && Editor.settings.output != undefined && Editor.output[Editor.settings.output] != undefined){
			output = Editor.output[Editor.settings.output](output);
		}
		
		return output;
	}
	
	Editor.region.html = function(region){
		if(region != undefined){
			
		}else{
			region = Editor.region.get();
		}
		
		var cleaned;
		
		if($(region).find('[data-modified="true"]').length){
			$.each($(region).find('[data-modified="true"]'), function(k, modified){
				cleaned = Editor.dom.clean(modified);
				$(modified).replaceWith(cleaned);
				//$(cleaned).removeAttr('data-modified');
			});
		}else{
			region = Editor.dom.clean(region);
		}
		//console.log($(region).clone());
		return $(region).html();
	}
	
	Editor.range.store = function(range){
		//console.log(range);
		//var tmpNode = $('<bravo class="ceditor-range"></bravo>').get(0);
		var startMarker = $('<param class="ceditor-range start">').get(0);
		var endMarker = $('<param class="ceditor-range end">').get(0);
		
		if(range.collapsed){
			if(range.startContainer.nodeName.toLowerCase() == 'br'){
				var empty = document.createTextNode('');
				$(range.startContainer).after(empty);
				range.selectNode(empty);
				range.collapse(true);
			}
			//range.insertNode(tmpNode);
			range.insertNode(startMarker);
			//update the active range
			range.setStartAfter(startMarker);
			range.collapse(true);
		}else{
			//range.surroundContents(tmpNode);
			var startRange = range.cloneRange();
			var endRange = range.cloneRange();
			
			startRange.collapse(true);
			startRange.insertNode(startMarker);
			
			endRange.collapse(false);
			endRange.insertNode(endMarker);
			
			//update the active range
			range.setStartAfter(startMarker);
			range.setEndBefore(endMarker);
		}
	}
	
	Editor.range.restore = function(node, range, pref){
		var startMarker = $(node).find('.ceditor-range.start').first();
		var endMarker = $(node).find('.ceditor-range.end').first();
		
		if(startMarker.length == 0){
			range.selectNodeContents(node);
			range.collapse(false);
			return;
		}
		
		range.collapse(true);
		
		range.setStartAfter(startMarker.get(0));
		range.collapse(true);
		startMarker.remove();
		
		if(endMarker.length){
			range.setEndBefore(endMarker.get(0));
			endMarker.remove();
		}
		
		
		if(range.startContainer.isSameNode(range.endContainer) && (range.endOffset == range.startOffset + 1)){
			//if(rangeChildrend.length == 1 && rangeChildrend.first().get(0).nodeName.toLowerCase() != '#text'){
			if(range.startContainer.childNodes[range.startOffset].nodeName.toLowerCase() != '#text'){
				//one node selected with 1 child, select the deepest child, suitable after unformat
				var child = range.startContainer.childNodes[range.startOffset];
				while(child.childNodes.length == 1){
					child = child.firstChild;
				}
				range.selectNodeContents(child);
			}
		}
		//range is in the region between 2 blocks, move it inside a block
		if(range.collapsed && $(range.startContainer).is('[data-editor="region"]')){
			if(range.startContainer.childNodes[range.startOffset]){
				range.selectNodeContents(range.startContainer.childNodes[range.startOffset]);
				range.collapse(true);
			}
		}
	}
	
	//remove
	/*
	Editor.range.restore2222 = function(node, range, pref){
		var tmpNode = $(node).find('.ceditor-range').first().get(0);
		
		var tmpChildren = $(tmpNode).contents();
		//console.log(tmpChildren);
		var tmpParent = $(tmpNode).parent();
		//console.log($(tmpNode).html());
		range.selectNode(tmpNode);
		range.collapse(true);
		$(tmpNode).replaceWith(tmpChildren);
		//tmpParent.get(0).normalize();
		
		if(tmpChildren.length){
			//range was not collapsed
			if(tmpChildren.length == 1 && tmpChildren.first().get(0).nodeName.toLowerCase() != '#text'){
				//one node selected with 1 child, selected the deepest child, suitable after unformat
				var child = tmpChildren.first().get(0);
				while(child.childNodes.length == 1){
					child = child.firstChild;
				}
				range.selectNodeContents(child);
			}else{
				//multiple nodes selected
				range.setStartBefore(tmpChildren.first().get(0));
				range.setEndAfter(tmpChildren.last().get(0));
			}
		}else{
			//range was collapsed
			if(tmpParent.contents().length == 0 && !tmpParent.is('[data-editor="region"]')){
				//range parent is an empty node, and the range was empty (collapsed), remove the parent
				//console.log('del empty parent');
				var highParent = tmpParent.parent();
				
				while(highParent.contents().length == 1 && !highParent.is('[data-editor="region"]')){
					tmpParent = highParent;
					highParent = highParent.parent();
				}
				
				if(Editor.settings.elements.blocks.indexOf(tmpParent.get(0).nodeName.toLowerCase()) > -1){
					//current parent is a block, do not remove it
					tmpParent.empty();
					var empty = document.createTextNode('\u200B');
					tmpParent.append(empty);
					range.selectNodeContents(empty);
					range.collapse(true);
				}else{
					range.selectNode(tmpParent.get(0));
					range.collapse(true);
					
					if(pref == 'prev' && tmpParent.get(0).previousSibling){
						range.selectNodeContents(tmpParent.get(0).previousSibling);
						range.collapse(false);
					}
					tmpParent.remove();
				}
			}
			
		}
	}
	*/
	Editor.history = {};
	Editor.history.snapshot = function(region, data){
		var ehistory = $(region).data('ehistory');
		if(ehistory == undefined){
			ehistory = [];
			$(region).data('elastmod', Date.now() - 5000);
		}
		
		var now = Date.now();
		//console.log((now - $(region).data('elastmod'))/1000);
		if((now - $(region).data('elastmod'))/1000 > 3){
			//ehistory.push($(region).html());
			ehistory.push(data);
			
			Editor.history.set(region, ehistory);
			//$(region).data('ehistory', ehistory);
			$(region).data('elastmod', Date.now());
		}
	}
	Editor.history.get = function(region){
		var ehistory = $(region).data('ehistory');
		if(ehistory == undefined || ehistory.length == 0){
			return false;
		}
		
		return ehistory;
	}
	Editor.history.set = function(region, images){
		$(region).data('ehistory', images);
		//console.log(images);
	}
	Editor.history.clear = function(region){
		$(region).data('ehistory', ['']);
	}
	
	Editor.region.clean = function (range, pref){
		var region = Editor.region.get();
		//console.log(region);
		if(range != undefined){
			Editor.range.store(range);
		}
		//console.log($(region).html());
		//console.log($(region).clone());
		if(region !== false){
			if($(region).find('[data-modified="true"]').length){
				$.each($(region).find('[data-modified="true"]'), function(k, modified){
					cleaned = Editor.dom.clean(modified);
					$(modified).replaceWith(cleaned);
				});
			}else{
				var cleaned = Editor.dom.clean(region);
				$(region).html($(cleaned).html());
			}
			//$(region).html(Editor.region.html(region));
		}
		//console.log($(region).html());
		
		//Editor.history.snapshot(region);
		//console.log($(region).clone());
		if(range != undefined){
			Editor.range.restore(region, range, pref);
			
			Editor.region.focus();
			
			Editor.range.set(range);
		}
	}
	
	Editor.dom.clean = function (element, pnames){
		if(pnames == undefined){
			var pnames = [];
		}
		
		var is_region = $(element).is('[data-editor="region"]');
		var is_texture = $(element).is('[data-editor="texture"]');
		var is_tmp = $(element).is('.ceditor-range');
		var tagName = $(element).get(0).nodeName.toLowerCase();
		
		if(is_texture){
			return $(element).clone().get(0);
		}
		
		if(element.childNodes.length > 0){
			
			var newNode = $(element).clone().empty();
			var clean;
			
			if(!is_region){
				pnames.push(newNode.get(0).nodeName.toLowerCase());
			}
			
			$.each(element.childNodes, function(i, node){
				clean = Editor.dom.clean(node, pnames);
				if(clean === false){
					return true;
				}
				
				if(newNode.get(0).lastChild && newNode.get(0).lastChild.nodeName.toLowerCase() != '#text'){
					if(clean.nodeName != undefined && newNode.get(0).lastChild.nodeName.toLowerCase() == clean.nodeName.toLowerCase()){
						if(Editor.settings.elements.empty.indexOf(clean.nodeName.toLowerCase()) == -1){
							if(clean.nodeName.toLowerCase() == 'span'){
								if(Editor.dom.equal(newNode.get(0).lastChild, clean)){
									$(newNode.get(0).lastChild).append(clean.childNodes);
								}else{
									newNode.append(clean);
								}
							}else{
								if(Editor.settings.elements.join.indexOf(clean.nodeName.toLowerCase()) > -1){
									$(newNode.get(0).lastChild).append(clean.childNodes);
								}else{
									newNode.append(clean);
								}
							}
						}else{
							newNode.append(clean);
						}
					}else{
						newNode.append(clean);
					}
				}else{
					newNode.append(clean);
				}
			});
			
			if(!is_region){
				pnames.pop();
			}
			
			Editor.dom.normalize(newNode.get(0));//.normalize();
			
			if(newNode.attr('style') != undefined && newNode.attr('style').length == 0){
				newNode.removeAttr('style');
			}
			
			if(!is_region && !is_tmp && newNode.get(0).childNodes.length == 1){
				if(newNode.get(0).firstChild.nodeName.toLowerCase() == '#text' && newNode.get(0).firstChild.nodeValue.trim().length == 0){
					return false;
				}
				
				if(newNode.contents().first().is('.ceditor-range')){
					if(Editor.settings.elements.blocks.indexOf(newNode.get(0).nodeName.toLowerCase()) > -1){
						var empty = document.createTextNode('\u200B');
						newNode.prepend(empty);
					}else{
						return newNode.contents().first();
					}
				}
			}
			
			if(newNode.get(0).childNodes.length == 0 && !is_region && !is_tmp){
				return false;
			}
			
			if(pnames.indexOf(newNode.get(0).nodeName.toLowerCase()) != -1){
				return newNode.get(0).childNodes;
			}else if(newNode.get(0).nodeName.toLowerCase() != 'div'){
				var invalid = false;
				
				var parentWeight = Editor.weight.get(newNode.get(0).nodeName.toLowerCase());
				
				$.each(newNode.contents(), function(i, child){
					var childWeight = Editor.weight.get(child.nodeName.toLowerCase());
					
					if(childWeight !== false && parentWeight !== false && childWeight > parentWeight){
						invalid = true;
						
						return false; //break
					}
				});
				
				if(invalid){
					var tempNode = $('<del>');
					
					$.each(newNode.contents(), function(i, child){
						var newChild;
						var newNodeClone = newNode.clone().empty();
						
						var childWeight = Editor.weight.get(child.nodeName.toLowerCase());
						//if(child.nodeName.toLowerCase() == 'pre'){
						if(childWeight !== false && parentWeight !== false && childWeight > parentWeight){
							newChild = $(child).clone().empty();
							//newChild.append(Editor.format(child, {'name' : newNode.get(0).nodeName.toLowerCase()}));
							newChild.append(newNodeClone.append($(child).contents()));
						}else{
							newChild = newNodeClone.append(child);
							//newChild = Editor.format(child, {'name' : newNode.get(0).nodeName.toLowerCase()});
						}
						tempNode.append(newChild);
						//$(child).replaceWith(Editor.format(child, {'name' : newNode.get(0).nodeName.toLowerCase()}));
					});
					//return newNode.contents().get();
					tempNode = $(Editor.dom.clean(tempNode.get(0)));
					return tempNode.contents().get();
				}else{
					return newNode.get(0);
				}
			}else{
				//newNode.get(0).normalize();
				return newNode.get(0);
			}
		}else{
			if(tagName != '#text' && Editor.settings.elements.empty.indexOf(tagName) == -1 && !is_region && !is_tmp){
				var rnode = $(element).clone().get(0);
				Editor.dom.normalize(rnode);
				if($(rnode).contents().length > 0){
					return rnode;
				}else{
					return false;
				}
			}else{
				if(tagName == '#text' && element.length == 0){
					return false;
				}else{
					var rnode = $(element).clone().get(0);
					Editor.dom.normalize(rnode);//.normalize();
					return rnode;
				}
			}
		}
	}
	
	Editor.dom.normalize = function(node){
		node.normalize();
		
		$.each($(node).contents(), function(k, child){
			if(child.nodeName.toLowerCase() == '#text'){
				if(child.nodeValue && Editor.const.zwspRegex.test(child.nodeValue)){
					//child.nodeValue = child.nodeValue.replace(/(\u200B)+/g, '\u200B');
					child.nodeValue = child.nodeValue.replace(/(\u200B)+/g, '');
					
					if(child.previousSibling){
						if(child.previousSibling.nodeName.toLowerCase() == 'br'){
							child.nodeValue = '\u200B' + child.nodeValue;
						}
					}
					
					if(child.length == 0){
						//check that it is not the last node in a block
						if($(node).is(Editor.settings.elements.blocks.toString()) || $(node).is('.ceditor-range')){
							if($(node).contents().length == 1 || ($(node).contents().length == 2 && $(node).find('.ceditor-range').length)){
								child.nodeValue = '\u200B';
							}else{
								$(child).remove();
							}
						}
					}
				}
				
				//check ending space
				/*if(child.length){
					var last_char = child.nodeValue.substr(child.length - 1, 1);
					if(last_char == '\u0020'){
						child.nodeValue = child.nodeValue.substr(0, child.length - 1) + '\u00A0';
					}
				}*/
				//just replace any space with &nbsp; because space causes a problem when backspace is used
				//child.nodeValue = child.nodeValue.replace(/(\u0020)/g, '\u00A0');
				
			}else if(child.nodeName.toLowerCase() == 'br'){
				//remove any br without zwsp after it
				if(child.nextSibling && child.nextSibling.nodeName.toLowerCase() == '#text' && child.nextSibling.nodeValue.indexOf('\u200B') == 0){
					
				}else{
					//$(child).remove();
					//add a zwsp after the br to fix the problem when html is loaded into the region with brs
					var empty = document.createTextNode('\u200B');
					$(child).after(empty);
				}
			}
		});
		/*
		//moved to initialize
		if($(node).is(Editor.settings.elements.blocks.toString()) && $(node).contents().length == 0){
			//empty block, add zwsp
			var empty = document.createTextNode('\u200B');
			$(node).append(empty);
		}
		*/
		return node;
	}
	
	Editor.dom.format = function(node, tag){
		var newNode = document.createElement(tag.name);
		var child;
		
		if(tag.attrs != undefined){
			$.each(tag.attrs, function(name, value){
				$(newNode).attr(name, value);
			});
		}
		
		while(child = node.firstChild){
			newNode.appendChild(child);
		}
		
		if(tag.css != undefined){
			$.each(tag.css, function(name, value){
				$(newNode).css(name, value);
			});
		}
		
		return newNode;
	}
	
	Editor.dom.unformat = function(node, tag, range){
		var tagRange = document.createRange();
		tagRange.selectNodeContents(node);
		
		var tipRange = tagRange.cloneRange();
		tipRange.setEnd(range.startContainer, range.startOffset);
		
		var tailRange = tagRange.cloneRange();
		tailRange.setStart(range.endContainer, range.endOffset);
		
		var newNode = document.createElement(tag.name);
		
		if(tipRange.collapsed === false){
			$(newNode).append(this.format(tipRange.cloneContents(), tag));
		}
		
		var modifiedNodes = $(range.cloneContents()).contents();
		
		if(!node.isSameNode(range.commonAncestorContainer) && $(range.commonAncestorContainer).closest(node).length > 0){
			//get the parents list up to the target tag including the commonAncestorContainer
			if(range.commonAncestorContainer.nodeName.toLowerCase() != '#text'){
				var parents = $(range.commonAncestorContainer).contents().first().parentsUntil(node);
			}else{
				var parents = $(range.commonAncestorContainer).parentsUntil(node);
			}
			
			if(parents.length){
				$.each(parents, function(i, parent){
					var clone = $(parent).clone().empty();
					modifiedNodes = clone.append(modifiedNodes);
				});
			}
		}
		$(newNode).append(modifiedNodes);
		
		if(tailRange.collapsed === false){
			$(newNode).append(this.format(tailRange.cloneContents(), tag));
		}
		/*
		var parts = [];
		
		$.each($(newNode).contents(), function(i, inode){
			parts.push(inode);
		});
		*/
		return $(newNode).contents().get();
	}
	
	Editor.range.format = function(tag, range){
		if(range == undefined){
			range = this.get();
		}
		
		var newNode = Editor.dom.format(range.cloneContents(), tag);
		
		Editor.range.replaceWith(newNode, range);
		
		if(Editor.settings.attrs.tags[tag.name] != undefined){
			$(newNode).attr(Editor.settings.attrs.tags[tag.name]);
		}
		//update range
		range.selectNodeContents(newNode);//selecting 2 p causing a tag weight problem with bravo
		//range.selectNode(newNode);
		
		return newNode;
	}
	
	Editor.range.unformat = function(node, tag, range){
		if(range == undefined){
			range = this.get();
		}
		
		newNodes = Editor.dom.unformat(node, tag, range);
		
		range.selectNode(node);
		Editor.range.replaceWith(newNodes.reverse(), range);
		
		//update range
		newNodes.reverse();
		//console.log(newNodes);
		var set = false;
		$.each(newNodes, function(i, n){
			//tmpRange.selectNode(n);
			/*if(Editor.range.hasTag(tag, tmpRange)){
				range.selectNodeContents(n);
			}*/
			if(!set && n.nodeName.toLowerCase() != tag.name){
				//tmpRange.selectNodeContents(n);
				//tmpRange.setStartBefore(n);
				range.selectNode(n);
				set = true;
			}else if(set && n.nodeName.toLowerCase() != tag.name){
				//var tmpRange2 = document.createRange();
				//tmpRange2.selectNodeContents(n);
				//tmpRange.setEnd(tmpRange2.endContainer, tmpRange2.endOffset);
				range.setEndAfter(n);
				set = true;
			}
		});
		//if no matching nodes found
		if(!set){
			//console.log(newNodes);
			range.setStartBefore(newNodes[0]);
			range.setEndAfter(newNodes[newNodes.length - 1]);
		}//return;
		//check if single node in the range
		//if($(tmpRange.cloneRange().cloneContents()).contents().length == 1){
			//tmpRange.selectNodeContents($(tmpRange.cloneContents()).contents().first().get(0));
		//}
		
		return newNodes;
	}
	
	Editor.app = {};
	Editor.app.apply = function (task, tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		if(task == 'texture'){
			Editor.tasks.texture[tag.name](tag, range);
		}else{
			Editor.tasks[task](tag, range);
		}
	}
	
	Editor.tasks = {};
	Editor.tasks.format = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		//console.log(range);
		if(parent = Editor.range.hasTag(tag, range)){
			Editor.range.unformat(parent, tag, range);
		}else{
			Editor.range.format(tag, range);
		}
		
		Editor.region.clean(range);
	}
	Editor.tasks.insert = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		var node = document.createElement(tag.name);
		
		if(tag.attrs){
			$.each(tag.attrs, function(atname, atvalue){
				$(node).attr(atname, atvalue);
			});
		}
		
		range.insertNode(node);
		
		range.setStartAfter(node);
		range.setEndAfter(node);
		
		Editor.region.clean(range);
	}
	
	Editor.tasks.undo = function(tag, range){
		var region = Editor.region.get();
		
		var images = Editor.history.get(region);
		
		if(images){
			var image = images.pop();
			
			$(region).html(image);
			
			if(images.length == 0){
				images.push(image);
			}
			
			Editor.history.set(region, images);
			
			if(range != undefined){
				Editor.range.restore(region, range);
				
				Editor.region.focus();
				
				Editor.range.set(range);
			}
		}
	}
	
	Editor.dom.recycle = function(element, range){
		var tagRange = document.createRange();
		tagRange.selectNodeContents(element);
		
		var tipRange = tagRange.cloneRange();
		tipRange.setEnd(range.startContainer, range.startOffset);
		
		var tailRange = tagRange.cloneRange();
		tailRange.setStart(range.endContainer, range.endOffset);
		
		//var newContent = $('<div>').append($(tipRange.cloneContents())).html();
		
		var newNode = $('<div>');
		
		$(newNode).append($(tipRange.cloneContents()));
		
		var clearText = document.createTextNode($(range.cloneContents()).text());
		$(newNode).append(clearText);
		
		$(newNode).append($(tailRange.cloneContents()));
		
		$(element).empty();
		$.each($(newNode).contents(), function(k, node){
			$(element).append(node);
		});
		
		range.selectNode(clearText);
	}
	
	Editor.tasks.recycle = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		var sblock = Editor.dom.block(range.startContainer);
		var eblock = Editor.dom.block(range.endContainer);
		var srange = range.cloneRange();
		
		if(sblock){
			Editor.range.store(range);
			
			if(!sblock.isSameNode(eblock)){
				range.setEndAfter($(sblock).contents().last().get(0));
				Editor.dom.recycle(sblock, range);
				
				while(sblock.nextSibling && !sblock.nextSibling.isSameNode(eblock)){
					sblock = sblock.nextSibling;
					
					range.selectNodeContents(sblock);
					Editor.dom.recycle(sblock, range);
				}
				
				range.selectNodeContents(eblock);
				range.setEnd(srange.endContainer, srange.endOffset);
				Editor.dom.recycle(eblock, range);
			}else{
				Editor.dom.recycle(sblock, range);
			}
			
			Editor.range.restore(Editor.region.get(), range);
		}
		/*
		if(block){
			Editor.dom.recycle(block, range);
		}else{
			Editor.dom.recycle(Editor.region.get(), range);
		}
		*/
		Editor.region.clean(range);
	}
	
	Editor.tasks.block = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		if(parent = Editor.range.hasTag(tag, range)){
			if(Editor.settings.mode == 'html'){
				var newBlock = Editor.dom.changeTag({'name':Editor.settings.text_block}, parent);
				
				range.selectNodeContents(newBlock);
				range.collapse(false);
			}else{
				range.selectNodeContents(parent);
				Editor.range.unformat(parent, tag, range);
			}
		}else{
			if(block = Editor.dom.block()){
				Editor.range.store(range);
				if(block.nodeName.toLowerCase() == 'li'){
					var ul = $(block).parent('ul, ol').first().get(0);
					range.selectNode(block);
					range.collapse(true);
					var blockClone = $(block).clone().get(0);
					$(block).remove();
					
					var newNodes = Editor.dom.split(ul, range);
					$(newNodes[0]).after(blockClone);
					
					var newBlock = Editor.dom.changeTag(tag, blockClone);
				}else{
					var newBlock = Editor.dom.changeTag(tag, block);
				}
				//range.selectNodeContents(newBlock);
				Editor.range.restore(newBlock, range);
			}else{
				Editor.range.format(tag, range);
			}
		}
		
		Editor.region.clean(range);
	}
	
	Editor.tasks.list = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		if(ul = Editor.range.hasTag(tag, range)){
			if((li = Editor.range.hasTag({'name':'li'}, range))){
				range.selectNode(li);
				Editor.range.unformat(ul, tag, range);
				if(li = Editor.range.hasTag({'name':'li'}, range)){
					var p = Editor.dom.changeTag({'name':Editor.settings.text_block}, li);
					range.selectNodeContents(p);
					range.collapse(false);
				}
			}else{
				return false;
				var found = false;
				var fli = $(range.startContainer).closest('li').get(0);
				var eli = $(range.endContainer).closest('li').get(0);
				//var lis = [fli];
				//console.log(fli);
				//console.log(eli);
				range.setStartBefore(fli);
				range.setEndAfter(eli);
				Editor.range.unformat(ul, tag, range);
				//console.log(range);
				//console.log(Editor.range.nodes(range));
				/*
				while(fli.nextSibling && !fli.nextSibling.isSameNode(eli)){
					fli = fli.nextSibling;
					lis.push(fli);
				}
				lis.push(fli.nextSibling);
				
				//console.log(lis);
				
				$.each(lis, function(k, li){
					range.selectNode(li);
					Editor.range.unformat(ul, tag, range);
					if(li = Editor.range.hasTag({'name':'li'}, range)){
						var p = Editor.dom.changeTag({'name':'p'}, li);
						range.selectNodeContents(p);
					}
				});
				*/
			}
		}else{
			block = Editor.dom.block();
			//Editor.range.store(range);
			var ul = Editor.dom.changeTag(tag, block);
			range.selectNodeContents(ul);
			//Editor.range.restore(ul, range);
			var li = Editor.range.format({'name':'li'}, range);
			
			range.selectNodeContents(li);
			range.collapse(false);
		}
		
		Editor.region.clean(range);
	}
	
	Editor.tasks.blockstyle = function(attrs, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		if(block = Editor.dom.block()){
			if(Editor.range.has.blockstyle(attrs, range)){
				$.each(attrs, function(name, value){
					$(block).css(name, '');
				});
			}else{
				$.each(attrs, function(name, value){
					$(block).css(name, value);
				});
			}
		}
		
		Editor.region.clean(range);
	}
	
	Editor.tasks.inlinestyle = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		if(parent = Editor.range.hasTag(tag, range)){
			Editor.range.unformat(parent, tag, range);
		}else{
			//check other inline style
			var vTag = {'name':'span'};
			if(parent = Editor.range.hasTag(vTag, range)){
				vTag.css = $(parent).css(['color']);
				
				Editor.range.unformat(parent, vTag, range);
				
				Editor.range.format(tag, range);
			}else{
				Editor.range.format(tag, range);
			}
		}
		
		Editor.region.clean(range);
	}
	
	Editor.tasks.link = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		parent = Editor.range.hasTag(tag, range);
		if(parent){
			range.selectNodeContents(parent);
			Editor.range.unformat(parent, tag, range);
		}
		
		if(tag.attrs.href != undefined && tag.attrs.href.trim().length > 0){
			Editor.range.format(tag, range);
		}
		
		Editor.region.clean(range);
	}
	
	Editor.tasks.image = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		if(tag.attrs.src != undefined && tag.attrs.src.trim().length > 0){
			var img = $('<img>').attr('src', tag.attrs.src).get(0);
			
			if(tag.attrs['data-output'].trim().length > 0){
				$(img).attr('data-output', tag.attrs['data-output']);
			}
			
			if(Editor.settings.attrs.tags.img){
				$(img).attr(Editor.settings.attrs.tags.img);
			}
			
			Editor.range.replaceWith(img, range);
		}
		
		Editor.region.clean(range);
	}
	
	Editor.tasks.texture = {};
	Editor.tasks.texture.file = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		if(block = Editor.dom.block()){
			range.setStartAfter(block);
			range.setEndAfter(block);
		}
		
		if(tag.attrs.href != undefined && tag.attrs.href.trim().length > 0){
			//var file = $('<div class="ui header small" data-editor="texture" data-name="file"><i class="file icon"></i><a class="content"></a></div>');
			var file = $('<div class="ui icon message mini"><i class="file icon"></i><div class="content"><a target="_blank" rel="nofollow" class="header"></a></div></div>').get(0);
			$(file).find('a').attr('href', tag.attrs.href);
			$(file).find('a').text(tag.attrs.title ? tag.attrs.title : tag.attrs.href);
			
			if(tag.attrs['data-output'].trim().length > 0){
				$(file).attr('data-output', tag.attrs['data-output']);
			}else{
				$(file).attr('data-output', '[file='+tag.attrs.href+']'+$(file).find('a').text()+'[/file]');
			}
			
			file = Editor.dom.texturize(file, 'file');
			
			Editor.range.replaceWith(file, range);
		}
		
		Editor.region.clean(range);
	}
	
	Editor.tasks.texture.video = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		if(block = Editor.dom.block()){
			range.setStartAfter(block);
			range.setEndAfter(block);
		}
		
		if(tag.attrs.youtube != undefined && tag.attrs.youtube.trim().length > 0){
			var video = $('<div class="ui embed" data-output="[youtube]'+ tag.attrs.youtube +'[/youtube]" data-source="youtube" data-id="' + tag.attrs.youtube + '"></div>');
			
			video = Editor.dom.texturize(video, 'video');
			
			Editor.range.replaceWith(video, range);
			
			if(jQuery.fn.embed != undefined){
				$(Editor.region.get()).find('.ui.embed').embed();
			}
		}
		
		Editor.region.clean(range);
	}
	
	Editor.tasks.texture.media = function(tag, range){
		if(range == undefined){
			range = Editor.range.get();
		}
		
		if(block = Editor.dom.block()){
			range.setStartAfter(block);
			range.setEndAfter(block);
		}
		
		if(tag.attrs.url != undefined && tag.attrs.url.trim().length > 0){
			var media = $('<div class="ui embed" data-output="[media]'+ tag.attrs.url +'[/media]" data-url="' + tag.attrs.url + '"></div>');
			
			media = Editor.dom.texturize(media, 'media');
			
			Editor.range.replaceWith(media, range);
			
			if(jQuery.fn.embed != undefined){
				$(Editor.region.get()).find('.ui.embed').embed();
			}
		}
		
		Editor.region.clean(range);
	}
	
	Editor.dom.texturize = function(node, name){
		$(node).attr('data-editor', 'texture');
		$(node).attr('contenteditable', 'false');
		
		if(name != undefined){
			$(node).attr('data-name', name);
		}
		
		return node;
	}
	
	Editor.output = {};
	Editor.output.bbcode = function(output){
		var bboutput = $('<div>').html(output);
		
		$.each(bboutput.find('[data-output]'), function(k, tag){
			$(tag).replaceWith($(tag).attr('data-output'));
		});
		
		$.each(bboutput.find('img'), function(k, tag){
			$(tag).replaceWith('[img]'+$(tag).attr('src')+'[/img]');
		});
		/*
		$.each(bboutput.find('[data-editor="texture"]'), function(k, tag){console.log(tag);
			var name = $(tag).data('name');
			var href = $(tag).find('a').first().attr('href');
			var title = $(tag).find('a').first().text();
			$(tag).replaceWith('['+name+'='+href+']'+title+'[/'+name+']');
		});
		*/
		$.each(bboutput.find('a'), function(k, tag){
			$(tag).replaceWith('[url='+$(tag).attr('href')+']'+$(tag).html()+'[/url]');
		});
		
		$.each(bboutput.find('span'), function(k, tag){
			if($(tag).css('color')){
				$(tag).replaceWith('[color='+$(tag).css('color')+']'+$(tag).html()+'[/color]');
			}
			if($(tag).css('font-size')){
				$(tag).replaceWith('[size='+$(tag).css('font-size').replace(/%|px|pt/g, '')+']'+$(tag).html()+'[/size]');
			}
		});
		
		$.each(bboutput.find('.private'), function(k, tag){
			$(tag).replaceWith('[private]'+$(tag).html()+'[/private]');
		});
		
		bboutput.find('*').removeAttr('class').removeAttr('style');
		
		output = $(bboutput).html();
		
		output = output.replace(/</g, '[');
		output = output.replace(/>/g, ']');
		
		return output;
	}
	
	function removePrev(){
		var range = Editor.range.get();
		
		if(range.collapsed === false){
			var con = range.extractContents();
			console.log(con);
			return true;
		}
		
		var node = range.startContainer;
		var findSib = false;
		if(node.nodeName.toLowerCase() == '#text'){
			if(range.startOffset == 0){
				findSib = true;
			}else{
				return true;
			}
		}else{
			findSib = true;
		}
		
		if(findSib){
			if((range.startOffset == node.childNodes.length) && node.lastChild){
				//var pos = (node.lastChild.nodeName.toLowerCase() == '#text') ? node.lastChild.length : node.lastChild.childNodes.length;
				
				if(node.lastChild.nodeName.toLowerCase() == '#text'){
					range.setStart(node.lastChild, node.lastChild.length);
					range.setEnd(node.lastChild, node.lastChild.length);
				}else{
					if(node.lastChild.childNodes.length){
						range.setStart(node.lastChild, node.lastChild.childNodes.length);
						range.setEnd(node.lastChild, node.lastChild.childNodes.length);
					}else{
						range.selectNode(node.lastChild);
						//$(node.lastChild).remove();
					}
				}
			}else{
				if(node.previousSibling){
					//var pos = (node.previousSibling.nodeName.toLowerCase() == '#text') ? node.previousSibling.length : node.previousSibling.childNodes.length;
					
					if(node.previousSibling.nodeName.toLowerCase() == '#text'){
						range.setStart(node.previousSibling, node.previousSibling.length);
						range.setEnd(node.previousSibling, node.previousSibling.length);
					}else{
						if(node.previousSibling.childNodes.length){
							range.setStart(node.previousSibling, node.previousSibling.childNodes.length);
							range.setEnd(node.previousSibling, node.previousSibling.childNodes.length);
						}else{
							range.selectNode(node.previousSibling);
							//$(node.previousSibling).remove();
						}
					}
					node.normalize();
					console.log(node.outerHTML);
				}else{
					if($(node).is('[data-editor="region"]')){
						return false;
					}
					//console.log(node.childNodes);
					range.setStartBefore(node);
					range.setEndBefore(node);
					
					node.normalize();
					console.log(node.outerHTML);
					if(node.childNodes.length == 0){
						//$(node).remove();
					}
				}
			}
		}
		console.log(range);
		removePrev();
	}
	
}(jQuery));