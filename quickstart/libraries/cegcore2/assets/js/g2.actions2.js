(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	
	$.G2.actions2 = {};
	
	$.G2.actions2.ready = function(Elem){
		if(typeof Elem == 'undefined'){
			Elem = $('body');
		}
		
		Elem.find('.G2-dynamic2, [data-class="G2-dynamic2"]').addBack().each(function(k, element){
			if($(element).data('dynamic2ready') !== true){
				$.G2.actions2.dynamics(element);
				$(element).data('dynamic2ready', true);
			}
		});
	};
	
	$.G2.actions2.get = function(selector, element){
		var target = null;
		
		if(typeof selector === 'object'){
			//target = selector['id'];
			
			if(selector['fn'] == 'closest'){
				target = $(element).closest(selector['id']);
			}else if(selector['fn'] == 'next'){
				target = $(element).next(selector['id']);
			}else if(selector['id'] != undefined){
				target = $(selector['id']).first();
			}else{
				target = $(element);
			}
			
			if(selector['find'] == 'last'){
				target = target.children().last();
			}else if(selector['find'] != undefined){
				target = target.find(selector['find']).first();
			}
		}else if(selector == undefined){
			target = $(element);
		}else{
			target = $(selector);
		}
		
		return target;
	};
	
	$.G2.actions2.fns = function(element, actData, params){
		var act = actData['act'];
		if(act == 'modal'){
			var $modal = $.G2.actions2.get(actData['id'], element);//.last();
			if(actData['fn'] == undefined){
				if($modal.hasClass('dynamic') && actData['dynamic'] !== false){
					$modal.children('.content').last().html('<div class="ui active inline centered loader"></div>');
				}
				$modal.modal({
					//'detachable' : false, 
					//'allowMultiple' : true,
					'inverted' : true,
					'onShow' : function(){
						if($(this).hasClass('source')){
							//$(this).children('.content').first().html($(this).children('.source').first().html());
						}
					}
				}).modal('show');
			}else{
				$modal.modal(actData['fn']);
			}
			return true;
		}else if(act == 'notifier'){
			var $notifier = $.G2.actions2.get(actData['id'], element);
			$notifier.css({
				'position':'fixed',
				'z-index':'999999',
				'max-width':'90%',
			});
			
			var position = actData['position'] ? actData['position'] : 'top right';
			var duration = actData['duration'] ? actData['duration'] : 5000;
			
			if(position.indexOf('top') > -1){
				$notifier.css('top', '20px');
			}else if(position.indexOf('middle') > -1){
				$notifier.css('top', '50%');
				$notifier.css('margin-top', - $notifier.outerHeight()/2);
			}else{
				$notifier.css('bottom', '20px');
			}
			
			if(position.indexOf('right') > -1){
				$notifier.css('right', '20px');
			}else if(position.indexOf('center') > -1){
				$notifier.css('left', '50%');
				$notifier.css('margin-left', - $notifier.outerWidth()/2);
			}else{
				$notifier.css('left', '20px');
			}
			
			$notifier.transition({
				'animation' : 'fade down', 
				'onComplete' : function(){
					setTimeout(function (){
						if($notifier.hasClass('visible')){
							$notifier.transition('fade down');
						}
					}, duration);
				}
			});
			
			$notifier.off('click').on('click', function(){
				$notifier.transition('fade down');
			});
			
			return true;
		}else if(act == 'html'){
			$.G2.actions2.get(actData['id'], element).html(actData['html']);
			return true;
		}else if(act == 'submit'){
			$.G2.actions2.get(actData['id'], element).submit();
			return true;
		}else if(act == 'validate'){
			if($.G2.actions2.get(actData['id'], element).form('is valid')){
				return true;
			}
		}else if(act == 'empty'){
			$.G2.actions2.get(actData['id'], element).empty();
			return true;
		}else if(act == 'class'){
			$.G2.actions2.get(actData['id'], element).addClass(actData['class']);
			return true;
		}else if(act == 'hide'){
			$.G2.actions2.get(actData['id'], element).hide();
			return true;
		}else if(act == 'show'){
			$.G2.actions2.get(actData['id'], element).show();
			return true;
		}else if(act == 'disable'){
			$.G2.actions2.get(actData['id'], element).attr('disabled', true);
			$.G2.actions2.get(actData['id'], element).addClass('disabled');
			return true;
		}else if(act == 'enable'){
			$.G2.actions2.get(actData['id'], element).removeAttr('disabled');
			$.G2.actions2.get(actData['id'], element).removeClass('disabled');
			return true;
		}else if(act == 'remove'){
			if(actData['animation'] === false){
				$.G2.actions2.get(actData['id'], element).remove();
			}else{
				$.G2.actions2.get(actData['id'], element).transition({
					'animation' : 'fly right', 
					'onComplete' : function(){
						$.G2.actions2.get(actData['id'], element).remove();
					}
				});
			}
			return true;
		}else if(act == 'scrollto'){
			$('html, body').animate({
				scrollTop: $.G2.actions2.get(actData['id'], element).offset().top - 50
			}, 'slow');
			return true;
		}else if(act == 'centeron'){
			$('html, body').animate({
				scrollTop: $.G2.actions2.get(actData['id'], element).offset().top - $(window).height()/2
			}, 'slow');
			return true;
		}else if(act == 'trigger'){
			$.G2.actions2.get(actData['id'], element).trigger(actData['event'], [params]);
			return true;
		}else if(act == 'reload'){
			if($(actData['id']).find('.ui.modal.visible').length > 0){
				//return false;
			}
			
			var counter = $(element).data('counter') ? parseInt($(element).data('counter')) : 0;
			counter = counter + 1;
			$(element).data('counter', counter);
			
			return $.G2.actions2.fns(element, {'act':'ajax', 'url':$.G2.actions2.get(actData['id'], element).data('url') + '&_counter=' + counter, 'form':actData['id'], 'result':{'id':actData['id'],'place':'replace'}});
		}else if(act == 'ajax'){
			//add text data
			requestData = new FormData();
			var formArea = (actData['form'] != undefined) ? $.G2.actions2.get(actData['form'], element) : false;
			if(formArea != false){
				$.each(formArea.find(':input').serializeArray(), function(key, input){
					requestData.append(input.name, input.value);
				});
				//add files data
				formArea.find('input[type="file"]').each(function(key, input){
					requestData.append($(input).attr('name'), $(input)[0].files[0]);
				});
			}
			//console.log(requestData);
			$.ajax({
				type: 'POST',
				url: actData['url'],
				//data: (actData['form'] != undefined) ? $.G2.actions2.get(actData['form'], element).find(':input').serializeArray() : {},
				data: requestData,
				processData: false,
				contentType: false,
				beforeSend: function(){
					$(element).addClass('loading');
					if(actData['form'] != undefined){
						$.G2.actions2.get(actData['form'], element).addClass('loading');
					}
					if(actData['before'] != undefined){
						$.G2.actions2.run(element, actData['before']);
					}
				},
				error: function(xhr, textStatus, message){
					$(element).removeClass('loading');
					if(actData['form'] != undefined){
						$.G2.actions2.get(actData['form'], element).removeClass('loading');
					}
					if(actData['result'] != undefined){
						$.G2.actions2.get(actData['result'], element).html('<div class="ui message red">'+textStatus+':'+message+'</div>');
					}
					if(actData['error'] != undefined){
						$.G2.actions2.run(element, actData['error']);
					}
				},
				success: function(result){
					var json = false;
					var resultContent = false;
					
					$(element).removeClass('loading');
					if(actData['form'] != undefined){
						$.G2.actions2.get(actData['form'], element).removeClass('loading');
					}
					if(result.substring(0, 1) == '{' && result.slice(-1) == '}'){
						var json = JSON.parse(result);
						
						var resultContent = '';
						
						if(json.error != undefined){
							var resultContent = $('<div class="ui message red">'+json.error+'</div>');
						}
						if(json.success != undefined){
							var resultContent = $('<div class="ui center aligned icon header green"><i class="circular checkmark icon green"></i>'+json.success+'</div>');
						}
						
						if(actData['result.json'] != undefined){
							$.G2.actions2.addContent($.G2.actions2.get(actData['result.json'], element), resultContent, actData['result.json']);
						}else if(actData['result'] != undefined){
							$.G2.actions2.addContent($.G2.actions2.get(actData['result'], element), resultContent, actData['result']);
						}
						
						if(json.state != undefined && actData['result.state'] != undefined){
							$.G2.actions2.get(actData['result.state'], element).hide();
							$.G2.actions2.get(actData['result.state'], element).filter('[data-state="'+json.state+'"]').show();
						}
						
						if(actData['success.json'] != undefined){
							$.G2.actions2.run(element, actData['success.json'], {'data' : result, 'json' : json, 'content' : resultContent});
						}
					}else{
						var resultContent = $(result);
						
						if(actData['result.content'] != undefined){
							$.G2.actions2.addContent($.G2.actions2.get(actData['result.content'], element), resultContent, actData['result.content']);
						}else if(actData['result'] != undefined){
							$.G2.actions2.addContent($.G2.actions2.get(actData['result'], element), resultContent, actData['result']);
						}
						
						resultContent.trigger('contentChange');
						
						if(actData['success.content'] != undefined){
							$.G2.actions2.run(element, actData['success.content'], {'data' : result, 'json' : json, 'content' : resultContent});
						}
					}
					
					if(actData['success'] != undefined){
						$.G2.actions2.run(element, actData['success'], {'data' : result, 'json' : json, 'content' : resultContent});
					}
					
					return true;
				}
			});
			
			return true;
		}
	};
	
	$.G2.actions2.addContent = function(target, result, actResult){
		var pos = actResult.place;
		
		if(pos == undefined || pos == 'html'){
			target.html(result);
		}else if(pos == 'replace'){
			target.replaceWith(result);
		}else if(pos == 'append'){
			target.append(result);
		}else if(pos == 'after'){
			target.after(result);
		}else if(pos == 'before'){
			target.before(result);
		}
	};
	
	$.G2.actions2.run = function(element, actions, params){
		$.each(actions, function(index, actData){
			var reply = $.G2.actions2.fns(element, actData, params);
			//console.log(reply);
			if(reply != true){
				return false;
			}
		});
	};
	
	$.G2.actions2.dynamics = function(element){
		
		if($(element).data('actions')){
			var actions = $(element).data('actions');
			$.each(actions, function(event, eventActions){
				$(element).on(event, function(e){
					e.preventDefault();
					$.G2.actions2.run(element, eventActions);
				});
			});
		}
		
		$(element).trigger('init');
	};
	
}(jQuery));