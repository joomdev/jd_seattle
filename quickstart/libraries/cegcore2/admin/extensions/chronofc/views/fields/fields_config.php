<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	ob_start();
?>
<style>
.fields-destination{padding-bottom:40px !important;}
</style>
<script>
	jQuery(document).ready(function($){
		$('.fields-destination').children('.field, .fields').each(function(k, field){
			//Fields_prepare_field($(field), $(field).children('.field-config'));
			Fields_prepare_field($(field));
		});
		
		Fields_sorter();
	});
</script>
<?php
	$jscode = ob_get_clean();
	\GApp::document()->addHeaderTag($jscode);
?>
<script>
	function Fields_add_field(add_link, n){
		var field_type = jQuery(add_link).data('type');
		var field_number = parseInt(jQuery(add_link).closest('.segment').find('.fields-count').val()) + 1;
		
		var active_destination = jQuery(add_link).closest('.segment').find('.fields-destination.green');
		
		//active_destination.addClass('loading');
		var dummy_field = jQuery('<div class="ui segment loading"></div>');
		active_destination.append(dummy_field);
		
		jQuery.ajax({
			url: "<?php echo r2('index.php?ext=chronoconnectivity&cont=connections&act=view_config&tvout=view'); ?>",
			data: {'type' : 'fields', 'id' : 'field', 'count' : n, 'params[0]' : 'field_type', 'params[1]' : 'field_number', 'field_type' : field_type, 'field_number' : field_number},
			success: function(result){
				
				var new_field = jQuery(result);
				
				Fields_prepare_field(new_field, field_type);
				
				//insert new field
				new_field.find('.field_destination_number').first().val(active_destination.children('.field-config').first().data('number'));
				
				dummy_field.replaceWith(new_field);
				
				jQuery('body').trigger('contentChange');
			}
		});
		
		jQuery(add_link).closest('.segment').find('.fields-count').val(field_number);
	}
	
	function Fields_prepare_field(new_field, field_type){
		var new_field_field = new_field.children('.field, .fields').first();
		var new_field_config = new_field.children('.field-config').first();
		
		var config_links_holder = new_field.find('label').first();
		if(config_links_holder.length < 1){
			config_links_holder = new_field.find('label').first();
		}
		
		var n = new_field_config.data('count');
		var field_type = new_field_config.data('type');
		var field_number = new_field_config.data('number');
		
		
		//add edit link
		//if(field_type != 'multiple'){
		if(new_field_config.attr('data-type') != 'multiple'){
			var edit_link = jQuery('<i class="icon write blue edit_link"></i>');
			
			edit_link.on('click', function(){
				new_field_config.transition('slide down');
				
				Fields_activate_closest_destination(edit_link.closest('.fields-destination'));
				
				if(new_field_config.closest('.fields.fields-destination').length > 0){
					if(new_field_config.hasClass('visible') == false){
						new_field_config.closest('.fields.fields-destination').removeClass('two').addClass('grouped');
					}else{
						new_field_config.closest('.fields.fields-destination').removeClass('grouped').addClass('two');
					}
				}
			});
			
			config_links_holder.append(' ').append(edit_link);
			
			var drag_link = jQuery('<i class="icon move purple drag_link"></i>');
			
			//config_links_holder.append(' ').append(drag_link);
		}
		
		if(new_field_config.attr('data-type') == 'multiple'){
			var activate_link = jQuery('<i class="icon checkmark green"></i>');
			
			activate_link.on('click', function(){
				Fields_activate_closest_destination(activate_link.closest('.fields-destination'));
			});
			
			config_links_holder.append(' ').append(activate_link);
		}
		//}
		
		//add sort link
		var sort_link = jQuery('<i class="icon sort orange field-sort-link"></i>');
		
		config_links_holder.append(' ').append(sort_link);
		
		//add delete link
		var delete_link = jQuery('<i class="icon delete red"></i>');
		
		delete_link.on('click', function(){
			new_field.transition({
				'animation' : 'fly right', 
				'onComplete' : function(){
					new_field.remove();
				}
			});
		});
		
		config_links_holder.append(' ').append(delete_link);
		
		//add hover events for config links
		/*new_field_field.on('mouseenter', function(){
			new_field_field.find('.field-config-link').removeClass('hidden');
		});
		new_field_field.on('mouseleave', function(){
			new_field_field.find('.field-config-link').addClass('hidden');
		});*/
		
		//add config buttons
		var config_apply_button = jQuery('<button type="button" class="ui button green compact small" data-type="apply">Apply</button>');
		
		config_apply_button.on('click', function(){
			var config_panel = config_apply_button.closest('.field-config');
			var data = {'type' : 'fields', 'id' : 'field', 'count' : n, 'params[0]' : 'field_type', 'params[1]' : 'field_number', 'field_type' : field_type, 'field_number' : field_number};
			
			var config_data = config_panel.find(':input').serializeArray();
			
			jQuery.each(config_data, function(k, config_object){
				data[config_object.name] = config_object.value;
			});
			
			new_field_config.transition('slide down');
			new_field.addClass('ui segment loading');
			
			jQuery.ajax({
				'url': "<?php echo r2('index.php?ext=chronoconnectivity&cont=connections&act=view_config&tvout=view'); ?>",
				'data': data,
				success: function(result){
					new_field_config.remove();
					
					var updated_field = jQuery(result);
					
					Fields_prepare_field(updated_field);
					
					new_field.replaceWith(updated_field);
					
					updated_field.closest('.fields-destination').removeClass('loading');
					
					jQuery('body').trigger('contentChange');
				}
			});
		});
		
		var config_cancel_button = jQuery('<button type="button" class="ui button red compact small" data-type="cancel">Close</button>');
		config_cancel_button.on('click', function(){
			//new_field_config.transition('slide down');
			config_links_holder.find('.edit_link').first().trigger('click');
		});
		
		new_field_config.append(config_cancel_button).append(config_apply_button);
		
	}
	
	function Fields_Movable(){
		jQuery('.fields-destination > .field').draggable({
			handle:'.drag_link',
			'revert' : 'invalid',
			zIndex:1000,
		});
	}
	
	Fields_Movable();
	
	function Fields_sorter(){
		jQuery('.fields-destination').sortable({
			placeholder : 'ui message orange',
			items : '> div.field, > div.fields',
			handle : '.field-sort-link'
			/*update : function(event, ui){
				ui.item.after(jQuery('.field-config[data-field="'+ ui.item.attr('data-field') +'"]'));
			}*/
		});
	}
	
	function Fields_activate_closest_destination(closestOne){
		jQuery('.fields-destination').removeClass('green');
		closestOne.addClass('green');
	}
	
	//Fields_activate_closest_destination(jQuery('.main_activate_link').closest('.segment').next('.segment'));
	
	Fields_sorter();
