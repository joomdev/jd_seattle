(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	$.G2.forms = {};
	
	$.G2.forms.initializeForm = function (Form){
		var validationRules = {};
		
		jQuery.fn.form.settings.rules.required = function(value){
			if(value){
				return true;
			}else{
				return false;
			}
		};
		
		jQuery.fn.form.settings.rules.email = function(value){
			if(value.match(/^([a-zA-Z0-9_\.\-\+%])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,11})+$/)){
				return true;
			}else{
				return false;
			}
		};
		
		jQuery.fn.form.settings.rules.minChecked = function(value, minChecked){
			jQuery(this).closest('.fields').off('change.validation').on('change.validation', ':input', function(){
				Form.form('validate form');
			});
			
			if(jQuery(this).closest('.fields').find(':input:checked').length >= minChecked){
				jQuery(this).closest('.fields').removeClass('error');
				return true;
			}else{
				jQuery(this).closest('.fields').addClass('error');
				return false;
			}
		};
		
		jQuery.fn.form.settings.rules.maxChecked = function(value, maxChecked){
			jQuery(this).closest('.fields').off('change.validation').on('change.validation', ':input', function(){
				Form.form('validate form');
			});
			
			if(jQuery(this).closest('.fields').find(':input:checked').length > maxChecked){
				jQuery(this).closest('.fields').addClass('error');
				return false;
			}else{
				jQuery(this).closest('.fields').removeClass('error');
				return true;
			}
		};
		
		jQuery.fn.form.settings.rules.exactChecked = function(value, exactChecked){
			jQuery(this).closest('.fields').off('change.validation').on('change.validation', ':input', function(){
				Form.form('validate form');
			});
			
			if(jQuery(this).closest('.fields').find(':input:checked').length != exactChecked){
				jQuery(this).closest('.fields').addClass('error');
				return false;
			}else{
				jQuery(this).closest('.fields').removeClass('error');
				return true;
			}
		};
		
		Form.find('[data-validationrules]').each(function(i, inp){
			if(jQuery(inp).data('validationrules').disabled == undefined || jQuery(inp).data('validationrules').disabled == 0){
				validationRules['field'+i] = jQuery(inp).data('validationrules');
				
				//jQuery.each(['empty', 'required', 'checked', 'minChecked', 'maxChecked', 'exactChecked'], function(i, r){
				jQuery.each(jQuery(inp).data('validationrules')['rules'], function(i, r){
					//if(jQuery(inp).data('validationrules')['rules'][0]['type'].indexOf(r) >= 0){
					jQuery.each(['empty', 'required', 'checked', 'minChecked', 'maxChecked', 'exactChecked'], function(ir, vr){
						if(r['type'].indexOf(vr) > -1){
							if(jQuery(inp).parent().hasClass('checkbox')){
								if(jQuery(inp).closest('.fields').length > 0){
									jQuery(inp).closest('.fields').addClass('required');
								}else{
									jQuery(inp).closest('.field').addClass('required');
								}
							}else{
								jQuery(inp).closest('.field').addClass('required');
							}
						}
					});
				});
			}
		});
		
		Form.form({
			//inline : true,
			inline : Form.data('valloc') ? ((Form.data('valloc') == 'inline' || Form.data('valloc') == 'inlinetext') ? true : false) : true,
			on : 'blur',
			fields: validationRules,
			onInvalid: function(){
				if(Form.data('valloc') == 'inlinetext'){
					var erlabel = $(this).closest('.field').find('.ui.label.prompt.pointing').first();
					erlabel.css('display', 'none !important');
					var field = $(this).closest('.multifield.fields.grouped, .multifield.fields.inline').length > 0 ? $(this).closest('.multifield.fields.grouped, .multifield.fields.inline') : $(this).closest('.field');
					if(field.find('small.custom-error').length > 0){
						field.find('small.custom-error').show();
					}else{
						field.append($('<small class="custom-error">').css('color', 'red').css('display', 'block').text(erlabel.text()));
					}
					erlabel.remove();
				}
			},
			onValid: function(){
				var field = $(this).closest('.multifield.fields.grouped, .multifield.fields.inline').length > 0 ? $(this).closest('.multifield.fields.grouped, .multifield.fields.inline') : $(this).closest('.field');
				field.find('small.custom-error').hide();
			}
		});
	}
	
	$.G2.forms.initializeEvents = function (Form){
		//Form.find('[data-events]').each(function(i, inp){
		Form.off('change.events click.events ready.events', '[data-events]');
		Form.on('change.events click.events ready.events', '[data-events]', function(e){
			e.stopPropagation();
			//var events = jQuery(inp).data('events');
			var inp = this;
			var events = JSON.parse(jQuery(inp).attr('data-events'));
			
			//jQuery(inp).off('change.events click.events ready.events');
			jQuery.each(events, function(ei, event){
				//jQuery(inp).off('change click ready');
				//jQuery(inp).on('change.events click.events ready.events', function(e){
					
					if(event.hasOwnProperty('identifier') != true || event['identifier'] == '' || event.hasOwnProperty('action') != true || event.action.length == 0){
						return;
					}
					
					//get current input value
					var inp_value = jQuery(inp).data('value') ? jQuery(inp).data('value') : jQuery(inp).val();
					
					if(jQuery(inp).attr('type') == 'checkbox'){
						inp_value = (jQuery(inp).is(':checked') ? inp_value : '');
					}
					if(jQuery(inp).prop('tagName') == 'SELECT'){
						inp_value = jQuery(inp).find(':selected').data('value') ? jQuery(inp).find(':selected').data('value') : jQuery(inp).val();
					}
					if(event.hasOwnProperty('value') != true){
						event['value'] = [jQuery(inp).val()];
					}
					if(event.hasOwnProperty('group') && event.group == 1){
						inp_value = [];
						jQuery.each(jQuery(inp).closest('.fields').find(':input:checked'), function(kk, checked){
							if(jQuery(checked).data('value')){
								inp_value.push(jQuery(checked).data('value'));
							}else{
								inp_value.push(jQuery(checked).val());
							}
						});
					}
					
					//evaluate condition
					var event_condition = false;
					if(jQuery.isArray(inp_value)){
						if(event.sign == '='){
							//event_condition = (jQuery.inArray(event['value'], inp_value) > -1);
							event_condition = (jQuery(inp_value).filter(event['value']).length > 0);
						}else if(event.sign == '!='){
							//event_condition = (jQuery.inArray(event['value'], inp_value) == -1);
							event_condition = (jQuery(inp_value).filter(event['value']).length == 0);
						}else if(event.sign == 'change'){
							if(e.type != 'ready'){
								event_condition = true;
							}
						}
					}else{
						if(event.sign == '='){
							//event_condition = (inp_value == event['value']);
							event_condition = (jQuery([inp_value]).filter(event['value']).length > 0);
						}else if(event.sign == '!='){
							//event_condition = (inp_value != event['value']);
							event_condition = (jQuery([inp_value]).filter(event['value']).length == 0);
						}else if(event.sign == 'change'){
							if(e.type != 'ready'){
								event_condition = true;
							}
						}else if(event.sign == 'click' && e.type == 'click'){
							event_condition = true;
						}
					}
					
					var event_targets = [];
					jQuery.each(event['identifier'], function(idi, ident){
						if(ident.substring(0, 1) == '#' || ident.substring(0, 1) == '.' || ident.substring(0, 1) == '['){
							event_targets = jQuery.merge(event_targets, jQuery(ident));
						}else{
							event_targets = jQuery.merge(event_targets, jQuery(':input[name="' + ident + '"]'));
							if(jQuery.inArray('function', event.action) > -1){
								event_targets = [ident];
							}
						}
					});
					
					jQuery.each(event_targets, function(ix, event_target){
						event_target = jQuery(event_target);
						var event_target_one = event_target;
						
						var target_element = event_target.closest('.field');
						if(jQuery.inArray(event_target.prop('tagName'), ['BUTTON', 'DIV']) > -1){
							target_element = event_target;
						}
						if(jQuery.inArray(event_target.prop('type'), ['checkbox', 'radio']) > -1){
							target_element = event_target.closest('.multifield.fields').length > 0 ? event_target.closest('.multifield.fields') : event_target.closest('.field');
						}
						
						if(event_target.data('ghost')){
							if(event_target.closest('.multifield.fields').length > 0){
								var real_event_target = event_target.closest('.multifield.fields').find(':checkbox, :radio');
								target_element = event_target.closest('.multifield.fields');
								if(real_event_target.length > 0){
									event_target = real_event_target;
									event_target_one = real_event_target.first();
								}
							}else{
								
							}
						}
						
						if(jQuery.isArray(event.action) == false){
							event.action = [event.action];
						}
						if(event_condition){
							if(jQuery.inArray('hide', event.action) > -1){
								target_element.hide();
							}
							if(jQuery.inArray('show', event.action) > -1){
								//target_element.show();
								target_element.css('display', '');
								target_element.removeClass('hidden');
							}
							if(jQuery.inArray('disable', event.action) > -1){
								target_element.addClass('disabled');
								event_target.prop('disabled', true);
							}
							if(jQuery.inArray('enable', event.action) > -1){
								target_element.removeClass('disabled');
								event_target.prop('disabled', false);
								if(event_target.prop('tagName') == 'SELECT'){
									event_target.parent('.ui.dropdown').removeClass('disabled');
								}
							}
							if(jQuery.inArray('disable_validation', event.action) > -1){
								if(event_target_one.data('validationrules')){
									var vrules = event_target_one.data('validationrules');
									vrules['disabled'] = 1;
									event_target_one.data('validationrules', vrules);
									
									$.G2.forms.initializeForm(Form);
									target_element.removeClass('required error');
									target_element.find('.ui.label.red.pointing.prompt').remove();
								}
							}
							if(jQuery.inArray('enable_validation', event.action) > -1){
								if(event_target_one.data('validationrules')){
									var vrules = event_target_one.data('validationrules');
									vrules['disabled'] = 0;
									event_target_one.data('validationrules', vrules);
									
									$.G2.forms.initializeForm(Form);
								}
							}
							if(jQuery.inArray('reload', event.action) > -1){
								if(e.type != 'ready' && event_target.length > 0){
									target_element.addClass('ui form loading');
									
									$.ajax({
										url: event_target.data('reloadurl'),
										data: jQuery(inp).closest('.form').serialize(),
										success: function(result){
											var newContent = $(result);
											
											target_element.replaceWith(newContent);
											
											newContent.trigger('contentChange');
											jQuery.G2.forms.initializeForm(Form);
											//Form.trigger('contentChange');
										}
									});
								}
							}
							if(jQuery.inArray('function', event.action) > -1){
								jQuery.each(event['identifier'], function(idi, ident){
									if(e.type != 'ready' && window[ident] != undefined){
										window[ident](jQuery(inp));
									}
								});
							}
							//if(jQuery.inArray(event.action, ['add', 'sub', 'multiply', 'set']) > -1){
							if(jQuery(event.action).filter(['add', 'sub', 'multiply', 'set']).length){
								target_element = event_target;
								
								var current_value = parseFloat(target_element.val());
								if(isNaN(current_value)){
									current_value = 0;
								}
								
								if(jQuery.isArray(inp_value)){
									var inp_value_float = 0;
									jQuery.each(inp_value, function(iv, inp_value_v){
										if(!isNaN(parseFloat(inp_value_v))){
											inp_value_float = inp_value_float + parseFloat(inp_value_v);
										}
									});
								}else{
									var inp_value_float = parseFloat(inp_value);
									if(isNaN(inp_value_float)){
										inp_value_float = 0;
										if(event.action == 'multiply'){
											inp_value_float = 1;
										}
									}
								}
								
								var calcList = {};
								var inp_name = jQuery(inp).attr('name');
								
								if(target_element.data('calclist')){
									calcList = target_element.data('calclist');
								}
								
								var prev_inp_value = 0;
								if(calcList.hasOwnProperty(inp_name)){
									prev_inp_value = calcList[inp_name];
								}
								
								calcList[inp_name] = inp_value_float;
								target_element.data('calclist', calcList);
								var change_value = 0;
								
								if(jQuery.inArray('add', event.action) > -1){
									var total = current_value + inp_value_float - prev_inp_value;
									change_value = inp_value_float;
								}else if(jQuery.inArray('sub', event.action) > -1){
									var total = current_value - inp_value_float - prev_inp_value;
									change_value = - inp_value_float;
								}else if(jQuery.inArray('multiply', event.action) > -1){
									if(prev_inp_value == 0){
										prev_inp_value = 1;
									}
									var total = (current_value/prev_inp_value) * inp_value_float;
								}else if(jQuery.inArray('set', event.action) > -1){
									var total = inp_value_float;
								}
								
								if(change_value != 0){
									calcList[inp_name] = change_value;
									target_element.data('calclist', calcList);
								}
								
								target_element.val(total);
								
								if(target_element.data('display')){
									jQuery('#'+target_element.data('display')).text(total);
								}
							}
						}
					});
				//});
				
				//jQuery(inp).trigger('ready.events');
			});
			
			//jQuery(inp).trigger('ready.events');
		});
		
		Form.find('[data-events]').trigger('ready.events');
	}
	
	$.G2.forms.initializeFeatures = function (Form){
		Form.on('click', '.partitioned .ui.button.next, .partitioned .ui.button.forward', function(e){
			e.preventDefault();
			var activeTab = jQuery(this).closest('.partitioned').find('.ui.segment.tab.active').first();
			activeTab.find(':input').trigger('blur');
			
			if(activeTab.next('.ui.segment.tab').length > 0 && activeTab.find('.field.error').length == 0){
				activeTab.removeClass('active');
				jQuery('[data-tab="'+activeTab.data('tab')+'"]').removeClass('active');
				activeTab.next('.ui.segment.tab').addClass('active');
				jQuery('[data-tab="'+activeTab.next('.ui.segment.tab').data('tab')+'"]').addClass('active').removeClass('disabled');
			}else{
				
			}
		});
		
		Form.on('click', '.partitioned .ui.button.prev, .partitioned .ui.button.backward', function(e){
			e.preventDefault();
			var activeTab = jQuery(this).closest('.partitioned').find('.ui.segment.tab.active').first();
			activeTab.find(':input').trigger('blur');
			
			if(activeTab.prev('.ui.segment.tab').length > 0 && activeTab.find('.field.error').length == 0){
				activeTab.removeClass('active');
				jQuery('[data-tab="'+activeTab.data('tab')+'"]').removeClass('active');
				activeTab.prev('.ui.segment.tab').addClass('active');
				jQuery('[data-tab="'+activeTab.prev('.ui.segment.tab').data('tab')+'"]').addClass('active').removeClass('disabled');
			}else{
				
			}
		});
		
		//Form.find('.repeater .ui.source-item').hide().find(':input').prop('disabled', true);
		Form.find('.repeater .ui.source-item').hide().find(':input').each(function(i, inp){
			$(inp).attr('ex-name', $(inp).attr('name'));
			$(inp).removeAttr('name');
			if(jQuery(inp).data('validationrules')){
				$(inp).attr('data-exvalidationrules', $(inp).attr('data-validationrules'));
				$(inp).removeAttr('data-validationrules');
			}
		});
		
		Form.on('click.repeater', '.repeater .ui.button.multiply', function(e){
			e.preventDefault();
			
			var cloned = jQuery(this).closest('.repeater').find('.ui.source-item').clone().show();
			cloned.find(':input').each(function(i, inp){
				$(inp).attr('name', $(inp).attr('ex-name'));
				$(inp).removeAttr('ex-name');
				if(jQuery(inp).attr('data-exvalidationrules')){
					$(inp).attr('data-validationrules', $(inp).attr('data-exvalidationrules'));
				}
			});
			
			var newHTML = cloned.html().replace(/-N-/g, jQuery(this).closest('.repeater').data('count'));
			if(cloned.data('name')){
				repeaterRegex = new RegExp('#'+cloned.data('name')+'.count', 'gi');
				newHTML = newHTML.replace(repeaterRegex, jQuery(this).closest('.repeater').data('count'));
			}
			
			cloned.html(newHTML);
			jQuery(this).closest('.repeater').data('count', parseInt(jQuery(this).closest('.repeater').data('count')) + 1);
			
			if(jQuery(this).closest('.repeater').data('limit')){
				if(jQuery(this).closest('.repeater').find('.clone-item').length >= parseInt(jQuery(this).closest('.repeater').data('limit'))){
					return;
				}
			}
			jQuery(this).before(cloned.removeClass('source-item').addClass('clone-item'));
			
			cloned.trigger('contentChange');
			jQuery.G2.forms.initializeForm(Form);
			
			jQuery(this).closest('.repeater').trigger('g2.forms.repeater.add');
		});
		
		Form.on('click.repeater', '.repeater .ui.button.remove', function(e){
			e.preventDefault();
			
			jQuery(this).closest('.ui.clone-item').remove();
			
			jQuery(this).closest('.repeater').trigger('g2.forms.repeater.remove');
			
			jQuery.G2.forms.initializeForm(Form);
		});
		
		Form.on('click', '.modaled > .ui.button.green, .modaled > .ui.button.launch', function(e){
			e.preventDefault();
			var theModal = jQuery(this).closest('.modaled').find('.ui.modal').first();
			theModal.modal({detachable : false, closable : (theModal.data('closable') ? true : false)}).modal('show');
		});
		
		Form.on('submit', function(e){
			if(Form.form('is valid') == false){
				Form.form('validate form');//revalidate the form to have the error class added in case the error is not under the first tab
				if(Form.find('.field.error').first().is(':visible')){
					jQuery.G2.scrollTo(Form.find('.field.error').first());
				}else{
					//Form.form('validate form');//revalidate the form to have the error class added in case the error is not under the first tab
					if(Form.find('.field.error').first().closest('.partitioned').length > 0){
						var activeTab = Form.find('.field.error').first().closest('.partitioned').find('.ui.segment.tab.active').first();
			
						activeTab.removeClass('active');
						jQuery('[data-tab="'+activeTab.data('tab')+'"]').removeClass('active');
						Form.find('.field.error').first().closest('.ui.segment.tab').addClass('active');
						jQuery('[data-tab="'+Form.find('.field.error').first().closest('.ui.segment.tab').data('tab')+'"]').addClass('active');
						jQuery('[data-tab="'+Form.find('.field.error').first().closest('.ui.segment.tab').data('tab')+'"]').removeClass('disabled');
					}
				}
			}else{
				if(Form.data('subanimation')){
					Form.addClass('loading');
				}
				//Form.form('submit');
			}
		});
	}
	
	$.G2.forms.invisible = function(){
		jQuery('div[data-invisible="1"]').each(function(i, invForm){
			var content = jQuery(invForm).html();
			var newForm = jQuery('<form>').html(content);
			jQuery.each(jQuery(invForm).get(0).attributes, function(i, att){
				newForm.attr(att.name, att.value);
			});
			jQuery(invForm).replaceWith(newForm);
			//jQuery('body').trigger('contentChange');
		});
	}
	
	$.G2.forms.ready = function(Form){
		jQuery.G2.forms.initializeFeatures(Form);
		
		jQuery.G2.forms.initializeEvents(Form);
		
		jQuery.G2.forms.initializeForm(Form);
		
		if(jQuery.fn.inputmask != undefined){
			Form.find('[data-inputmask]').inputmask();
		}
		
		Form.on('g2.actions.dynamic.beforeStart', function(){
			Form.data('beforeStart', Form.form('is valid'));
		});
	}
	
}(jQuery));