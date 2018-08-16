(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	
	$.G2.actions = {};
	$.G2.actions.list = {};
	
	$.G2.actions.include = function(items){
		$.each(items, function(k, item){
			$.G2.actions.list[k] = item;
		});
	};
	
	$.G2.actions.ready = function(Elem){
		if(typeof Elem == 'undefined'){
			Elem = $('body');
		}
		
		Elem.find('.G2-static').each(function(k, element){
			$.G2.actions.statics(element);
		});
		
		Elem.find('.G2-dynamic').each(function(k, element){
			$.G2.actions.dynamics(element);
		});
		
	};
	
	$.G2.actions.statics = function(element){
		var id = $(element).data('id');
		
		$(element).off('click.static');
		$(element).on('click.static', function(e){
			e.preventDefault();
			if($.G2.actions.list.hasOwnProperty(id) && $.G2.actions.list[id].hasOwnProperty('click')){
				var result = $.G2.actions.list[id].click($(element), e);
				if(result == false){
					return ret;
				}
			}
		});
		
		if($(element).data('task')){
			
			var targets = $.G2.actions.getTarget(element, $(element).data('task'));
			target_element = targets[1];
			element_task = targets[0];
			$(element).data('target', target_element);
			
			if(element_task == 'popup'){
				
				popup_element = target_element;
				
				if(popup_element == null){
					$(element).after($('<div class="ui fluid popup top left transition hidden G2-static-popup"><div class="ui active inline centered loader"></div></div>'));
					popup_element = $(element).next('.popup').first();
				}
				
				if(popup_element != null){
					$(element).popup({
						//inline: true, 
						position: (typeof popup_element.data('position') == 'undefined') ? 'top right' : popup_element.data('position'),
						popup: popup_element,
						on : 'click',
						closable: true,
						exclusive: true,
						preserve: true,
						lastResort: true,
						onHidden: function(){
							//$('body').off('click.staticpopup');
						},
						onVisible: function(){
							/*$('body').on('click.staticpopup', function(_e){
								if(_e.target !== $(element).next('.popup')[0] && !$.contains($(element).next('.popup')[0], _e.target)){
									if($(element).next('.popup').hasClass('visible')){
										$(element).popup('show');
										$(element).popup('hide');
									}
								}
							});*/
						}
					});
					/*$(element).on('click', function(){
						$(element).popup('show');
					});*/
				}
				
			}else if(element_task == 'modal'){
				
				modal_element = target_element;
				$(element).on('click', function(){
					$(element).next('.ui.modal').modal({'detachable' : false, 'inverted' : true}).modal('show');
				});
			}else if(element_task == 'scroll'){
				
				$(element).off('click').on('click', function(e){
					e.preventDefault();
					target_element = $(element).data('target');
					
					if(target_element != null){
						$.G2.scrollTo(target_element);
					}
				});
			}else if(element_task == 'hide'){
				
				$(element).off('click').on('click', function(e){
					e.preventDefault();
					target_element = $(element).data('target');
					
					target_element.removeClass('visible').addClass('hidden');
				});
			}else if(element_task == 'remove'){
				
				$(element).off('click').on('click', function(e){
					e.preventDefault();
					target_element = $(element).data('target');
					
					target_element.remove();
					$(element).trigger('g2.actions.static.remove');
				});
			}else if(element_task == 'clone'){
				
				$(element).off('click').on('click', function(e){
					e.preventDefault();
					target_element = $(element).data('target');
					
					var clone = target_element.clone();
					var counter = target_element.data('counter') ? parseInt(target_element.data('counter')) : 0;
					counter = counter + 1;
					target_element.data('counter', counter);
					
					clone.html(clone.html().replace(/\[0\]/g, '[' + counter + ']').replace(/-0/g, counter));
					target_element.after(clone);
					
					clone.trigger('contentChange.basics');
				});
			}
		}
	};
	
	$.G2.actions.getTarget = function(element, string){
		
		var task_data = string.split(':');
		var task_data1 = task_data[0].split('/');
		
		if(task_data.length > 1){
			var task_data2 = task_data[1].split('/');
		}else{
			var task_data2 = '';
		}
		
		var target_element = null;
		
		if(task_data1[1] == 'self'){
			
			target_element = $(element);
		
		}else if(task_data1[1] == 'next'){
			
			if(task_data2[0] == undefined){
				target_element = $(element).next();
			}else{
				target_element = $(element).next().find(task_data2[0]);
			}
			
		}else if(task_data1[1] == 'closest' && task_data2[0] != undefined){
			
			target_element = $(element).closest(task_data2[0]);
			
		}else if(task_data1[1] == 'child'){
			
		}else if((task_data1[1] == 'find' || task_data1[1] == undefined) && task_data2[0] != undefined){
			
			target_element = $(task_data2[0]);
			
		}else if(task_data1[1] == undefined && task_data2[0] == undefined){
			target_element = $(element).next();
		}
		
		return [task_data1[0], target_element];
	};
	
	$.G2.actions.dynamics = function(element){
		var id = $(element).data('id');
		
		var Event = 'click'; 
		if($(element).prop('tagName') == 'FORM'){
			Event = 'submit';
			$(element).data('url', $(element).attr('action'));
		}
		
		if($(element).data('url') == undefined && $(element).attr('href')){
			$(element).data('url', $(element).attr('href'));
		}
		/*
		if($(element).is('.ui.dropdown')){
			$(element).data('url', $(element).find('select').first().data('url'));
		}
		*/
		$(element).off(Event + '.dynamic');
		$(element).on(Event + '.dynamic', function(e){
			
			e.preventDefault();
			
			$.G2.actions.dynamics.run(element);
		});
	};
	
	$.G2.actions.dynamics.run = function(element){
		var id = $(element).data('id');
		
		var counter = $(element).data('counter') ? parseInt($(element).data('counter')) : 0;
		//counter = counter + 1;
		//$(element).data('counter', counter);
		
		var counterParam = $(element).attr('name') ? '&' + $(element).attr('name') + '[counter]=' + counter : '';
		counterParam = counterParam + '&_counter=' + counter;
		//update the counter for the next run
		counter = counter + 1;
		$(element).data('counter', counter);
		
		if($(element).data('once') && $(element).data('called')){
			return false;
		}
		
		//trigger element completion event
		var beforeStartValue = $(element).triggerHandler('g2.actions.dynamic.beforeStart');
		if(beforeStartValue === false || $(element).data('beforeStart') === false){
			return false;
		}
		
		if($(element).data('url')){
			var requestData = {};
			var content = false;//'application/x-www-form-urlencoded; charset=UTF-8';
			var container = '';
			var buildData = false;
			
			if($(element).data('dtask')){
				
				var targets = $.G2.actions.getTarget(element, $(element).data('dtask'));
				target_element = targets[1];
				element_task = targets[0];
				
				if(element_task == 'send'){
					
					if(target_element != null){
						buildData = true;
						container = target_element;
					}
					
				}
			}
			
			//check triggers
			if($(element).data('dynamic') && $(element).data('dynamic')['onstart']){
				$.each($(element).data('dynamic')['onstart'], function(dact, dactdata){
					if(dact == 'send'){
						buildData = true;
						$.each(dactdata, function(k, dactid){
							container = $(dactid);
						});
					}
				});
			}
			
			if(buildData){
				//add text data
				requestData = new FormData();
				$.each(container.find(':input').serializeArray(), function(key, input){
					requestData.append(input.name, input.value);
				});
				//add files data
				container.find('input[type="file"]').each(function(key, input){
					requestData.append($(input).attr('name'), $(input)[0].files[0]);
				});
			}
			
			$.ajax({
				xhr: function(){
					var xhr = new window.XMLHttpRequest();
					
					if(container && jQuery.fn.progress != undefined){
						//container.find('.progress').show();
						xhr.upload.addEventListener('progress', function(evt){
							if(evt.lengthComputable){
								var percentComplete = evt.loaded / evt.total;
								percentComplete = parseInt(percentComplete * 100);
								
								container.find('.progress').progress('set percent', percentComplete);
								
								if(percentComplete === 100){
									//container.find('.progress').hide();
								}
							}
						}, false);
					}
					return xhr;
				},
				url: $(element).data('url') + counterParam,
				type: "POST",
				data: requestData,
				processData: false,
				contentType: content,
				beforeSend: function(){
					$(element).addClass('loading');
					if(container && container.hasClass('loading') == false){
						container.append('<div class="ui active inverted dimmer"><div class="ui text loader"></div></div>');
					}
				},
				error: function(xhr, textStatus, message){
					$(element).removeClass('loading');
					if(container){
						container.children('.ui.active.dimmer').remove();
					}
					
					if(jQuery.fn.popup != undefined){
						$(element).popup({html : '<div class="ui message error small">'+textStatus+':'+message+'</div>'});
						$(element).popup('show');
					}else{
						alert(textStatus+':'+message);
					}
				},
				success: function(data, textStatus, xhr){
					$(element).removeClass('loading');
					//$(element).popup('destroy');
					
					if(container){
						container.find('.ui.dimmer').remove();
					}
					var is_json = false;
					//check response data type
					if(data.substring(0, 1) == '{' && data.slice(-1) == '}'){
						var is_json = true;
						
						var json = JSON.parse(data);
						
						if(json.hasOwnProperty('error') && json.error != 0){
							if(jQuery.fn.popup != undefined){
								$(element).popup({html : '<div class="ui message error small">'+json.error+'</div>'});
								$(element).popup('show');
							}else{
								alert(json.error);
							}
						}else{
							json.error = 0;
						}
						
						data = json;
					}
					
					var newContent = '';
					
					if($(element).data('result') && (is_json == false || (is_json == true && data.error == 0))){
						
						if(is_json == false){
							var newContent = $(data);
						}
						
						var targets = $.G2.actions.getTarget(element, $(element).data('result'));
						target_element = targets[1];
						element_task = targets[0];
						
						if(element_task == 'replace'){
							
							if(target_element != null){
								target_element.replaceWith(newContent);
							}
						}else if(element_task == 'after'){
							
							if(target_element != null){
								target_element.after(newContent);
							}
						}else if(element_task == 'before'){
							
							if(target_element != null){
								target_element.before(newContent);
							}
						}else if(element_task == 'html'){
							
							if(target_element != null){
								target_element.html(newContent);
							}
						}else if(element_task == 'text'){
							
							if(target_element != null){
								target_element.text(data);
							}
						}else if(element_task == 'append'){
							
							if(target_element != null){
								target_element.append(newContent);
							}
						}else if(element_task == 'prepend'){
							
							if(target_element != null){
								target_element.prepend(newContent);
							}
						}else if(element_task == 'remove'){
							
							if(target_element != null){
								if(target_element.prop('tagName') == 'TR'){
									target_element.remove();
								}else{
									target_element.transition({
										'animation' : 'fly right', 
										'onComplete' : function(){
											target_element.remove();
										}
									});
								}
							}
						}
						
					}
					
					//process click events for the element
					/*if($.G2.actions.list.hasOwnProperty(id) && $.G2.actions.list[id].hasOwnProperty('success')){
						$.G2.actions.list[id].success($(element), data, is_json, newContent);
					}*/
					
					//check triggers
					if($(element).data('dynamic') && $(element).data('dynamic')['oncomplete']){
						//$.each($(element).data('dynamic'), function(devent, deventdata){
							$.each($(element).data('dynamic')['oncomplete'], function(dact, dactdata){
								if(dact == 'reload'){
									$.each(dactdata, function(k, dactid){
										$(dactid).addClass('loading');
										$.ajax({
											url: $(dactid).data('url'),
											success: function(result){
												var newContent = $(result);
												$(dactid).replaceWith(newContent);
												newContent.trigger('contentChange');
											}
										});
									});
								}else if(dact == 'show'){
									$.each(dactdata, function(k, dactid){
										$(dactid).removeClass('hidden');
										$(dactid).show();
									});
								}else if(dact == 'hide'){
									$.each(dactdata, function(k, dactid){
										$(dactid).addClass('hidden');
										$(dactid).hide();
									});
								}
							});
						//});
					}
					//trigger element completion event
					$(element).trigger('g2.actions.dynamic.complete', [data, is_json, newContent]);
					//set called status
					if($(element).data('once')){
						$(element).data('called', true);
					}
					
					if(is_json == false && newContent){
						newContent.trigger('contentChange');
					}
					
				}
			});
		}
	};
	
}(jQuery));