</script>
<script>
	function Fields_add_field_event(link){
		var new_event = jQuery(link).closest('.fields').clone();
		new_event.find('.delete_button').removeClass('hidden');
		var events_count = 1 + parseInt(jQuery(link).closest('.fields_events_list').find('.fields_events_counter').first().val());
		jQuery(link).closest('.fields_events_list').find('.fields_events_counter').first().val(events_count);
		
		new_event.html(new_event.html().replace(/\[events\]\[[0-9]+\]/g, '[events][' + events_count + ']'));
		
		jQuery(link).closest('.fields_events_list').append(new_event);
		jQuery('.ui.dropdown').dropdown('refresh');
	}
	function Fields_delete_field_event(link){
		jQuery(link).closest('.fields').remove();
	}
</script>
<div class="ui segment tab views-tab active" data-tab="view-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="view-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="view-<?php echo $n; ?>-general">
		<input type="hidden" value="fields" name="Connection[views][<?php echo $n; ?>][type]">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][name]">
			</div>
			<div class="field">
				<label><?php el('Category'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][category]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Container class'); ?></label>
				<input type="text" value="ui form" name="Connection[views][<?php echo $n; ?>][container][class]">
			</div>
		</div>
		
		<div class="ui menu inverted fields-source top attached">
			<a class="item" data-type="text" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Text'); ?></a>
			<a class="item" data-type="password" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Password'); ?></a>
			<a class="item" data-type="textarea" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Textarea'); ?></a>
			<a class="item" data-type="checkbox" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Checkbox'); ?></a>
			<a class="item" data-type="checkboxes" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Checkboxes'); ?></a>
			<a class="item" data-type="radios" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Radios'); ?></a>
			<a class="item" data-type="select" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Dropdown'); ?></a>
			<a class="item" data-type="file" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('File'); ?></a>
			<a class="item" data-type="button" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Button'); ?></a>
		</div>
		<div class="ui menu inverted fields-source bottom attached">
			<a class="item" data-type="hidden" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Hidden'); ?></a>
			<a class="item" data-type="calendar" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Calendar'); ?></a>
			<a class="item" data-type="button_link" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Button Link'); ?></a>
			<a class="item" data-type="header" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Header'); ?></a>
			<a class="item" data-type="message" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Message'); ?></a>
			<a class="item" data-type="custom" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Custom'); ?></a>
			<!--<a class="item" data-type="image" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Image'); ?></a>-->
			<a class="item" data-type="multiple" onclick="Fields_add_field(this, <?php echo $n; ?>);"><?php el('Multiple'); ?></a>
		</div>
		
		<input type="hidden" value="<?php echo empty($view['fields']) ? 0 : max(array_keys($view['fields'])); ?>" class="fields-count">
		
		<div class="ui tab segment green active fields-destination">
			<?php if(!empty($view['fields'])): ?>
				<?php foreach($view['fields'] as $field_number => $field): ?>
					<?php if(empty($field['field_destination'])): ?>
						<?php $this->view(dirname(__FILE__).DS.'field.php', ['field_number' => $field_number, 'field_type' => $field['field_type'], 'n' => $n, 'all' => $view['fields']]); ?>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[views]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
</div>