(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	$.G2.forms2 = {};
	
	$.G2.forms2.initializeForm = function (Form){
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
		
		Form.find('[data-vrules]').each(function(i, inp){
			if(jQuery(inp).data('vrules').disabled == undefined || jQuery(inp).data('vrules').disabled == 0){
				validationRules['field'+i] = jQuery(inp).data('vrules');
			}
		});
		
		Form.form({
			inline : false,
			on : 'blur',
			fields: validationRules
		});
	}
	
	$.G2.forms2.ready = function(Elem){
		
		if(typeof Elem == 'undefined'){
			Elem = $('body');
		}
		
		var forms = [];
		
		if(Elem.hasClass('G2-form2')){
			forms = Elem;
		}else{
			forms = Elem.find('.G2-form2');
		}
		
		forms.each(function(k, Form){
			jQuery.G2.forms2.initializeForm($(Form));
		});
		/*
		jQuery.G2.forms2.initializeForm(Form);
		
		if(jQuery.fn.inputmask != undefined){
			Form.find('[data-inputmask]').inputmask();
		}
		
		Form.on('g2.actions.dynamic.beforeStart', function(){
			Form.data('beforeStart', Form.form('is valid'));
		});
		*/
	}
	
}(jQuery));